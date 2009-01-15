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
		
		$this->base = BASE.AMP.'C=modules'.AMP.'M=referrer'.AMP;

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
        global $DSP, $DB, $LANG, $PREFS;
                        
        $DSP->title = $LANG->line('dc_template_updater_module_name');
        $DSP->crumb = $LANG->line('dc_template_updater_module_name'); 
        
        // display message if available
        if ($message != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $message));
        }

        $DSP->body	.=	$DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
        $DSP->body	.=	$DSP->table_row(array(array('text' => 'Templates', 'class' => 'tableHeading', 'colspan' => '2' )));
		$DSP->body	.=	$DSP->table_row(
        					array(
								array('text' => 'Group', 'class' => 'tableHeadingAlt', 'width' => '30%'),
								array('text' => 'Name', 'class' => 'tableHeadingAlt', 'width' => '70%'),
							)
						);

		$DSP->body	.=	$DSP->table_row(
        					array(
								array('text' => $DSP->div('box'). 'Title' .$DSP->div_c(), 'colspan' => '2'),
							)
						);
		
		$sql = "SELECT tg.group_id, tg.group_name, tg.is_site_default, t.template_id, t.template_name, t.template_type, t.hits, t.enable_http_auth, t.save_template_file
				FROM exp_template_groups tg, exp_templates t
				WHERE tg.group_id = t.group_id AND tg.site_id = '". $DB->escape_str($PREFS->ini('site_id')) ."'
				ORDER BY tg.group_order, t.group_id, t.template_name";
				
		$query = $DB->query($sql);
		
		foreach($query->result as $count => $row)
		{
			$cellClass = $count % 2 == 0 ? 'tableCellOne' : 'tableCellTwo';
			$DSP->body	.=	$DSP->table_row(	
	        					array(
									array('text' => $row['group_name'], 'class' => $cellClass, 'width' => '30%'),
									array('text' => $row['template_name'], 'class' => $cellClass, 'width' => '70%'),
								)
							);
		}

		// close table
		$DSP->body	.=	$DSP->table_close();
    }
    // END

    // ----------------------------------------
    //  Private Helper Functions
    // ----------------------------------------
	
}
// END CLASS