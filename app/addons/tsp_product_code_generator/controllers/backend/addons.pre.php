<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	fn.product_code_generator.php
 * @version		2.1.1
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
 * @brief		Helper functions for addon
 * 
 */
define('DEBUG', false);

if ( !defined('BOOTSTRAP') ) { die('Access denied'); }

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST' and $mode == 'update'
    and $_REQUEST['addon'] == 'tsp_product_code_generator') {

	// if the settings are being updated update the settings
	// in the registry to capture the user's latest changes
	if (isset($_REQUEST['addon_data']))
	{
		// TODO - Determine why settings are not being updated
		//fn_update_addon($_REQUEST['addon_data']);
	}//end if
}
?>