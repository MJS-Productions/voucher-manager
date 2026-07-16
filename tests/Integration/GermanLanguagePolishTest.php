<?php
/**
 * Framework-free German language polish test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'German language polish assertion failed: ' . $message );
	}
};

$po           = file_get_contents( $root . '/languages/voucher-manager-de_DE.po' );
$pot          = file_get_contents( $root . '/languages/voucher-manager.pot' );
$distribution = file_get_contents( $root . '/templates/admin/distribution.php' );
$pool_form    = file_get_contents( $root . '/templates/admin/pool-form.php' );
$composer     = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $pool_form )
	&& str_contains( $pool_form, 'Notifications will use this value in the Pro extension.' )
	&& ! str_contains( $pool_form, 'future Pro extension' ),
	'Free UI must refer to the planned Pro extension without future wording.'
);

$assert(
	is_string( $distribution )
	&& str_contains( $distribution, 'automatically assigns the next available One-Time Code' )
	&& str_contains( $distribution, 'Concurrent requests never receive the same code.' )
	&& ! str_contains( $distribution, 'atomically assigns' ),
	'Administrator-facing Distribution guidance must avoid implementation jargon.'
);

$required_german = array(
	'Benachrichtigungen verwenden diesen Wert in der Pro-Erweiterung.',
	'Import rückgängig machen',
	'Import erfolgreich rückgängig gemacht',
	'Voucher Manager gibt automatisch den nächsten verfügbaren Einmalcode aus. Gleichzeitige Anfragen erhalten niemals denselben Code.',
	'Aufbewahrungsdauer und Verhalten bei der Deinstallation verwalten.',
	'Aufbewahrungsdauer der Aktivitätshistorie',
	'Die Aktivitätshistorie dient der Nachvollziehbarkeit von Vorgängen. Sie ist kein rechtliches oder finanzielles Prüfprotokoll.',
	'Einträge der Aktivitätshistorie werden nicht automatisch gelöscht.',
	'Die Bereinigung erfolgt automatisch über WordPress-Cron in begrenzten täglichen Schritten.',
	'Verhalten bei der Deinstallation',
	'Standard: Alle Daten von Voucher Manager behalten.',
);

foreach ( $required_german as $translation ) {
	$assert(
		is_string( $po ) && str_contains( $po, $translation ),
		'Approved German language polish is missing: ' . $translation
	);
}

$forbidden_german = array(
	'in einer zukünftigen Pro-Erweiterung',
	'Import zurückrollen',
	'Import zurückgerollt',
	'atomar aus',
	'Aufbewahrung betrieblicher Daten',
	'Aufbewahrung betrieblicher Aktivitäten',
	'betrieblicher Verlauf',
	'Betriebliche Aktivitäten',
	'Die Bereinigung läuft über WordPress-Cron',
	'Datengrenze bei der Deinstallation',
	'Alle Geschäftsdaten',
);

foreach ( $forbidden_german as $translation ) {
	$assert(
		is_string( $po ) && ! str_contains( $po, $translation ),
		'Rejected German wording remains in the catalog: ' . $translation
	);
}

$assert(
	is_string( $pot )
	&& str_contains( $pot, 'Notifications will use this value in the Pro extension.' )
	&& str_contains( $pot, 'Concurrent requests never receive the same code.' )
	&& ! str_contains( $pot, 'future Pro extension' )
	&& ! str_contains( $pot, 'atomically assigns' ),
	'POT source catalog must match the polished English source language.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:german-language-polish' )
	&& strpos( $composer, '@test:german-language-polish' ) < strpos( $composer, '@build' ),
	'German language polish coverage must run before build.'
);

echo "German language polish OK: Pro wording and natural administrator-facing copy verified.\n";
