<?php
//SVN $Id$

/*
=====================================================
DC Template Manager Extension
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
File: ext.dc_template_manager.php
-----------------------------------------------------
Purpose: Makes categories required for weblogs.
=====================================================
*/

if ( ! defined('EXT')) exit('Invalid file request');

// define constants
if (!defined('DC_TEMPLATE_MGR_VERSION'))
{
	define("DC_TEMPLATE_MGR_VERSION",	'1.0.1');	
	define("DC_TEMPLATE_MGR_ID",		'DC Template Manager');
	define("DC_TEMPLATE_MGR_DOCS",		'http://www.designchuchi.ch/index.php/blog/comments/dc-template-manager');
}

/**
 * Manages extension activation, deactivation and upgrading, links class 
 * methods to ExpressionEngine hooks and implements the administration interface.
 */
class Dc_template_manager {

	var $settings		= array();

	var $name			= 'DC Template Manager';
	var $version		= DC_TEMPLATE_MGR_VERSION;
	var $description	= 'Checks for updates of the DC Template Manager module.';
	var $settings_exist = 'y';
	var $docs_url		= DC_TEMPLATE_MGR_DOCS;

	// -------------------------------
	//	Constructor - Extensions use this for settings
	// -------------------------------
	function Dc_template_manager($settings='')
	{
		$this->settings = $this->_get_site_settings($settings);
	}
	
	// --------------------------------
	//	Activate Extension
	// --------------------------------

