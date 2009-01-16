<?php
//SVN $Id$

/*
=====================================================

-----------------------------------------------------
http://www.designchuchi.ch/
-----------------------------------------------------
Copyright (c) 2008 - today Designchuchi
=====================================================
THIS MODULE IS PROVIDED "AS IS" WITHOUT WARRANTY OF
ANY KIND OR NATURE, EITHER EXPRESSED OR IMPLIED,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE,
OR NON-INFRINGEMENT.
=====================================================
File:
-----------------------------------------------------
Purpose:
=====================================================
*/

if ( ! defined('EXT')) { exit('Invalid file request'); }

class Dc_template_updater_CP {

	var $version		= '1.0';
    var $module_name	= 'Dc_template_updater';
	var $base			= '';
	var $base_crumb		= '';
	var $base_title		= '';

	// -------------------------
	//	Constructor
	// -------------------------

	function Dc_template_updater_CP($switch = TRUE)
	{
		global $IN, $DSP, $LANG;

		// Base variables
		
		$this->base =		'C=modules'.AMP.'M='. $this->module_name .AMP;
		$this->base_crumb =	$DSP->anchor(BASE.AMP.$this->base, $LANG->line('dc_template_updater_module_name'));
		$this->base_title = $LANG->line('dc_template_updater_module_name');

		if ($switch)
		{
			switch($IN->GBL('P'))
			{
				case 'update'		:	$this->update_templates();
                	break;
				default				:	$this->updater_home();
					break;
			}
		}
	}
	// END

	// ----------------------------------------
	//	Module installer
	// ----------------------------------------

	function dc_template_updater_module_install()
	{
		global $DB;

		$sql[] = "INSERT INTO exp_modules (module_id,
										   module_name,
										   module_version,
										   has_cp_backend)
										   VALUES
										   ('',
										   '$this->module_name',
										   '$this->version',
										   'y')";


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

	function dc_template_updater_module_deinstall()
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

    function updater_home($message = '')
    {
        global $DSP, $DB, $IN, $LANG, $PREFS;

        $DSP->title = $this->base_title;
        $DSP->crumb = $this->base_crumb . $DSP->crumb_item($LANG->line('dc_templates'));

        // These variables are only set when one of the pull-down menus is used
        // We use it to construct the SQL query with

        $group_id   = $IN->GBL('group_id', 'GP');

        // Begin building the page output

        $r = $DSP->qdiv('tableHeading', $LANG->line('dc_template_groups'));

        // display message if available
        if ($message != '')
        {
			$r .= $DSP->qdiv('successBox', $DSP->qdiv('success', $message));
        }

        // Declare the "filtering" form

        $r .= $DSP->form_open(array('action' => $this->base));

        // Filtering table
        $select =	$DSP->input_select_header('group_id').
        	        $DSP->input_select_option('', $LANG->line('dc_template_groups')).
					$DSP->input_select_option('', $LANG->line('all'));

		$query = $DB->query("SELECT group_name, group_id FROM exp_template_groups WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ORDER BY group_name");

        foreach ($query->result as $row)
        {
			$group_name = $row['group_name'];
            $select .= $DSP->input_select_option($row['group_id'], $group_name, ($group_id == $row['group_id']) ? 1 : '');
        }

		$select .=  $DSP->input_select_footer().$DSP->nbs(2);
		$select .= 	$DSP->input_submit($LANG->line('submit'), 'submit');

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
        $r	.=	$DSP->table_row(array(array('text' => $LANG->line('dc_templates'), 'class' => 'tableHeading', 'colspan' => '3' )));
		$r	.=	$DSP->table_row(
        			array(
						array('text' => 'Group', 'class' => 'tableHeadingAlt', 'width' => '10%'),
						array('text' => 'Name', 'class' => 'tableHeadingAlt', 'width' => '80%'),
						array('text' => $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\""), 'class' => 'tableHeadingAlt', 'width' => '10%'),
					)
				);
		*/

		$r	.=	$DSP->qdiv('tableHeading', $LANG->line('dc_templates'));
        $r	.=	$DSP->table('tableBorder', '0', '', '100%').
	            $DSP->tr().
    	        $DSP->table_qcell('tableHeadingAlt', $LANG->line('group')).
        	    $DSP->table_qcell('tableHeadingAlt', $LANG->line('name')).
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

