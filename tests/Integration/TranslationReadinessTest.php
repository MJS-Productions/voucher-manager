<?php
/**
 * Framework-free translation readiness test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Translation readiness assertion failed: ' . $message );
	}
};

$inventory_view   = file_get_contents( $root . '/src/Admin/InventoryViewModel.php' );
$import_admin     = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$activity_admin   = file_get_contents( $root . '/src/Admin/OperationalActivityAdmin.php' );
$settings_admin   = file_get_contents( $root . '/src/Admin/SettingsAdmin.php' );
$distribution    = file_get_contents( $root . '/templates/admin/distribution.php' );
$import_template = file_get_contents( $root . '/templates/admin/import.php' );
$danger_template = file_get_contents( $root . '/templates/admin/pool-danger-zone.php' );
$delete_template = file_get_contents( $root . '/templates/admin/pool-delete-available-confirmation.php' );
$guide           = file_get_contents( $root . '/docs/LOCALIZATION_GUIDE.md' );
$notes           = file_get_contents( $root . '/docs/TRANSLATOR_NOTES.md' );
$composer        = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $inventory_view )
	&& str_contains( $inventory_view, "_x( 'Available', 'One-Time Code status', 'voucher-manager' )" )
	&& str_contains( $inventory_view, "_x( 'Assigned', 'One-Time Code status', 'voucher-manager' )" ),
	'Ambiguous One-Time Code status labels must carry translator context.'
);

$assert(
	is_string( $import_admin )
	&& str_contains( $import_admin, "_x( 'Import', 'admin menu label', 'voucher-manager' )" )
	&& is_string( $activity_admin )
	&& str_contains( $activity_admin, "_x( 'Activity', 'admin menu label', 'voucher-manager' )" )
	&& is_string( $settings_admin )
	&& str_contains( $settings_admin, "_x( 'Settings', 'admin menu label', 'voucher-manager' )" ),
	'Ambiguous admin menu labels must carry translator context.'
);

$assert(
	is_string( $distribution )
	&& str_contains( $distribution, "esc_html_x( 'Pool', 'Distribution form field label', 'voucher-manager' )" )
	&& str_contains( $distribution, 'One-Time Code assignment occurs immediately after confirmation.' ),
	'Distribution field context and neutral assignment wording must remain translation-ready.'
);

$assert(
	is_string( $import_template )
	&& substr_count( $import_template, '_n(' ) >= 5
	&& str_contains( $import_template, '%d One-Time Code added' )
	&& str_contains( $import_template, '%d rows processed' )
	&& str_contains( $import_template, 'translators: 1: Pool name' ),
	'Import counts and Pool option placeholders must be plural-ready and documented.'
);

$assert(
	is_string( $danger_template )
	&& substr_count( $danger_template, '_n(' ) >= 3
	&& str_contains( $danger_template, 'This permanently deletes the pool, %1$s, and %2$s.' )
	&& ! str_contains( $danger_template, 'all %1$s' )
	&& str_contains( $danger_template, 'translators: 1: localized One-Time Code count' ),
	'Destructive Pool summaries must support independent plurals and grammar-safe reorderable placeholders.'
);

$assert(
	is_string( $delete_template )
	&& str_contains( $delete_template, '_n(' )
	&& str_contains( $delete_template, 'translators: %d: number of available One-Time Codes' ),
	'Available-code deletion consent must use plural handling and translator guidance.'
);

$assert(
	is_string( $guide )
	&& str_contains( $guide, 'Use `_x()`' )
	&& str_contains( $guide, 'Use numbered placeholders' )
	&& str_contains( $guide, 'Use `_n()`' )
	&& is_string( $notes )
	&& str_contains( $notes, '`Pool` is a product concept' )
	&& str_contains( $notes, 'Preserve placeholder type and numbering' ),
	'Localization and translator contracts must be documented.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:translation-readiness' )
	&& strpos( $composer, '@test:translation-readiness' ) < strpos( $composer, '@build' ),
	'Translation Readiness coverage must run before build.'
);

echo "Translation readiness OK: context, placeholders, translator comments and plural forms verified.\n";
