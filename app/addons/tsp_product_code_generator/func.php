<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	func.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
 * @brief		Hooks implementations for addon
 * 
 */

if ( !defined('BOOTSTRAP') )	{ die('Access denied');	}

require_once 'lib/fn.product_code_generator.php';

/***********
 *
 * Function to update the product code on load if there is no product code
 *
 ***********/
function fn_tsp_product_code_generator_update_product_post(&$product_data, $product_id, $lang_code, $create){

	if (!empty($product_id) && !empty($product_data))
	{
		$product_code = $product_data['product_code'];
		
		if (empty($product_code))
		{		
			$product_code = fn_tsppcg_generate_product_code($product_id, $product_data);
			
			db_query("UPDATE ?:products SET `product_code` = ?s WHERE `product_id` = ?i",$product_code,$product_id);		
			
		}//endif
	}//endif
}//end fn_tsp_product_code_generator_update_product_post

?>