		$i = 0;
		foreach($query->result as $row)
		{
			$disabled = $row['save_template_file'] != 'y';
			$cellClass = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
			$spanClass = $disabled ? 'defaultLight' : 'default';
			$checkBox = $DSP->input_checkbox('toggle[]', $row['template_id'], '', ' id="update_box_'.$row['template_id'].'"'. ($disabled ? ' disabled="disabled"' : ''));
			
			$r	.=	$DSP->table_row(
						array(
							array('text' => $DSP->span($spanClass) . $row['group_name'] .$DSP->span_c(), 'class' => $cellClass, 'width' => '10%'),
							array('text' => $DSP->span($spanClass) . $row['template_name'] .$DSP->span_c(), 'class' => $cellClass, 'width' => '80%'),
							array('text' => $checkBox, 'class' => $cellClass, 'width' => '10%'),
						)
					);
		}

		// close table
		$r	.=	$DSP->table_close();
		
		// Action select table with submit button
		
		$r	.=	$DSP->table_open(array('width' => '98%'));
		$submit	=	$DSP->input_submit($LANG->line('submit'));
        $submit	.=	NBS.$DSP->input_select_header('action');
        
        $submit	.=	$DSP->input_select_option('files_database', $LANG->line('files_database')).
        		$DSP->input_select_option('database_files', $LANG->line('database_files')).
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
    	
    	// define magic constants for changeset array
    	
    	define('ID_INDEX', 		0);
    	define('NAME_INDEX', 	1);
    	define('GROUP_INDEX', 	2);
    	
    	
    	// output variable
    	$r = '';
    	
/*
		echo('<pre>');
		print_r($_POST);
		echo('</pre>');
*/
		
        $DSP->title = $this->base_title . ' | ' .$LANG->line('dc_templates_update');
        $DSP->crumb = $this->base_crumb . $DSP->crumb_item($LANG->line('dc_templates_update'));
		
        if (!$IN->GBL('toggle', 'POST'))
		{
			return $DSP->error_message('Please chose at least one template.');
		}
		
		// Get template data
		
		$query = $DB->query(
					"SELECT t.group_id, t.template_id, t.template_name, t.template_data, g.group_name
					FROM exp_templates AS t
					INNER JOIN exp_template_groups AS g 
					ON t.group_id = g.group_id
					WHERE template_id IN (". implode($_POST['toggle'], ',') .")"
				 );

        if ($query->num_rows > 0 && $PREFS->ini('save_tmpl_files') == 'y' AND $PREFS->ini('tmpl_file_basepath') != '')
        {
        
        	// Build array of templates with changes
        	
        	$changeset = array();
        
        	foreach ($query->result as $row)
        	{
				$basepath = $PREFS->ini('tmpl_file_basepath');

				if ( ! ereg("/$", $basepath)) $basepath .= '/';

				$basepath .= $row['group_name'].'/'.$row['template_name'].'.php';
			
				// Open Template File
				
				if ($file = $DSP->file_open($basepath))
				{
					$template_data_file = $file;
					
					// Compare DB version with File version
					
					if ($template_data_file != $row['template_data'])
					{
						$changeset[] =	array(
											ID_INDEX	=>	$row['template_id'],
											NAME_INDEX	=>	$row['template_name'],
											GROUP_INDEX	=>	$row['group_name']
										);
					}
				}
        	}
        }
        
        // Empty changeset, no differences between files and DB
        
        if (empty($changeset))
        {
        	return $DSP->body = $this->_simple_message($LANG->line('dc_templates_update'), $LANG->line('dc_templates_no_changes'));
        }
        
        // Show templates that will be updated
        
        $r .=	$DSP->form_open(array('action'	=> $this->base .'P=update'));
        
        $i = 0; $list = '';
        
        foreach ($changeset as $template)
        {        
            $r .=		$DSP->input_hidden('update[]', $template[ID_INDEX]);
            $list .=	'<li>'. $template[NAME_INDEX] .' in group '. $template[GROUP_INDEX] .'</li>';
            $i++;
        }
        
        $r .=	$DSP->qdiv('alertHeading', $LANG->line('dc_templates_update'));
        $r .=	$DSP->div('box');
		$r .=	'<b>'. (($i == 1) ? $LANG->line('update_template_confirm') : $LANG->line('update_templates_confirm')) .'</b>';
		$r .=	'<ul>'. $list .'</ul>';
		$r .=	$DSP->qdiv('itemWrapper', $DSP->qdiv('alert', $LANG->line('action_can_not_be_undone')));
        $r .=	$DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('update'))).
              		$DSP->div_c().
              	 	$DSP->form_close();

		 
		echo('<pre>');
		print_r($changeset);
		echo('</pre>');
		
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

}
// END CLASS
