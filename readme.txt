=== MJS-Productions Voucher Manager ===
Contributors: mjs512
Tags: one-time codes, voucher codes, unique codes, code import, code pool
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage, import and distribute One-Time Codes, voucher codes and unique codes in WordPress.

== Description ==

Voucher Manager helps you manage, import and distribute One-Time Codes, voucher codes and unique codes directly in WordPress.

Organize codes in dedicated pools, import existing code lists from TXT or CSV files, keep track of available and assigned codes, and issue each One-Time Code through a controlled workflow.

= Key Features =

* Pool Management - organize One-Time Codes in dedicated pools for products, campaigns or other use cases.
* Inventory Overview - review available and assigned One-Time Codes with filters and pool context.
* TXT and CSV Import - import existing One-Time Code lists from TXT or CSV files directly into selected pools.
* Import Rollback - undo eligible imports as long as no One-Time Code from the import has been distributed.
* Controlled Distribution - One-Time Codes are issued through a controlled workflow designed to help prevent duplicate issuance.
* Activity History - review important operational events without storing distributed One-Time Code values.
* Retention Management - configure automatic activity retention and scheduled cleanup.
* Administrator Access - Voucher Manager is designed for WordPress administrators.
* Localization Ready - uses the WordPress localization system and supports translations provided through WordPress.org.

= Data-Minimal by Design =

Distributed One-Time Code values are not stored in the Activity History. Operational events remain available for review without exposing the issued code values.

= Simple Workflow =

1. Create a pool.
2. Import One-Time Codes.
3. Distribute one available One-Time Code.
4. Review inventory and operational activity as needed.

Whether you manage voucher codes for products, campaigns or other use cases, Voucher Manager keeps your One-Time Code workflow organized directly in WordPress.

== Installation ==

1. In your WordPress administration area, go to Plugins → Add Plugin and search for “MJS-Productions Voucher Manager”.
2. Install and activate the plugin.
3. Open Voucher Manager in the WordPress administration area.
4. Create your first pool.
5. Import One-Time Codes from a TXT or CSV file.
6. Distribute codes and review inventory and activity as needed.

No additional configuration is required for basic use.

== Frequently Asked Questions ==

= What is a One-Time Code? =

A One-Time Code is a unique code intended to be issued once. Voucher Manager stores available One-Time Codes in pools and provides a controlled workflow for issuing them.

= Can I use Voucher Manager for voucher codes, coupon codes or other unique codes? =

Yes. Voucher Manager can manage existing unique code lists for products, campaigns and other use cases. If each code is intended to be issued only once, it can be organized in a pool, imported from a TXT or CSV file and distributed through Voucher Manager.

= What file formats can I use to import One-Time Codes? =

Voucher Manager supports TXT and CSV files for importing One-Time Codes.

= Can I organize codes for different products or use cases? =

Yes. One-Time Codes are organized in separate pools, allowing different products, campaigns or other use cases to be managed independently.

= Can an import be undone? =

Yes, when the import is still eligible for rollback. An import can only be undone when no One-Time Codes from that import have been distributed.

= Are distributed One-Time Codes stored in the activity history? =

No. Distributed One-Time Code values are not stored in the activity history.

= Can activity history be cleaned up automatically? =

Yes. Voucher Manager provides configurable activity retention and automatic cleanup through WordPress-Cron.

= Does Voucher Manager support translations? =

Yes. Voucher Manager uses the WordPress localization system and supports translations provided through WordPress.org.

== Screenshots ==

1. Dashboard with key metrics, quick actions, recent activity and system status.
2. Pool management for organizing One-Time Codes by product, campaign or use case.
3. Inventory view with status, filters and pool context.
4. TXT and CSV import workflow for adding existing One-Time Codes to a selected pool.
5. Controlled distribution of available One-Time Codes.
6. Activity history with filters and recorded operational events.
7. Settings for activity retention and uninstall behavior.

== Changelog ==

= 1.0.8 =
* Confirmed compatibility with WordPress 7.1 and updated the tested-up-to declaration.
* Hardened database query preparation and release validation for current WordPress Plugin Check expectations.
* Tightened release packaging so development-only repository files are excluded from the installable ZIP.
* Corrected Import guidance: blank rows are ignored, while invalid rows are counted but not imported.
* No database schema migration is required.

= 1.0.7 =
* Expanded Activity History coverage for Pool administration, Settings changes, Distribution failures, plugin lifecycle events and automatic Activity cleanup.
* Added privacy-safe Activity entries for plugin installation, activation, deactivation and retained-data uninstall.
* Improved Activity and One-Time Code terminology in administrator-facing copy.
* TXT imports now ignore blank lines instead of treating them as importable values.
* No database schema migration is required.

= 1.0.6 =
* Refined the German Inventory status filter label from "Alle Status" to "Alle".
* No database schema migration is required.

= 1.0.5 =
* Preserved the Pool name in Activity when a Pool is permanently deleted.
* Hide the Undo import action when an Import already contains distributed One-Time Codes.
* Aligned German Import undo wording with "rückgängig machen".
* Aligned uninstall copy with the established One-Time Code / Einmalcode terminology.
* No database schema migration is required.

= 1.0.4 =
* Added the Pool name to newly completed Import entries in Activity history.
* Preserved the Pool ID fallback for existing Import activity recorded before 1.0.4.
* No database schema migration is required.

= 1.0.3 =
* Hardened Distribution against accidental rapid resubmission by separating a completed result from the next Distribution form.
* Added an explicit "Distribute another One-Time Code" action before a new Distribution intent is created.
* Updated the Dashboard credit to "Made in Austria by MJS-Productions."

= 1.0.2 =
* Added the Pool name to newly recorded successful Distribution entries in Activity history.
* Added remaining inventory information with correct singular and plural wording.
* Removed the internal event key from the administrator-facing Activity list.
* One-Time Code values and personal data remain excluded from Activity history.

= 1.0.1 =
* Added the source Pool name to successful Distribution results.
* Added an explicit remaining inventory label.
* Improved singular and plural wording for remaining One-Time Codes.
* Updated German translations for the additional Distribution context.

= 1.0.0 =
* Initial stable release of Voucher Manager.
* Added pool-based One-Time Code management.
* Added TXT and CSV imports with protected rollback.
* Added controlled Distribution designed to help prevent duplicate issuance.
* Added privacy-conscious Activity history with configurable retention.
* Added inventory and pool lifecycle management.
* Added configurable data removal during uninstall.
* Added English and German administration interfaces.