	function activate_extension()
	{
		global $DB;

		// hooks array
		$hooks = array(
			'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon'
		);

		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions',
				array(
					'extension_id'	=> '',
					'class'			=> get_class($this),
					'method'		=> $method,
					'hook'			=> $hook,
					'settings'		=> '',
					'priority'		=> 10,
					'version'		=> $this->version,
					'enabled'		=> 'y'
				)
			);
		}

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}

		return TRUE;
	}
	
	// --------------------------------
	//	Update Extension
	// --------------------------------
	function update_extension($current = '')
	{
		global $DB;

		//	=============================================
		//	Is Current?
		//	=============================================
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$sql[] = "UPDATE exp_extensions SET version = '" . $DB->escape_str($this->version) . "' WHERE class = '" . get_class($this) . "'";

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
	}
	
	// --------------------------------
	//	Disable Extension
	// --------------------------------
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}

	//	========================================================================
	//	Settings Form
	//	========================================================================
	
	/**
	 * Settings Form
	 *
	 * Construct the custom settings form.
	 *
	 * Look and feel based on LG Addon Updater's settings form.
	 *
	 * @param  array   $current	  Current extension settings (not site-specific)
	 * @see	   http://expressionengine.com/docs/development/extensions.html#settings
	 * @since  version 1.0.0
	 */
	function settings_form($current)
	{
		// create a local variable for the site settings
		$current = $this->_get_site_settings($current);
		
		global $DB, $DSP, $LANG, $IN;

		// Breadcrumbs
		$DSP->crumbline = TRUE;

		$DSP->title = $LANG->line('extension_settings');
		$DSP->crumb = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities'))
						. $DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')))
						. $DSP->crumb_item($this->name);

		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
		
	    $DSP->body = '';

		// Donations button
	    $DSP->body .= '<div style="float:right;">'
	                . '<a style="display:block; margin:0 10px 0 0; width:279px; height:27px; outline: none;'
					. ' background: url(http://www.designchuchi.ch/images/shared/donate.gif) no-repeat 0 0; text-indent: -10000em;"'
	                . ' href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=3885671"'
					. ' title="'. $LANG->line('donate_title') .'" target="_blank">'
	                . $LANG->line('donate')
	                . '</a>'
	                . '</div>';

		// Form header
		$DSP->body .= "<h1>{$this->name} <small>{$this->version}</small></h1>";
		$DSP->body .= $DSP->form_open(
							array(
								'action'	=> 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
								'name'		=> 'settings_example',
								'id'		=> 'settings_example'
							 ),
							array(
								/* thanks Leevi, based on WHAT A M*F*KING B*TCH this was, forever grateful! */
								'name'		=> strtolower(get_class($this))
							)
					  );
						
		// UPDATE SETTINGS
		$DSP->body .=	$DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:18px; width:100%'));
		
		$DSP->body .=	$DSP->table_row(array(array('text' => $LANG->line("check_for_updates_title"), 'class' => 'tableHeading', 'colspan' => '2')));
		$DSP->body .=	$DSP->table_row(array(array('text' => $DSP->div('box', '', '', '', "style='border-width: 0 0 1px 0; margin: 0;'") .'<p>'. $LANG->line("check_for_updates_info") .'</p>'. $DSP->div_c(), 'colspan' => '2')));
		
		$update_select =	"<select name='check_for_updates'>"
							. $DSP->input_select_option('y', "Yes", (($current['check_for_updates'] == 'y') ? 'y' : '' ))
							. $DSP->input_select_option('n', "No", (($current['check_for_updates'] == 'n') ? 'y' : '' ))
							. $DSP->input_select_footer();
		
		$DSP->body .=	$DSP->table_row(
							array(
								array('text' => $DSP->qdiv('defaultBold', $LANG->line("check_for_updates_label")), 'class' => 'tableCellOne', 'width' => '30%'),
								array('text' => $update_select, 'class' => 'tableCellOne')
							)
						);

		$DSP->body .= $DSP->table_close();

		// Close Form
		
		$DSP->body .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit()). $DSP->form_close();
	}

	//	========================================================================
	//	Settings
	//	========================================================================
	
	/**
	 * Get All Settings
	 *
	 * @return	array		All extension settings
	 * @since	version		0.9.5
	 */
	function _get_all_settings()
	{
		global $DB;

		$query = $DB->query("SELECT settings FROM exp_extensions WHERE class = '" . get_class($this) . "' AND settings != '' LIMIT 1");

		return $query->num_rows	? unserialize($query->row['settings']) : array();
	}


	/**
	 * Get Default Settings
	 * 
	 * @return	array	Default settings for site
	 * @since	1.0.0
	 */
	function _get_default_settings()
	{
		$settings = array(
			'check_for_updates'	=> 'y'
		);

		return $settings;
	}

	/**
	 * Get Site Settings
	 *
	 * @param  array   $settings   Current extension settings (not site-specific)
	 * @return array               Site-specific extension settings
	 * @since  version 1.0.0
	 */
	function _get_site_settings($settings = array())
	{
		global $PREFS;
		
		$site_settings = $this->_get_default_settings();
		
		$site_id = $PREFS->ini('site_id');
		if (isset($settings[$site_id]))
		{
			$site_settings = array_merge($site_settings, $settings[$site_id]);
		}
		
		return $site_settings;
	}
	
	/**
	 * Save Settings
	 *
	 * @since	version 1.0.5
	 */
	function save_settings()
	{
		global $DB, $PREFS;
		
		// load the settings
		$settings = $this->_get_all_settings();
		
		// Save new settings
		$settings[$PREFS->ini('site_id')] = $this->settings = array(
			'check_for_updates'	=> $_POST['check_for_updates']
		);
		
		$DB->query("UPDATE exp_extensions SET settings = '". addslashes(serialize($settings)) ."' WHERE class = '" . get_class($this) . "'");
	}

	/**
	* Register a new Addon Source
	*
	* @param	array	$sources	The existing sources
	* @return	array	The new source list
	* @since 	Version	1.6.2
	*/
	function lg_addon_update_register_source($sources)
	{
		global $EXT;
		
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$sources = $EXT->last_call;

		// add a new source
		// must be in the following format:
		/*
		<versions>
			<addon id='LG Addon Updater' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
		</versions>
		*/
		if($this->settings['check_for_updates'] == 'y')
		{
			$sources[] = 'http://www.designchuchi.ch/versions.xml';
		}

		return $sources;

	}

	/**
	* Register a new Addon
	*
	* @param	array	$addons The existing sources
	* @return	array	The new addon list
	* @since 	Version	1.6.2
	*/
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;
		
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$addons = $EXT->last_call;

		// add a new addon
		// the key must match the id attribute in the source xml
		// the value must be the addons current version
		if($this->settings['check_for_updates'] == 'y')
		{
			$addons[DC_TEMPLATE_MGR_ID] = $this->version;
		}

		return $addons;
	}

}
// END CLASS