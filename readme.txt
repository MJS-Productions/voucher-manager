=== Voucher Manager ===
Contributors: mjs512
Tags: one-time codes, vouchers, inventory, code distribution
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional One-Time Code Management for WordPress.

== Description ==

Voucher Manager provides a structured and efficient way to manage and distribute One-Time Codes within WordPress.

Create dedicated pools, import codes from TXT or CSV files, distribute available codes through a controlled workflow and keep track of inventory and operational activity from the WordPress administration area.

= Key Features =

* Pool Management - organize One-Time Codes in dedicated pools for products, campaigns or other use cases.
* Inventory Overview - review available and distributed codes with filters and inventory context.
* TXT and CSV Import - import One-Time Codes into selected pools.
* Import Rollback - undo eligible imports when no One-Time Codes from the import have been distributed.
* Controlled Distribution - One-Time Codes are issued through a controlled workflow designed to help prevent duplicate issuance.
* Activity History - review important operational events without storing distributed One-Time Code values in the activity history.
* Retention Management - configure automatic activity retention and scheduled cleanup.
* WordPress Roles and Capabilities - administrative access follows the native WordPress permission system.
* English and German Interface - integrated with the WordPress localization system.

= Data-Minimal by Design =

Voucher Manager stores only the information required for its core functionality. One-Time Code values are not stored in Activity history.

= Simple Workflow =

1. Create a pool.
2. Import One-Time Codes.
3. Distribute one available code.
4. Review inventory and operational activity.

Voucher Manager Free is a complete, usable edition designed to provide real value for everyday One-Time Code management.

== Installation ==

1. Upload the Voucher Manager plugin to WordPress and activate it.
2. Open Voucher Manager in the WordPress administration area.
3. Create your first pool.
4. Import One-Time Codes from a TXT or CSV file.
5. Distribute codes and review inventory and activity as needed.

No additional configuration is required for basic use.

== Frequently Asked Questions ==

= What is a One-Time Code? =

A One-Time Code is a code intended to be distributed once. Voucher Manager stores available codes in pools and provides a controlled workflow for distributing them.

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

= Does Voucher Manager support multiple languages? =

Yes. Voucher Manager includes English and German and uses the WordPress localization system.

== Screenshots ==

1. Dashboard with inventory overview, quick actions and recent activity.
2. Pool management for organizing One-Time Codes.
3. Inventory view with status, filters and pool context.
4. TXT and CSV import workflow for adding One-Time Codes to a pool.
5. Controlled distribution of available One-Time Codes.
6. Activity history with privacy-conscious operational information.
7. Settings for activity retention and data management.

== Changelog ==

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
* Initial stable release of Voucher Manager Free.
* Added pool-based One-Time Code management.
* Added TXT and CSV imports with protected rollback.
* Added controlled Distribution designed to help prevent duplicate issuance.
* Added privacy-conscious Activity history with configurable retention.
* Added inventory and pool lifecycle management.
* Added configurable data removal during uninstall.
* Added English and German administration interfaces.
