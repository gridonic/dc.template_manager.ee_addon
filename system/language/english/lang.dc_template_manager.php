<?php
//SVN $Id$

/*
=====================================================
DC Template Manager
-----------------------------------------------------
http://www.designchuchi.ch/
-----------------------------------------------------
Copyright (c) 2008 - today Designchuchi
=====================================================
THIS MODULE IS LICENSED UNDER THE  CREATIVE COMMONS
ATTRIBUTION-SHARE ALIKE 3.0 UNPORTED LICENSE. PLEASE
READ THE LICENSE UNDER CAREFULLY.
http://creativecommons.org/licenses/by-sa/3.0/
=====================================================
File: lang.dc_template_manager.php
-----------------------------------------------------
Purpose: Bulk-Manages EE Templates
=====================================================
*/

$L = array(

	//----------------------------------------
	// Required for MODULES page
	//----------------------------------------
	
	"dc_template_manager_module_name"			=>	"DC Template Manager",
	"dc_template_manager_module_description"	=>	"Bulk-Manages Templates in ExpressionEngine",
	
	//----------------------------------------
	// Required for MODULES page
	//----------------------------------------
	
	"templates"					=> 	"Templates",
	"update_templates"			=> 	"Update Templates",
	"errors"					=> 	"Errors Occured",
	"templates_no_changes"		=> 	"The database templates you selected are in sync with the saved files.",
	"templates_none_saved"		=> 	"None of the selected templates are saved as files.",
	"template_groups"			=> 	"Template Groups",
	"template_groups_filter"	=> 	"Filter by Template Groups",
	"template_not_saved" 		=> 	"The following errors occured when trying to save template files.",
	
	"update_template_confirm"	=> 	"The following template will be updated",
	"update_templates_confirm"	=> 	"The following templates will be updated",
	
	"revisions_message"			=> 	"The new contents of the template in the database will be saved as a new revision. You will not lose any of your former template data saved in the database.",
	"overwrite_message"			=> 	"Existing template files will be overwritten with the template data from database. ".
									"Templates not saved as file will be saved in a new file and the template setting <em>Save Template as File</em> will be set.",
	"delete_message"			=>	"Template files in your templates directory will be deleted for the listed templates. ".
									"The template setting <em>Save Template as File</em> will be set to <em>No</em> for all selected templates.",
	"update_success"			=>	"All templates updated successfully.",
	
	/* Short Messages */
	
	"group"		=>	"Group",
	"name"		=>	"Name",
	"hits"		=>	"Hits",
	"view"		=>	"View",
	"edit"		=>	"Edit",
	"access"	=>	"Access",
	"saved"		=>	"Saved",
	
	/* Drop-Downs */
	
	"files_database"	=>	"Files => Database",
	"database_files"	=>	"Database => Files",
	"delete_files"		=>	"Delete All Saved Files",
	
	//----------------------------------------
	// Required for SETTINGS page (extension)
	//----------------------------------------
	'template_editor_access'		=> 'Template Editor Access',
	'exlude_members_description'	=> 'Write the IDs of members for which the template editor in the control panel should be disabled. Please use the following format <b>3,19,7</b> (list of IDs separated by commas).',
	'check_for_updates_title'		=> 'Check for DC Template Manager Extension updates?',
	'check_for_updates_info'		=> 'DC Template Manager Extension can call home (<a href="http://www.designchuchi.ch">http://www.designchuchi.ch</a>) and check to see if the extension has been updated.',
	'check_for_updates_label'		=> 'Would you like this extension to check for updates and display them on your CP homepage?',

//----------------------------------------

// END
''=>''
);