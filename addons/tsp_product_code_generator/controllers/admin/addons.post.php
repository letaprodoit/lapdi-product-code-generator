<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	fn.product_code_generator.php
 * @version		1.1.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
 * @brief		Helper functions for addon
 * 
 */
define('DEBUG', false);

if ( !defined('AREA') )	{ die('Access denied');	}

if ($_SERVER['REQUEST_METHOD'] == 'POST' and $mode == 'update'
    and $_REQUEST['addon'] == 'tsp_product_code_generator') {
	
	// if either of the bulk keys are found then set the appropriate flag;
	$bulk_update = false;
	$bulk_update_key = "addon_option_{$_REQUEST['addon']}_bulk_update";	
	if (isset($_POST[$bulk_update_key]))
	{
		$bulk_update = ($_POST[$bulk_update_key] == "true") ? true : false;
	}//end if
	
	$bulk_replace = false;
	$bulk_replace_key = "addon_option_{$_REQUEST['addon']}_bulk_replace";
	if (isset($_POST[$bulk_replace_key]))
	{
		$bulk_replace = ($_POST[$bulk_replace_key] == "true") ? true : false;
	}//end if
	
	$update_made = ($bulk_update || $bulk_replace) ? true : false;
	
	if ($bulk_update)
	{
		$invalid_product_ids = fn_tsppcg_get_invalid_product_code_ids(COMPANY_ID);
		fn_tsppcg_update_product_codes($invalid_product_ids, true, $return_url);
	}
	else if ($bulk_replace)
	{
		$product_ids = db_get_fields("SELECT `product_id` FROM ?:products WHERE `company_id` = ?i", COMPANY_ID);
		fn_tsppcg_update_product_codes($product_ids, true);
	}
	
	if ($update_made)
	{
		return array(CONTROLLER_STATUS_OK, "addons.manage#grouptsp_product_code_generator");
	}
}
?>