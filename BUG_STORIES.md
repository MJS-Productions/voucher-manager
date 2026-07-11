# Bug Stories

Because every great project has a few stories.

Some are planned.

Most are not.

## VM-001 - The Phantom Update

**Version:** 0.4.0-alpha  
**Status:** Resolved

WordPress installed the first 0.4.0 package beside the existing plugin.

**Lesson:** Plugin identity and ZIP structure are part of the product.

## VM-002 - The Missing Composer Passenger

**Version:** 0.4.0-alpha  
**Status:** Resolved

The update package expected `vendor/autoload.php`, but the release ZIP did not contain Composer dependencies.

**Lesson:** A release artifact must be self-contained and tested as an artifact, not only as source code.
