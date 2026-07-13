<?php
/**
 * Import application service.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Import;

use RuntimeException;
use Throwable;
use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Support\CodeFileParser;

final class ImportService {
	private const BATCH_SIZE = 250;

	public function __construct(
		private readonly ImportRepository $imports,
		private readonly CodeRepository $codes,
		private readonly LogRepository $logs,
		private readonly CodeFileParser $parser
	) {}

	public function import( int $pool_id, string $path, string $filename, string $file_type ): ImportResult {
		$import_id = $this->imports->start( $pool_id, $filename, $file_type );
		if ( 0 >= $import_id ) {
			throw new RuntimeException( 'Import record could not be created.' );
		}

		$total = 0;
		$imported = 0;
		$invalid = 0;
		$batch = array();
		$seen = array();

		try {
			foreach ( $this->parser->parse( $path, $file_type ) as $candidate ) {
				++$total;
				$code = trim( $candidate );
				if ( '' === $code || 4096 < strlen( $code ) ) {
					++$invalid;
					continue;
				}
				$hash = hash( 'sha256', $code );
				if ( isset( $seen[ $hash ] ) ) {
					continue;
				}
				$seen[ $hash ] = true;
				$batch[] = $code;
				if ( self::BATCH_SIZE <= count( $batch ) ) {
					$imported += $this->codes->insert_batch( $pool_id, $import_id, $batch );
					$batch = array();
				}
			}
			if ( array() !== $batch ) {
				$imported += $this->codes->insert_batch( $pool_id, $import_id, $batch );
			}
			$skipped = max( 0, $total - $invalid - $imported );
			$this->imports->complete( $import_id, $total, $imported, $skipped, $invalid );
			$this->logs->add(
				OperationalEvent::IMPORT_COMPLETED->value,
				'Code import completed.',
				array( 'import_id' => $import_id, 'pool_id' => $pool_id, 'imported' => $imported, 'skipped' => $skipped, 'invalid' => $invalid )
			);
			return new ImportResult( $import_id, $total, $imported, $skipped, $invalid );
		} catch ( Throwable $exception ) {
			$skipped = max( 0, $total - $invalid - $imported );
			$this->imports->fail( $import_id, $total, $imported, $skipped, $invalid );
			$this->logs->add( OperationalEvent::IMPORT_FAILED->value, 'Code import failed.', array( 'import_id' => $import_id, 'pool_id' => $pool_id ) );
			throw $exception;
		}
	}

	public function rollback( int $import_id ): int {
		if ( 0 < $this->codes->count_assigned_by_import( $import_id ) ) {
			throw new RuntimeException( 'Assigned codes prevent rollback.' );
		}
		$deleted = $this->codes->delete_available_by_import( $import_id );
		$this->imports->mark_rolled_back( $import_id );
		$this->logs->add( OperationalEvent::IMPORT_ROLLED_BACK->value, 'Code import rolled back.', array( 'import_id' => $import_id, 'deleted' => $deleted ) );
		return $deleted;
	}
}
