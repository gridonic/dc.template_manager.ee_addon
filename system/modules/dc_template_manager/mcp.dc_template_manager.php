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
File: mcp.dc_template_manager.php
-----------------------------------------------------
Purpose: Bulk-Manages EE Templates
=====================================================
*/

if ( ! defined('EXT')) { exit('Invalid file request'); }

class Dc_template_manager_CP {

	var $version		= '1.0.1';
    var $module_name	= 'Dc_template_manager';
	var $base			= '';
	var $base_crumb		= '';
	var $base_title		= '';
	var $theme_path		= '';

	// -------------------------
	//	Constructor
	// -------------------------

	function Dc_template_manager_CP($switch = TRUE)
	{
		global $IN, $DSP, $LANG, $PREFS;

		// Base variables

		$this->base			=	'C=modules'.AMP.'M='. $this->module_name .AMP;
		$this->base_crumb	=	$DSP->anchor(BASE.AMP.$this->base, $LANG->line('dc_template_manager_module_name'));
		$this->base_title	=	$LANG->line('dc_template_manager_module_name');
		$this->theme_path	=	$PREFS->ini('theme_folder_url') . 'dc_template_manager/';

		if ($switch)
		{
			switch($IN->GBL('P'))
			{
				case 'update'		:	$this->update_templates();
                	break;
				default				:	$this->manager_home();
					break;
			}
		}
	}
	// END

	// ----------------------------------------
	//	Module installer
	// ----------------------------------------

	function dc_template_manager_module_install()
	{
		global $DB;

		$sql[] = 	"INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend)
					VALUES ('', '$this->module_name', '$this->version', 'y')";


		foreach ($sql as $query)
		{
			$DB->query($query);
		}

		return true;
	}
	// END

	// ----------------------------------------
	//	Module de-installer
	// ----------------------------------------

	function dc_template_manager_module_deinstall()
	{
		global $DB;

		$query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = '". $this->module_name ."'");

		$sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";
		$sql[] = "DELETE FROM exp_modules WHERE module_name = '". $this->module_name ."'";

		foreach ($sql as $query)
		{
			$DB->query($query);
		}

		return true;
	}
	// END

    // ----------------------------------------
    //  Module Homepage
    // ----------------------------------------

