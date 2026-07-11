<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Log;

interface LogRepository {
	/** @param array<string,mixed> $context */
	public function add( string $event_type, string $message, array $context = array() ): void;
}
