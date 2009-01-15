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
	
	// -------------------------
	//	Constructor
	// -------------------------

	function Dc_template_updater_CP($switch = TRUE)
	{
		global $IN;
		
		$this->base = BASE.AMP.'C=modules'.AMP.'M='. $this->module_name .AMP;

		if ($switch)
		{
			switch($IN->GBL('P'))
			{
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
                        
        $DSP->title = $LANG->line('dc_template_updater_module_name');
        $DSP->crumb = $DSP->anchor($this->base, $LANG->line('dc_template_updater_module_name')) . $DSP->crumb_item($LANG->line('dc_templates')); 
        
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
        
        $r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M='. $this->module_name));
        
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
        
        // Start table

		$r	.=	$DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
        $r	.=	$DSP->table_row(array(array('text' => $LANG->line('dc_templates'), 'class' => 'tableHeading', 'colspan' => '2' )));
		$r	.=	$DSP->table_row(
        			array(
						array('text' => 'Group', 'class' => 'tableHeadingAlt', 'width' => '10%'),
						array('text' => 'Name', 'class' => 'tableHeadingAlt', 'width' => '90%'),
					)
				);
		
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
		
		foreach($query->result as $count => $row)
		{
			$cellClass = $count % 2 == 0 ? 'tableCellOne' : 'tableCellTwo';
			$r	.=	$DSP->table_row(	
	        					array(
									array('text' => $row['group_name'], 'class' => $cellClass, 'width' => '10%'),
									array('text' => $row['template_name'], 'class' => $cellClass, 'width' => '90%'),
								)
							);
		}

		// close table
		$r	.=	$DSP->table_close();
		
		$DSP->body = $r;
    }
    // END

    // ----------------------------------------
    //  Private Helper Functions
    // ----------------------------------------
	
}
// END CLASS