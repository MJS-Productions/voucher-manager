<?php
/** Plugin unit tests. @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Tests\Unit;
use PHPUnit\Framework\TestCase;
use VoucherManager\Core\Plugin;
final class PluginTest extends TestCase {
	public function test_instance_returns_same_object(): void { self::assertSame( Plugin::instance(), Plugin::instance() ); }
}
