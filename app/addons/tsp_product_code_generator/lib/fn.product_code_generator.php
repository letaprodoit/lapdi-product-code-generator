<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	fn.product_code_generator.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Helper functions for addon
 * 
 */

if ( !defined('BOOTSTRAP') )	{ die('Access denied');	}

use Tygh\Registry;

/***********
 *
 * [Functions - Addon.xml Handlers]
 *
 ***********/


/***********
 *
 * Function to unisntall languages
 *
 ***********/
function fn_tsppcg_uninstall_languages () 
{
	$names = array(
		'tsp_product_code_generator'
	);
	
	if (!empty($names)) 
	{
		db_query("DELETE FROM ?:language_values WHERE name IN (?a)", $names);
	}//end if
}//end fn_tsppcg_uninstall_languages

/***********
 *
 * [Functions - General]
 *
 ***********/

/***********
 *
 * Function to generate product code
 *
 ***********/
function fn_tsppcg_generate_product_code($product_id, &$product_data)
{

	$product_code = "";
	$company_name = "";
	$product_name = "";
	$category_name = "";
				
	$settings = array();
	
	// Get prefixes
	$settings['first_prefix_type'] = Registry::get("addons.tsp_product_code_generator.first_prefix_type");
	$settings['second_prefix_type'] = Registry::get("addons.tsp_product_code_generator.second_prefix_type");
	$settings['third_prefix_type'] = Registry::get("addons.tsp_product_code_generator.third_prefix_type");
	$settings['last_prefix_type'] = Registry::get("addons.tsp_product_code_generator.last_prefix_type");
	
	// Get prefix settings
	$settings['seperator'] = Registry::get("addons.tsp_product_code_generator.seperator");
	$settings['prefix_auto_gen_len'] = Registry::get("addons.tsp_product_code_generator.prefix_auto_gen_len");
	$settings['prefix_auto_gen_type'] = Registry::get("addons.tsp_product_code_generator.prefix_auto_gen_type");
	$settings['prefix_company_name_len'] = Registry::get("addons.tsp_product_code_generator.prefix_company_name_len");
	$settings['prefix_product_name_len'] = Registry::get("addons.tsp_product_code_generator.prefix_product_name_len");
	$settings['prefix_category_name_len'] = Registry::get("addons.tsp_product_code_generator.prefix_category_name_len");

	// determine the product and category name as well as product id
	if (!empty($product_id) && !empty($product_data))
	{	
		// Generate the company name
		$long_name = fn_tsppcg_get_company_name($product_id);
		$comp_names = explode(" ", $long_name);
		$company_name = fn_tsppcg_convert_name($comp_names,$settings['prefix_company_name_len']);
		
		// Generate the product name
		$long_name = db_get_field("SELECT `product` FROM ?:product_descriptions WHERE `product_id` =?i", $product_id);
		$prod_names = explode(" ", $long_name);
		$product_name = fn_tsppcg_convert_name($prod_names,$settings['prefix_product_name_len']);
		
		// Generate the category name
		$ids_type1 = $product_data['categories'];
		$ids_type2 = $product_data['category_ids'];
		
		if (!empty($ids_type1))
		{
			$ids = explode(",", $ids_type1);
		}//endif 
		elseif (!empty($ids_type2))
		{
			$ids = $ids_type2;
		}//endelseif
		else
		{
			$ids = db_get_fields("SELECT `category_id` FROM ?:products_categories WHERE `product_id` = ?i", $product_id);
		}//endelse
		
		$cat_names = fn_tsppcg_get_category_names($ids);
		$category_name = fn_tsppcg_convert_name($cat_names,$settings['prefix_category_name_len']);
		
		// Store the data in the data array
		$data = array(
			'product_id' => $product_id,
			'company_name' => $company_name,
			'product_name' => $product_name,
			'category_name' => $category_name
		);

		// Generate product code prefixes
		// First prefix
		$product_code = fn_tsppcg_create_prefix($data, $settings['first_prefix_type'], $settings['prefix_auto_gen_len'], $settings['prefix_auto_gen_type'], $settings['seperator']);
			
		// Second prefix
		$product_code .= fn_tsppcg_create_prefix($data, $settings['second_prefix_type'], $settings['prefix_auto_gen_len'], $settings['prefix_auto_gen_type'], $settings['seperator']);
		
		// Third prefix
		$product_code .= fn_tsppcg_create_prefix($data, $settings['third_prefix_type'], $settings['prefix_auto_gen_len'], $settings['prefix_auto_gen_type'], $settings['seperator']);
		
		// Last prefix
		$product_code .= fn_tsppcg_create_prefix($data, $settings['last_prefix_type'], $settings['prefix_auto_gen_len'], $settings['prefix_auto_gen_type'],'');
	}//endif

	return $product_code;
}//end fn_tsppcg_generate_product_code

