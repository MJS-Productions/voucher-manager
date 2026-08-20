<?php
/**
 * Streaming parser for TXT and CSV code files.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Support;

use Generator;
use RuntimeException;
use SplFileObject;

final class CodeFileParser {
	/** @return Generator<int,string> */
	public function parse( string $path, string $file_type ): Generator {
		if ( ! is_readable( $path ) ) {
			throw new RuntimeException( 'Uploaded file is not readable.' );
		}
		if ( 'csv' === $file_type ) {
			yield from $this->parse_csv( $path );
			return;
		}
		yield from $this->parse_text( $path );
	}

	/** @return Generator<int,string> */
	private function parse_text( string $path ): Generator {
		$file = new SplFileObject( $path, 'rb' );
		while ( ! $file->eof() ) {
			$line = $file->fgets();
			if ( false === $line ) { break; }

			$value = $this->strip_bom( rtrim( $line, "\r\n" ) );
			if ( '' === trim( $value ) ) {
				continue;
			}

			yield $value;
		}
	}

	/** @return Generator<int,string> */
	private function parse_csv( string $path ): Generator {
		$file = new SplFileObject( $path, 'rb' );
		$delimiter = $this->detect_delimiter( $file );
		$file->rewind();
		$first = true;
		while ( ! $file->eof() ) {
			$row = $file->fgetcsv( $delimiter );
			if ( false === $row || array( null ) === $row ) { continue; }
			$value = isset( $row[0] ) ? $this->strip_bom( trim( (string) $row[0] ) ) : '';
			if ( $first && in_array( strtolower( $value ), array( 'code', 'voucher', 'voucher_code', 'coupon', 'key' ), true ) ) {
				$first = false;
				continue;
			}
			$first = false;
			yield $value;
		}
	}

	private function detect_delimiter( SplFileObject $file ): string {
		$sample = '';
		while ( ! $file->eof() && '' === trim( $sample ) ) {
			$line = $file->fgets();
			$sample = false === $line ? '' : $line;
		}
		$counts = array( ',' => substr_count( $sample, ',' ), ';' => substr_count( $sample, ';' ), "\t" => substr_count( $sample, "\t" ) );
		arsort( $counts );
		$delimiter = (string) array_key_first( $counts );
		return 0 < (int) current( $counts ) ? $delimiter : ',';
	}

	private function strip_bom( string $value ): string {
		return preg_replace( '/^\xEF\xBB\xBF/', '', $value ) ?? $value;
	}
}
