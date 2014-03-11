<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	products.post.php
 * @version		1.1.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
 * @brief		Products post hook for admin area
 * 
 */

if ( !defined('AREA') )	{ die('Access denied');	}

if ($_SERVER['REQUEST_METHOD']	== 'POST') 
{
	return;
}//endif

$product_id = $_REQUEST['product_id'];

// View Product: Generate a product code if one doesn't exist
if ($mode == 'update' && !empty($product_id))
{

	$product_data = array();
	$product_code = null;
	
	// Get current product data
	$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, false);
	
	if (PRODUCT_TYPE == 'ULTIMATE' && !empty($product_data['shared_product']) && $product_data['shared_product'] == 'Y')
	{
		$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, true);
	}//endif
	
	$product_code = $product_data['product_code'];
	
	if (empty($product_code))
	{	
		$product_data['product_code'] = fn_tsppcg_generate_product_code($product_id, $product_data);		
		$view->assign('product_data', $product_data);
		
	}//endif
}//endif

?>