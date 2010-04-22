Overview
========

The Template Manager module offers functions for bulk-updating templates either in the database based on the filesystem versions or vice versa. The following list shows what operation you can perform on multiple templates and what they do in detail:

`Files => Database`
:   This saves the content of the template files (saved in the file-system) back to the database. Before overwriting the contents in the database, a new revision with that content is created. This ensures that no data in the database is overwritten without a backup. The module also checks whether the version saved as a file differs from your current version in the database and only synchronizes templates that have differences.

`Database => Files`
:   This function is identical with the *Save As File* function in the template editor — except that you can perform it on as many templates as you like simultaneously.

`Delete All Saved Files`
:   This function deletes all templates from the file-system that were saved as files. The setting *Save As File* is set to no for the selected templates.

Installation
============

1.  Download the DC Template Manager module.
2.  Unpack the archive contents to your Desktop or to a location of your choice on your hard-drive.
3.  Copy the `system/modules/dc_template_manager` folder to your `system/modules` directory.
4.  Copy the `language/english/lang.dc_template_manager.php` file to your `system/language/english` directory (or duplicate and modify for any other language).
5.  Copy the `themes/dc_template_manager` directory to your themes directory on your server. This usually resides in `/themes`.
6.  Optionally you can also install the extension in this addon. The extension currently offers no additional functionality, but if you have installed the [LG Addon Updater](http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/) extension it can call home and check for updates of the DC Template Manager module.

    Copy the `extensions/ext.dc_template_manager.php` to your `system/extensions` directory.

Feedback
========

This module/extension combo has been tested to work with ExpressionEngine 1.6.6 and higher. Many of the important template handling functions were taken and adapted from the EE codebase. Should you encounter a bug with a lower EE version, please let us know. However, we encourage you to update your ExpressionEngine installation for your own sake.

Changelog
=========

1.0.1
-----

*   Fixed donation button on module settings page.

1.0.0
-----

*   Added donation button.
*   Fixed wording in some error messages.
*   First public release.

0.9.6
-----

*   Fixed an extension hook bug where this extension would ignore preceding extensions accessing the same hook.
*   Added LG Addon Updater support to the template manager extension.

0.9.5
-----

*   Added extension as a module combo.

0.9.4
-----

*   Fixed a bug where during files > DB sync template data differences would not be shown to the user for revision.
*   Fixed a bug where wrong template data was saved during files sync in revisions.

0.9.3
-----

*   Added bulk-delete template files function.
*   Renamed module from "Updater" to "Manager".

0.9.2
-----

*   Added themes folder and icons.
*   Added template managing functionality to the interface (template overview).
*   Added bulk-saving as file capabilities.
*   Added CHANGELOG.

0.9.1
-----

*   Added bulk-update functionality to synchronize file-bases template changes with the templates in the database.
*   First private beta (testing release).