    function manager_home($message = '')
    {
        global $DSP, $DB, $IN, $LANG, $PREFS, $FNS;

        $DSP->title = $this->base_title;
        $DSP->crumb = $this->base_crumb . $DSP->crumb_item($LANG->line('templates'));

        // These variables are only set when one of the pull-down menus is used
        // We use it to construct the SQL query with

        $group_id   = $IN->GBL('group_id', 'GP');

        // Begin building the page output

        $r = $DSP->qdiv('tableHeading', $LANG->line('template_groups_filter'));

        // display message if available
        if ($message != '')
        {
			$r .= $DSP->qdiv('successBox', $DSP->qdiv('success', $message));
        }

        // Declare the "filtering" form

        $r .= $DSP->form_open(array('action' => $this->base));

        // Filtering table
        $select =	$DSP->input_select_header('group_id').
        	        $DSP->input_select_option('', $LANG->line('template_groups')).
					$DSP->input_select_option('', $LANG->line('all'));

		$query = $DB->query("SELECT group_name, group_id FROM exp_template_groups WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ORDER BY group_name");

        foreach ($query->result as $row)
        {
			$group_name = $row['group_name'];
            $select .= $DSP->input_select_option($row['group_id'], $group_name, ($group_id == $row['group_id']) ? 1 : '');
        }

		$select .=  $DSP->input_select_footer().$DSP->nbs(2);
		$select .= 	$DSP->input_submit($LANG->line('submit'), 'submit');
		
		// Add donations button
		
		$select .=	'<div style="float: right;">'
						. '<a style="display:block; width:279px; height:27px; outline: none; margin-top: 0px;'
						. ' background: url(http://www.designchuchi.ch/images/shared/donate.gif) no-repeat 0 0; text-indent: -10000em;"'
	                	. ' href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=3885671"'
						. ' title="'. $LANG->line('donate_title') .'" target="_blank">'
	                	. $LANG->line('donate')
	                	. '</a>'
	                . '</div>';

        $r .=	$DSP->div('box');
        $r .=	$DSP->table_open(array('width' => '100%'));
        $r .=	$DSP->table_row(
        			array(
        				array('text' => $select)
        			)
				);

		// Close filtering form and table

        $r .=	$DSP->table_close();
        $r .=	$DSP->div_c();
        $r .=	$DSP->form_close();

  		// "select all" checkbox

        $r .=	$DSP->toggle();

        $DSP->body_props .= ' onload="magic_check()" ';

        $r .=	$DSP->magic_checkboxes();

        // Declare the "update" form

        $r .=	$DSP->form_open(
        			array(
        				'action'	=> $this->base .'P=update',
        				'name'		=> 'target',
        				'id'		=> 'target'
        			)
        		);

        // Start Templates table

		/*

		THERE STILL SEEMS TO BE A BUG IN THE NON-DEPRECATED $DSP METHODS WHEN USING
		THE TOGGLE FUNCTION AS THE ROW FOR SELECTING ALL SUBSEQUENT ROWS ALSO CHANGES
		THE CLASS AND THUS BACKGROUND COLOR UPON TOGGLING.

		THIS IS WHY FOR THIS SMALL PORTION, WE STILL USE THE DEPRECATED METHODS.

		$r	.=	$DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
        $r	.=	$DSP->table_row(array(array('text' => $LANG->line('templates'), 'class' => 'tableHeading', 'colspan' => '3' )));
		$r	.=	$DSP->table_row(
        			array(
						array('text' => 'Group', 'class' => 'tableHeadingAlt', 'width' => '10%'),
						array('text' => 'Name', 'class' => 'tableHeadingAlt', 'width' => '80%'),
						array('text' => $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\""), 'class' => 'tableHeadingAlt', 'width' => '10%'),
					)
				);
		*/

		$r	.=	$DSP->qdiv('tableHeading', $LANG->line('templates'));
        $r	.=	$DSP->table('tableBorder', '0', '', '100%').
	            $DSP->tr().
        	    $DSP->table_qcell('tableHeadingAlt', $LANG->line('name')).
    	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('group')).
    	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('saved')).
    	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('hits')).
    	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('view')).
       	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('edit')).
       	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('access')).
              	$DSP->table_qcell('tableHeadingAlt', $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"")).
              	$DSP->tr_c();

		// Templates SQL

		$sql = "SELECT tg.group_id, tg.group_name, tg.is_site_default, t.template_id, t.template_name, t.template_type, t.hits, t.enable_http_auth, t.save_template_file
				FROM exp_template_groups tg, exp_templates t
				WHERE tg.group_id = t.group_id AND tg.site_id = '". $DB->escape_str($PREFS->ini('site_id')) ."'";

		// Check for group_id filtering

        if ($group_id)
        {
            $sql .= " AND tg.group_id = $group_id";
        }

		$sql .= " ORDER BY tg.group_order, t.group_id, t.template_name";

		$query = $DB->query($sql);

		// Build table rows

		$i = 0;
		foreach($query->result as $row)
		{
			// Prepare row variables

			$disabled = $row['save_template_file'] != 'y';

			$cellClass	= ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
			$spanClass	= $disabled ? 'defaultLight' : 'default';

			$checkBox	= $DSP->input_checkbox('toggle[]', $row['template_id'], '', ' id="update_box_'.$row['template_id'].'"');
			$icon_text 	= ($disabled ? 'Not ' : ''). 'Saved As File';
			$icon		= '<img src="'. $this->theme_path .'images/disk_black'. ($disabled ? '_disabled' : '') .'.png" alt="'. $icon_text .'" title="'. $icon_text .'" />';

			$img_type = ($row['template_name'] == 'index') ? 'index' : $row['template_type'];
			$hidden_indicator = ($PREFS->ini('hidden_template_indicator') === FALSE) ? '.' : $PREFS->ini('hidden_template_indicator');
			$hidden = (substr($row['template_name'], 0, 1) == $hidden_indicator) ? '_hidden' : '';

			$edit_img 	= "<img src='".PATH_CP_IMG."{$img_type}_icon{$hidden}.png' border='0' width='16' height='16' alt='".$LANG->line('view')."' />";
			$editurl	= BASE.AMP.'C=templates'.AMP.'M=edit_template'.AMP.'id='.$row['template_id'].AMP.'tgpref='.$row['group_id'];
			$edit_group	= BASE.AMP.'C=templates'.AMP.'M=edit_preferences'.AMP.'id='.$row['group_id'].AMP.'tgpref='.$row['group_id'];
			$viewurl	= $this->_get_view_url($row['template_type'], $row['group_name'], $row['template_name']);
			$key_url	= BASE.AMP.'C=templates'.AMP.'M=template_access'.AMP.'id='.$row['template_id'].AMP.'tgpref='.$row['group_id'];


			// Table row

			$r	.=	$DSP->table_row(
						array(
							array('text' =>	$DSP->span($spanClass).$edit_img.NBS.NBS.$DSP->anchor($editurl, '<b>'.$row['template_name'].'</b>').$DSP->span_c(), 'class' => $cellClass, 'width' => '40%'),
							array('text' => $DSP->span($spanClass).$DSP->anchor($edit_group, $row['group_name']).$DSP->span_c(), 'class' => $cellClass, 'width' => '20%'),
							array('text' => $icon, 'class' => $cellClass, 'width' => '3%'),
							array('text' => $row['hits'], 'class' => $cellClass, 'width' => '3%'),
							array('text' => $DSP->pagepop($viewurl, $LANG->line('view')), 'class' => $cellClass, 'width' => '3%'),
							array('text' => $DSP->anchor($editurl, $LANG->line('edit')), 'class' => $cellClass, 'width' => '3%'),
							array('text' => $DSP->anchor($key_url, $LANG->line('access')), 'class' => $cellClass, 'width' => '3%'),
							array('text' => $checkBox, 'class' => $cellClass, 'width' => '2%'),
						)
					);
		}

		// close table
		$r	.=	$DSP->table_close();

		// Action select table with submit button

        $can_save_templates = ($PREFS->ini('save_tmpl_files') == 'y' AND $PREFS->ini('tmpl_file_basepath') != '');

		$r	.=	$DSP->table_open(array('width' => '100%'));
		$submit	=	$DSP->input_submit($LANG->line('submit'));
        $submit	.=	NBS.$DSP->input_select_header('action');

        $submit	.=	$DSP->input_select_option('files_database', $LANG->line('files_database')).
        			($can_save_templates ? $DSP->input_select_option('database_files', $LANG->line('database_files')) : '').
        			($can_save_templates ? $DSP->input_select_option('delete_files', $LANG->line('delete_files')) : '').
        			$DSP->input_select_footer();

		$r	.=	$DSP->table_row(
					array(
						array('text' => $submit, 'class' => 'defaultRight')
					)
				);

		$r	.=	$DSP->table_close();
		$r	.=	$DSP->form_close();


		$DSP->body = $r;
    }
    // END

    // ----------------------------------------
    //  Update Templates Page
    // ----------------------------------------

    function update_templates()
    {
    	global $DSP, $LANG, $DB, $PREFS, $IN;

    	// Check if the page was accessed in a good way

    	if (!$IN->GBL('action', 'POST'))
        {
            return $this->manager_home();
        }

    	// Output variable

    	$r = '';

    	// Action variables

	    $action			= $IN->GBL('action', 'POST');
	    $toggle			= $IN->GBL('toggle', 'POST');
	    $template_ids	= $IN->GBL('update', 'POST');

	    $bulk_sync		= $action == 'files_database';
	    $bulk_save		= $action == 'database_files';
	    $bulk_delete	= $action == 'delete_files';

    	// define magic constants for changeset array

    	define('ID_INDEX', 		0);
    	define('NAME_INDEX', 	1);
    	define('GROUP_INDEX', 	2);

        $DSP->title = $this->base_title . ' | ' .$LANG->line('update_templates');
        $DSP->crumb = $this->base_crumb . $DSP->crumb_item($LANG->line('update_templates'));

	    // ----------------------------------------
	    //  Trigger Templates Update
	    // ----------------------------------------

    	if ($template_ids)
    	{
			switch ($action)
			{
				case 'files_database'	:	$errors = $this->_update_from_files($template_ids);
					break;
				case 'database_files'	:	$errors = $this->_save_as_files($template_ids);
					break;
				case 'delete_files'		:	$errors = $this->_delete_files($template_ids);
					break;
				default					:	$errors = array();
			}

    		// No errors, redirect back to homepage

    		if (empty($errors))
    		{
	    		return $this->manager_home($LANG->line('update_success'));
    		}

    		// Show errors else...

    		$r .=	$DSP->qdiv('alertHeading', $LANG->line('update_templates') .' &mdash; '. $LANG->line('errors'));
    		$r .=	$DSP->div('box');
  			$r .=	$DSP->qdiv('alert', '<ul><li>'. implode($errors, '</li><li>') .'</li></ul>');
  			$r .= 	$DSP->div_c();

    		return $DSP->body = $r;
    	}

        // Check for selected templates

        if (!$toggle)
		{
			return $DSP->error_message('Please chose at least one template.');
		}

		// Get template data

		$query = $DB->query(
					"SELECT t.group_id, t.template_id, t.template_name, t.template_data, t.save_template_file, g.group_name
					FROM exp_templates AS t
					INNER JOIN exp_template_groups AS g
					ON t.group_id = g.group_id
					WHERE template_id IN (". implode($toggle, ',') .")"
				 );

		$changeset = array();

	    // ----------------------------------------
	    //  Action?
	    // ----------------------------------------

		if ($query->num_rows > 0)
		{
        	// Build array of templates

        	foreach ($query->result as $row)
        	{
        		// Load file template contents if we update DB templates

        		$differs = FALSE;

        		if ($bulk_sync && $template_data_file = $this->_load_from_file($row['group_name'], $row['template_name']))
        		{
					// Compare DB version with File version
					$differs = ($template_data_file != $row['template_data']);
        		}

        		// Put templates in changeset if we either sync, bulk-save or delete

        		if($bulk_sync && $differs || $bulk_save || $bulk_delete && $row['save_template_file'] == 'y')
        		{
    				$changeset[] =	array(
						ID_INDEX		=>	$row['template_id'],
						NAME_INDEX		=>	$row['template_name'],
						GROUP_INDEX		=>	$row['group_name']
					);
        		}
        	}
		}


        // Empty changeset and synching

        if (($bulk_sync || $bulk_delete) && empty($changeset))
        {
       		switch ($action)
			{
				case 'files_database'	:	return $DSP->body = $this->_simple_message($LANG->line('update_templates'), $LANG->line('templates_no_changes'));
				case 'delete_files'		:	return $DSP->body = $this->_simple_message($LANG->line('update_templates'), $LANG->line('templates_none_saved'));
			}
        }

        // Build confirm form

        $r .=	$DSP->form_open(array('action'	=> $this->base .'P=update'));
        $r .=	$DSP->input_hidden('action', $action);

        $i = 0; $list = '';

	    // ----------------------------------------
	    //  Build Templates Confirm List
	    // ----------------------------------------

        foreach ($changeset as $template)
        {
            $r .=		$DSP->input_hidden('update[]', $template[ID_INDEX]);
            $list .=	'<li>'. $DSP->qspan('highlight', $template[NAME_INDEX]) .' in group <em>'. $template[GROUP_INDEX] .'</em></li>';
            $i++;
        }

		// Switch messages depending on action

		switch ($action)
		{
			case 'files_database'	:	$msg = $LANG->line('revisions_message');
				break;
			case 'database_files'	:	$msg = $LANG->line('overwrite_message');
				break;
			case 'delete_files'		:	$msg = $LANG->line('delete_message');
				break;
			default					:	$msg = '';
		}

        $r .=	$DSP->qdiv('alertHeading', $LANG->line('update_templates'));
        $r .=	$DSP->div('box');
		$r .=	'<b>'. (($i == 1) ? $LANG->line('update_template_confirm') : $LANG->line('update_templates_confirm')) .'</b>';
		$r .=	'<ul>'. $list .'</ul>';
		$r .= 	$DSP->qdiv('itemWrapper', '<b>'. $msg .'</b>');
		$r .=	$DSP->qdiv('itemWrapper', $DSP->qdiv('alert', $LANG->line('action_can_not_be_undone')));
        $r .=	$DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('update'))).
              	$DSP->div_c().
              	$DSP->form_close();

		$DSP->body = $r;
    }

    // ----------------------------------------
    //  Private Helper Functions
    // ----------------------------------------

    function _simple_message($title = '', $msg = '', $n = 1)
    {
    	global $DSP, $LANG;

    	$r = '';

    	if (empty($msg) || empty($title)) return $r;

    	$r .=	$DSP->qdiv('tableHeading', $title);
    	$r .=	$DSP->div('box') .$DSP->qdiv('defaultBold', $msg);
    	$r .=   BR.$DSP->nl(2)."<a href='javascript:history.go(-{$n})' style='text-transform:uppercase;'>&#171; <b>".$LANG->line('back')."</b></a>";
    	$r .=	$DSP->div_c();

    	return $r;
    }

    /**
     * Updates the DB templates data based on the saved template files.
     * The $_POST array must contain a set of valid template IDs.
     */
    function _update_from_files($template_ids)
    {
    	global $LOC, $SESS, $DB, $FNS;

    	// Errors array

    	$errors = array();

    	// Check if the parameter is right
		if (!is_array($template_ids) || empty($template_ids))
		{
			return $errors[] = 'The parameter to this function must contain a non-empty array of template ids.';
		}

		// Loop through IDs

		foreach ($template_ids as $id)
		{
			// Get group and file name for the template id

			$query = $DB->query(
				"SELECT t.template_name, t.template_notes, t.template_data, t.save_template_file, g.group_name
				FROM exp_templates AS t
				INNER JOIN exp_template_groups AS g
				ON t.group_id = g.group_id
				WHERE t.template_id = '".$DB->escape_str($id)."'"
			);

			if ($query->num_rows <= 0)
			{
				return $errors[] = 'Database query error when trying to load template from database.';
			}

			// Get template data from file

			if ($template_data_file = $this->_load_from_file($query->row['group_name'], $query->row['template_name']))
			{
				/** -------------------------------
				/**  Save revision cache
				/** -------------------------------*/

		        $data = array(
					'tracker_id' 		=> '',
					'item_id'    		=> $id,
					'item_table'		=> 'exp_templates',
					'item_field'		=> 'template_data',
					'item_data'			=> $query->row['template_data'],
					'item_date'  		=> $LOC->now,
					'item_author_id'	=> $SESS->userdata['member_id']
				);

				$DB->query($DB->insert_string('exp_revision_tracker', $data));

				/** -------------------------------
				/**  Update Template in DB
				/** -------------------------------*/

		        $DB->query(
		        	$DB->update_string('exp_templates',
		        		array(
		        			'template_data'			=>	$template_data_file,
		        			'edit_date'				=>	$LOC->now,
		        			'last_author_id'		=>	$SESS->userdata['member_id'],
		        			'save_template_file'	=>	$query->row['save_template_file'],
		        			'template_notes' 		=>	$query->row['template_notes']
		        		),  "template_id = '$id'"
		        	)
		        );

		        // Clear tag caching if we find the cache="yes" parameter in the template

		        if (preg_match("#\s+cache=[\"']yes[\"']\s+#", stripslashes($template_data_file)))
		        {
		            $FNS->clear_caching('tag');
		        }

		        // Clear cache files

		        $FNS->clear_caching('all');
			}
		}

    	return $errors;
    }

    /**
     * Bulk-saves templates as files and returns errors that may have been
     * triggered in this method, either because of saving the file did not work
     * or other, settings or database dependent data was wrong.
     */
	function _save_as_files($template_ids)
	{
    	global $PREFS, $LOC, $SESS, $DB;

    	// Errors array

    	$errors = array();

		if ($PREFS->ini('tmpl_file_basepath') == '' OR $PREFS->ini('save_tmpl_files') == 'n')
		{
			return $errors[] = 'Please check your template settings. Either you have save template as files turned off or the basepath for templates is not set.';
		}

		// Loop through IDs

		foreach ($template_ids as $id)
		{
			// Get group and file name for the template id

			$query = $DB->query(
				"SELECT t.template_name, t.template_notes, t.save_template_file, t.template_data, g.group_name
				FROM exp_templates AS t
				INNER JOIN exp_template_groups AS g
				ON t.group_id = g.group_id
				WHERE t.template_id = '".$DB->escape_str($id)."'"
			);

			if ($query->num_rows <= 0)
			{
				return $errors[] = 'Database query error when trying to load template from database.';
			}

			$tdata = array(
				'template_id'		=> $id,
				'template_group'	=> $query->row['group_name'],
				'template_name'		=> $query->row['template_name'],
				'template_data'		=> $query->row['template_data'],
				'edit_date'			=> $LOC->now,
				'last_author_id'	=> $SESS->userdata['member_id']
			);

			if (!$this->_update_template_file($tdata))
			{
				$errors[] = "Could not save template ". $query->row['template_name'];
			}
			else
			{
				// If the file was successfully saved, update the "save_template_file" column in DB

		        $DB->query($DB->update_string('exp_templates', array('save_template_file' => 'y'), "template_id = '$id'"));
			}
		}

		return $errors;
	}


    /**
     * Bulk-deletes saved template files based on the given template IDs.
     * This function also sets the template "save_template_file" attribute
     * to "n" and thus disables saving as file for each template.
     */
	function _delete_files($template_ids)
	{
    	global $PREFS, $DB;

    	// Errors array

    	$errors = array();

		if ($PREFS->ini('tmpl_file_basepath') == '' OR $PREFS->ini('save_tmpl_files') == 'n')
		{
			return $errors[] = 'Please check your template settings. Either you have save template as files turned off or the basepath for templates is not set.';
		}

		// Loop through IDs

		foreach ($template_ids as $id)
		{
			// Get group and file name for the template id

			$query = $DB->query(
				"SELECT t.template_name, t.template_notes, t.save_template_file, t.template_data, g.group_name
				FROM exp_templates AS t
				INNER JOIN exp_template_groups AS g
				ON t.group_id = g.group_id
				WHERE t.template_id = '".$DB->escape_str($id)."'"
			);

			if ($query->num_rows <= 0)
			{
				return $errors[] = 'Database query error when trying to load template from database.';
			}

			$basepath = $PREFS->ini('tmpl_file_basepath');

			if ( ! ereg("/$", $basepath)) $basepath .= '/';

			$basepath .= $query->row['group_name'].'/'.$query->row['template_name'].'.php';

			if (!@unlink($basepath))
			{
				$errors[] = 'Could not delete '. $basepath;
			}
			else
			{
				// We were able to delete, update database parameter

				$DB->query($DB->update_string('exp_templates', array('save_template_file' => 'n'), "template_id = '$id'"));
			}

		}

		return $errors;
	}

    /**
     * Loads the template with the given group and template name
     * saved as a file and returns the content of the template or
     * FALSE if the file could not be loaded.
     */
    function _load_from_file($group_name, $template_name)
    {
    	global $PREFS, $DSP;

		$basepath = $PREFS->ini('tmpl_file_basepath');

		if ( ! ereg("/$", $basepath)) $basepath .= '/';

		$basepath .= $group_name.'/'.$template_name.'.php';

		// Open Template File

		if ($file = $DSP->file_open($basepath))
		{
			return $file;
		}

		return false;
    }

	/** -----------------------------
	/**  Update Template File
	/** -----------------------------*/

	function _update_template_file($data)
	{
		global $PREFS, $DB;

	    if ( ! $this->_template_access_privs(array('template_id' => $data['template_id'])))
	    {
	    	return FALSE;
	    }

		if ($PREFS->ini('save_tmpl_files') == 'n' OR $PREFS->ini('tmpl_file_basepath') == '')
	    {
			return FALSE;
			}

			$basepath = $PREFS->ini('tmpl_file_basepath', TRUE);

		if ( ! @is_dir($basepath) OR ! is_writable($basepath))
		{
			return FALSE;
		}

		$basepath .= $data['template_group'];

		if ( ! @is_dir($basepath))
		{
			if ( ! @mkdir($basepath, 0777))
			{
				return FALSE;
			}
			@chmod($basepath, 0777);
		}

	    if ( ! $fp = @fopen($basepath.'/'.$data['template_name'].'.php', 'wb'))
	    {
	    	return FALSE;
	    }
	    else
	    {
			flock($fp, LOCK_EX);
			fwrite($fp, stripslashes($data['template_data']));
			flock($fp, LOCK_UN);
			fclose($fp);

			@chmod($basepath.'/'.$data['template_name'].'.php', 0777);
		}

		return TRUE;
	}
	/* END */

    /** -----------------------------
    /**  Verify access privileges
    /** -----------------------------*/

    function _template_access_privs($data = '')
    {
    	global $SESS, $DB;

    	// If the user is a Super Admin, return true

		if ($SESS->userdata['group_id'] == 1)
		{
    		return TRUE;
		}

    	$template_id = '';
    	$group_id	 = '';

    	if (is_array($data))
    	{
    		if (isset($data['template_id']))
    		{
    			$template_id = $data['template_id'];
    		}

    		if (isset($data['group_id']))
    		{
    			$group_id = $data['group_id'];
    		}
    	}


        if ($group_id == '')
        {
        	if ($template_id == '')
        	{
        		return FALSE;
        	}
        	else
        	{
           		$query = $DB->query("SELECT group_id, template_name FROM exp_templates WHERE template_id = '".$DB->escape_str($template_id)."'");

           		$group_id = $query->row['group_id'];
            }
        }


        if ($SESS->userdata['tmpl_group_id'] == 0)
        {
			$access = FALSE;

			foreach ($SESS->userdata['assigned_template_groups'] as $key => $val)
			{
				if ($group_id == $key)
				{
					$access = TRUE;
					break;
				}
			}

			if ($access == FALSE)
			{
				return FALSE;
			}
        }
        else
        {
			if ($group_id != $SESS->userdata['tmpl_group_id'] )
			{
				return FALSE;
			}
        }

		return TRUE;
    }
    /* END */

    /**
     * Retrieves the "view" URL for a given template.
     */
    function _get_view_url($template_type, $group_name, $template_name)
    {
    	global $FNS, $PREFS;

   		$qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
		$sitepath = $FNS->fetch_site_index(0, 0).$qs.'URL='.$FNS->fetch_site_index();

        if ( ! ereg("/$", $sitepath))
        	$sitepath .= '/';

		$viewurl = $sitepath;

		if ($template_type == 'css')
		{
			$viewurl  = substr($viewurl, 0, -1);
			$viewurl .= $qs.$group_name.'/'.$template_name.'/';
		}
		else
		{
			$viewurl .= $group_name.'/'.$template_name.'/';
		}

		return $viewurl;
    }

}
// END CLASS