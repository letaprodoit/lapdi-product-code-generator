<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	fn.product_code_generator.php
 * @version		2.1.4.2
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

	$company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : 1;
	$return_url = "?dispatch=addons.manage#grouptsp_product_code_generator";
	
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
		$invalid_product_ids = fn_tsppcg_get_invalid_product_code_ids($company_id);
		fn_tsppcg_update_product_codes($invalid_product_ids, true, $return_url);
	}
	else if ($bulk_replace)
	{
		$starting_product_id_key = "addon_option_{$_REQUEST['addon']}_starting_id";
		if (isset($_POST[$starting_product_id_key]))
		{
			$starting_product_id = $_POST[$starting_product_id_key];
		}//end if
		
		if (empty($starting_product_id))
		{
			$starting_product_id = 0;
		}//end if
		
		$product_ids = db_get_fields("SELECT `product_id` FROM ?:products WHERE `company_id` = ?i AND `product_id` >= ?i", COMPANY_ID, $starting_product_id);		
		fn_tsppcg_update_product_codes($product_ids, true, $return_url);
	}
	
	if ($update_made)
	{
		return array(CONTROLLER_STATUS_OK, "addons.manage#grouptsp_product_code_generator");
	}
}
?>