/***********
 *
 * Function to generate a code based on max chars of an array of names
 *
 ***********/
function fn_tsppcg_convert_name($names, $max_len)
{
	$name = "";
		
	$first_x_chars = false;
	
	if (sizeof($names) < $max_len)
	{
		$first_x_chars = true;
	}//endif

	if ($first_x_chars)
	{
		$stripped = ereg_replace("[^A-Za-z0-9 ]", "", $names[0]); //remove special chars
		$name = strtoupper(substr($stripped, 0, $max_len));
	}//endif
	else 
	{
		$i = 0;
		while ($i < $max_len)
		{
			$stripped = ereg_replace("[^A-Za-z0-9 ]", "", $names[$i]); //remove special chars
			$name .= strtoupper(substr($stripped, 0, 1)); // get first letter of name
			$i++;
		}//endwhile
	}//endelse
	
	return $name;
}//end fn_tsppcg_convert_name

/***********
 *
 * Function to create the first prefix of the product code based on the supplier
 *
 ***********/
function fn_tsppcg_get_company_name($product_id)
{
	$company_name = Registry::get('settings.Company.company_name');
	
	// if there is a company id associated with the product and this is not
	// the community version then get the supplier name
	if (!empty($product_id) && !fn_allowed_for('COMMUNITY'))
	{
		$company_id = db_get_field("SELECT `company_id` FROM ?:products WHERE `product_id` = ?i", $product_id);
		
		if (!empty($company_id))
		{
			$company_name = db_get_field("SELECT `company` FROM ?:companies WHERE `company_id` = ?i", $company_id);
		}//endif
			
	}//endif
		
	return $company_name;
}//end fn_tsppcg_get_company_name

/***********
 *
 * Function to create a prefix for a product code
 *
 ***********/
function fn_tsppcg_create_prefix(&$data, $prefix_type, $auto_gen_len, $auto_gen_type, $sep = '')
{
	$prefix = "";
	
	if ($prefix_type == 'product_id')
	{
		
		$prefix = $data['product_id'].$sep;
	}//endif
	elseif ($prefix_type == 'company_name')
	{	
		$prefix = $data['company_name'].$sep;
		
	}//endelseif
	elseif ($prefix_type == 'product_name')
	{	
		$prefix = $data['product_name'].$sep;
		
	}//endelseif
	elseif ($prefix_type == 'category_name')
	{
	
		$prefix = $data['category_name'].$sep;
	
	}//endelseif
	elseif ($prefix_type == 'none')
	{
	
		$prefix = "";
	
	}//endelseif
	else 
	{ //default autogen
	
		$auto_gen_code = fn_tsppcg_autogenerate_code($auto_gen_len, $auto_gen_type);
		$prefix = $auto_gen_code.$sep;
	
	}//endif

	return $prefix;
}//end fn_tsppcg_create_prefix

/***********
 *
 * Function to generate the category name based on max chars
 *
 ***********/
function fn_tsppcg_autogenerate_code($max_len, $gen_type)
{
	$code = "";
	
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$nums = '1234567890';
	$i = 0;

	while ($i < $max_len)
	{	
		if ($gen_type == 'numbers')
		{
			$code .= $nums {
				mt_rand(0, strlen($nums) - 1)
			};
		}//endif
		elseif ($gen_type == 'chars')
		{
			$code .= $chars {
				mt_rand(0, strlen($chars) - 1)
			};
		}//endelseif
		else 
		{ 
			if ($i%2) 
			{
				$code .= $chars {
					mt_rand(0, strlen($chars) - 1)
				};
			} //endif
			else 
			{
				$code .= $nums {
					mt_rand(0, strlen($nums) - 1)
				};
			}//endelse
		}//endelse
	
		$i++;
	}//endwhile
			
	return $code;
}//end fn_tsppcg_autogenerate_code

/***********
 *
 * Function to get category names given the ids
 *
 ***********/
function fn_tsppcg_get_category_names($cat_ids){

	$cat_names = array();
	
	foreach ($cat_ids as $id)
	{
		$cat_names[] = db_get_field("SELECT `category` FROM ?:category_descriptions WHERE `category_id` = ?i", $id);
	}//endforeach
	
	return $cat_names;
}//end fn_tsppcg_get_category_names

?>