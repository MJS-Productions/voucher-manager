<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Log\LogRepository;

final class WpdbLogRepository implements LogRepository {
	public function add( string $event_type, string $message, array $context = array() ): void {
		global $wpdb;
		$wpdb->insert($wpdb->prefix.'vm_logs',array('event_type'=>$event_type,'message'=>$message,'context'=>wp_json_encode($context),'created_at'=>current_time('mysql',true)),array('%s','%s','%s','%s'));
	}
}
