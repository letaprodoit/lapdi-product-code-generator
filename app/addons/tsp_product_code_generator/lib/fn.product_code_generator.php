<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	fn.product_code_generator.php
 * @version		2.1.5
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2014 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
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
 * Function to generate product codes for all products that do not
 * meet the prefix settings
 *
 ***********/
function fn_tsppcg_info_update_product_codes () 
{
	$store_lang =  (DEFAULT_LANGUAGE != null) ? DEFAULT_LANGUAGE : CART_LANGUAGE;
	$company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : 1;
	$product_count = db_get_field("SELECT COUNT(*) FROM ?:products WHERE `company_id` = ?i", $company_id);
	$invalid_product_count = fn_tsppcg_count_invalid_product_codes($company_id);
	
	$field = array();

	$field_html = '
	<div class="control-group setting-wide tsp_product_code_generator">
        <label for="addon_option_tsp_product_code_generator_bulk_update" class="control-label ">%s:</label>
        <div class="controls">
        	<input class="btn btn-primary " 
				onclick="javascript:document.getElementById(\'addon_option_tsp_product_code_generator_bulk_update\').value = true;"
			 	type="submit" name="dispatch[addons.update]" value="%s" %s>
			<input type="hidden" id="addon_option_tsp_product_code_generator_bulk_update"
				name="addon_option_tsp_product_code_generator_bulk_update" />
           	<div class="right update-for-all"></div>
       </div>
   </div>';
					
	$info = array();
	$info['en']['label'] = __("tsppcg_update_invalid",array(),'en');
	$info['es']['label'] = __("tsppcg_update_invalid",array(),'es');
	$info['el']['label'] = __("tsppcg_update_invalid",array(),'el');
	$info['fr']['label'] = __("tsppcg_update_invalid",array(),'fr');
	
	$info['en']['button'] = __("tsppcg_update",array(),'en');
	$info['es']['button'] = __("tsppcg_update",array(),'es');
	$info['el']['button'] = __("tsppcg_update",array(),'el');
	$info['fr']['button'] = __("tsppcg_update",array(),'fr');
	
	foreach ($info as $lang => $fields)
	{
		foreach ($fields as $label => $value)
		{
			$info[$lang]['button'] = sprintf($value, $invalid_product_count);
		}
	}

	$enabled = "";
	// Company ID must be set to a non-null value for 4x dev
	if (is_null($company_id) || $invalid_product_count == 0 || $product_count == 0)
		$enabled = "disabled";
	
	return sprintf($field_html, $info[$store_lang]['label'], $info[$store_lang]['button'], $enabled);
}//end fn_tsppcg_update_product_codes

/***********
 *
 * Function to replace product codes for all products
 *
 ***********/
function fn_tsppcg_info_replace_product_codes () 
{
	$store_lang = (DEFAULT_LANGUAGE != null) ? DEFAULT_LANGUAGE : CART_LANGUAGE;
	$company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : 1;
	$product_count = db_get_field("SELECT COUNT(*) FROM ?:products WHERE `company_id` = ?i", $company_id);
	$invalid_product_count = fn_tsppcg_count_invalid_product_codes($company_id);
	
	$field = array();

	$field_html = '
	<div class="control-group setting-wide tsp_product_code_generator">
        <label for="addon_option_tsp_product_code_generator_bulk_replace" class="control-label ">%s:</label>
        <div class="controls">
        	<input class="btn btn-primary " 
				onclick="javascript:document.getElementById(\'addon_option_tsp_product_code_generator_bulk_replace\').value = true;"
			 	type="submit" name="dispatch[addons.update]" value="%s" %s>
			<input type="hidden" id="addon_option_tsp_product_code_generator_bulk_replace"
				name="addon_option_tsp_product_code_generator_bulk_replace" />
           	<div class="right replace-for-all"></div>
       </div>
  	</div>';
	
    $info = array();
	$info['en']['label'] = __("tsppcg_replace_invalid",array(),'en');
	$info['es']['label'] = __("tsppcg_replace_invalid",array(),'es');
	$info['el']['label'] = __("tsppcg_replace_invalid",array(),'el');
	$info['fr']['label'] = __("tsppcg_replace_invalid",array(),'fr');
	
	$info['en']['button'] = __("tsppcg_replace",array(),'en');
	$info['es']['button'] = __("tsppcg_replace",array(),'es');
	$info['el']['button'] = __("tsppcg_replace",array(),'el');
	$info['fr']['button'] = __("tsppcg_replace",array(),'fr');
	
	foreach ($info as $lang => $fields)
	{
		foreach ($fields as $label => $value)
		{
			$info[$lang]['button'] = sprintf($value, $product_count);
		}
	}	
	
	$enabled = "";
	// Company ID must be set to a non-null value for 4x dev
	if (is_null($company_id) || $product_count == 0)
			$enabled = "disabled";
	
	return sprintf($field_html, $info[$store_lang]['label'], $info[$store_lang]['button'], $enabled);
}//end fn_tsppcg_replace_all_product_codes


/***********
 *
 * Function to display info about regenerating product codes
 *
 ***********/
function fn_tsppcg_display_product_analysis()
{
	$store_lang = (DEFAULT_LANGUAGE != null) ? DEFAULT_LANGUAGE : CART_LANGUAGE;
	$company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : 1;
	$product_count = db_get_field("SELECT COUNT(*) FROM ?:products WHERE `company_id` = ?i", $company_id);
	$invalid_product_count = fn_tsppcg_count_invalid_product_codes($company_id);
	
	$info = array();
	
	$info['en'] = '
<p>You currently have <font color="red"><strong>%s</strong></font> of <strong>%s</strong> products in your store that do not meet your prefix settings.<br><br>
If you <strong>do not</strong> want to change your current prefix settings and wish to update your records, then click the appropriate button below</p>
<p>If you <strong>do</strong> want to change your prefix settings, choose the appropriate tab above to make updates, then <strong>click "Save"</strong>, then navigate back to the <strong>"Bulk Product Code Generator"</strong> tab and click the appropriate button below.</p>
<p>Please be sure to chose the appropriate option....</p>
<p>Click the "<strong>Update</strong>" button to update only the products with issues. <br>
Click the "<strong>Replace</strong>" button to reset your product code on all products in the store.</p>
<p><br><strong>WARNING</strong>: <strong>Changes are <u>irreversible</u> and only <u>occur for your current company ID.</u></strong>
If you are unsure of what you are doing, please backup your database first, before making these changes.</p>';
	
	$info['el'] = '
<p>Αυτή τη στιγμή διαθέτετε <font color="red"><strong>%s</strong></font> Της <strong>%s</strong> προϊόντα στο κατάστημά σας ότι δεν σας ικανοποιούν πρόθεμα ρυθμίσεις.</p>
<p>Εάν δεν θέλετε να αλλάξετε τις τρέχουσες ρυθμίσεις προθέματος και θέλετε να ενημερώσετε τα αρχεία σας, στη συνέχεια, κάντε κλικ στο κατάλληλο κουμπί παρακάτω</p>
<p>εάν θέλετε να αλλάξετε το πρόθεμα ρυθμίσεις, επιλέξτε την κατάλληλη καρτέλα παραπάνω για να ενημερώσεις, στη συνέχεια, κάντε κλικ στο "<strong>Αποθήκευση</strong>", και στη συνέχεια πλοήγηση προς τα πίσω για να την "<strong>χύμα Κωδικός Προϊόντος γεννήτρια</strong>" Γλωττίδα και κάντε κλικ στο κατάλληλο κουμπί παρακάτω.</p>
<p>Παρακαλούμε βεβαιωθείτε ότι επιλέξατε την κατάλληλη επιλογή ....</p>
<p>Κάντε κλικ στο κουμπί "<strong>Ενημερωμένη έκδοση</strong>" κουμπί για να ενημερώσετε μόνο τα προϊόντα με θέματα.<br>
  Κάντε κλικ στο κουμπί "<strong>Αντικατάσταση</strong>" κουμπί για να επαναφέρετε τον κωδικό προϊόντος για όλα τα προϊόντα στην αποθήκη.<br>
</p>
<p><strong>ΠΡΟΕΙΔΟΠΟΊΗΣΗ: Οι αλλαγές είναι μη αναστρέψιμη και συμβαίνει μόνο για την τρέχουσα εταιρεία ID.</strong> Εάν δεν είστε σίγουροι για το τι κάνετε, παρακαλούμε δημιουργήστε αντίγραφα ασφαλείας δεδομένων πρώτα, πριν από τις τροποποιήσεις αυτές.</p>';

	$info['es'] =  '
<p>Actualmente tienes <font color="red"><strong>%s</strong></font> de <strong>%s</strong> productos en su tienda que no cumplan con la configuración de prefijo.<br>
<p>Si <strong>no</strong> para cambiar la configuración de prefijos actuales y desea actualizar sus datos, a continuación, haga clic en el botón apropiado abajo</p>
<p>Si <strong>haces</strong> para cambiar la configuración de prefijo, seleccione la ficha correspondiente de arriba para hacer cambios, entonces <strong>clic en "Guardar"</strong>, a continuación, vaya de nuevo a la <strong>"Bulk Producto Code Generator"</strong> y haga clic en el botón correspondiente a continuación.</p>
<p>Por favor, asegúrese de elegir la opción adecuada...</p>
<p>Haga clic en el botón para actualizar sólo los productos con problemas "<strong>actualización</strong>".<br>
Haga clic en el botón "<strong>Reemplace</strong>" para restablecer el código de producto en todos los productos en el almacén.</p>
<p><strong>ADVERTENCIA</strong>: Cambios <strong>son <u>irreversible</u> y sólo <u>producen para su empresa actual ID</u></strong>
Si no está seguro de lo que estás haciendo, por favor copia de seguridad de su base de datos en primer lugar, antes de realizar estos cambios.</p>';
	
	$info['fr'] = '
<p>Vous avez actuellement <font color="red"><strong>%s</strong></font> de <strong>%s</strong> produits dans votre magasin qui ne répondent pas à vos paramètres de préfixe.<br>
<p>Si vous <strong>ne</strong> voulez pas changer vos paramètres de préfixe actuelles et souhaitez mettre à jour vos dossiers, puis cliquez sur le bouton approprié ci-dessous</p>
<p>Si vous <strong>faites</strong> vous souhaitez modifier vos paramètres de préfixe, choisissez l\'onglet approprié ci-dessus pour faire les mises à jour, alors <strong>cliquez sur "Enregistrer "</strong>, puis revenez à la <strong > "En vrac le produit générateur de code "</strong>onglet et cliquez sur le bouton approprié ci-dessous .</p>
<p>S\'il vous plaît assurez-vous de choisir l\'option appropriée...</p>		
<p>Cliquez sur le "<strong>mise à jour</strong>" pour mettre à jour uniquement les produits qui ont des problèmes.<br>
Cliquez sur le bouton "<strong>Remplacez</strong>" pour réinitialiser votre code de produit sur tous les produits dans le magasin .</p>
<p><br><strong>AVERTISSEMENT</strong>: Changements <strong>sont <u>irréversible</u> et ne <u>se produisent pour votre ID actuel de l\'entreprise</u></strong>
Si vous n\'êtes pas sûr de ce que vous faites, s\'il vous plaît une sauvegarde de votre base de données en premier lieu, avant de faire ces changements .</p>';
	
	foreach ($info as $key => $value)
	{
		$info[$key] = sprintf($value, $invalid_product_count, $product_count);
	}
	
	return html_entity_decode($info[$store_lang]);
}//fn_tsppcg_display_product_analysis

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
		
		// if seperator found at the end of the code remove it
		// this means the user wanted no last prefix
		// convert seperator to a regular expression
		$seperator_regex = preg_quote($settings['seperator'], '/');
		$seperator_regex .= "$"; //seperator at the end of the string
				
		$product_code = preg_replace("/".$seperator_regex."/", "", $product_code);
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

/***********
 *
* Function to count the number of products that have invalid product codes
*
***********/

function fn_tsppcg_count_invalid_product_codes($company_id)
{
	$invalid_count = 0;
	
	// loop through products and get the product code
    $products = db_get_fields("SELECT `product_id` FROM ?:products WHERE `company_id` = ?i", $company_id);
		
	foreach ($products as $null => $product_id)
	{
		// Get current product data
		$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, false);
		
		if (fn_allowed_for('ULTIMATE') && !empty($product_data['shared_product']) && $product_data['shared_product'] == 'Y')
		{
			$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, true);
		}//endif
		
		$this_product_code = $product_data['product_code'];
		
		// if the product code is empty OR...
		if (empty($this_product_code))
		{
			$invalid_count++;
		}
		else
		{
			$generated_product_code = fn_tsppcg_generate_product_code($product_id, $product_data);
		
			// the product's code does NOT equal the generated code
			// then increment the invalid count
			if (!fn_tsppcg_product_code_type_match($this_product_code, $generated_product_code))
			{
				$invalid_count++;
			}
		}
	}

	return $invalid_count;
}

/***********
 *
* Function to get the product_ids of products that have invalid product codes
*
***********/
function fn_tsppcg_get_invalid_product_code_ids($company_id)
{
	$invalid_products = array();
	
    $products = db_get_fields("SELECT `product_id` FROM ?:products WHERE `company_id` = ?i", $company_id);
		
	// loop through products and get the product code
    foreach ($products as $null => $product_id)
	{
		// Get current product data
		$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, false);
		
		if (fn_allowed_for('ULTIMATE') && !empty($product_data['shared_product']) && $product_data['shared_product'] == 'Y')
		{
			$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, true);
		}//endif
		
		$this_product_code = $product_data['product_code'];
		
		// if the product code is empty OR...
		if (empty($this_product_code))
		{
			$invalid_products[] = $product_id;
		}
		else
		{
			$generated_product_code = fn_tsppcg_generate_product_code($product_id, $product_data);
		
			// the product's code does NOT equal the generated code
			// then increment the invalid count
			if (!fn_tsppcg_product_code_type_match($this_product_code, $generated_product_code))
			{
				$invalid_products[] = $product_id;
			}
		}
	}

	return $invalid_products;
}
/***********
 *
* Function to determine if two product codes are of the same type
*
***********/
function fn_tsppcg_product_code_type_match($old_code, $new_code)
{
	$match = true;
				
	$settings = array();
	
	$default_part_size = 4; //default for product codes
	
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
	
	// store the parts of the prefix that contain auto-generated parts
	// this is important because these will change but they should be of the same
	// format as the settings to be valid
	$auto_gen_parts = array();
	$contains_auto_gen = false;
	
	$auto_gen_parts[0] = ($settings['first_prefix_type'] == 'auto_gen') ? true : false;
	$auto_gen_parts[1] = ($settings['second_prefix_type'] == 'auto_gen') ? true : false;
	$auto_gen_parts[2] = ($settings['third_prefix_type'] == 'auto_gen') ? true : false;
	$auto_gen_parts[3] = ($settings['last_prefix_type'] == 'auto_gen') ? true : false;
	
	$contains_auto_gen = ($auto_gen_parts[0] || $auto_gen_parts[1] || $auto_gen_parts[2] || $auto_gen_parts[3]) ? true : false;
	
	// store the parts of the prefix that are empty
	// this is important because these will change but they should be of the same
	// format as the settings to be valid
	$null_parts = array();
	$contains_null = false;
	
	$null_parts[0] = ($settings['first_prefix_type'] == 'none') ? true : false;
	$null_parts[1] = ($settings['second_prefix_type'] == 'none') ? true : false;
	$null_parts[2] = ($settings['third_prefix_type'] == 'none') ? true : false;
	$null_parts[3] = ($settings['last_prefix_type'] == 'none') ? true : false;
	
	$contains_null = ($null_parts[0] || $null_parts[1] || $null_parts[2] || $null_parts[3]) ? true : false;	

	// convert seperator to a regular expression
	$seperator_regex = preg_quote($settings['seperator'], '/');
	
	$old_code_parts = preg_split("/".$seperator_regex."/" , $old_code);
	$new_code_parts = preg_split("/".$seperator_regex."/" , $new_code);
	
	//if the number of elements in the product code dont match
	// then they are not equal then match is false
	if (count($old_code_parts) != count($new_code_parts))
	{
		$match = false;
	}
	// if the code contains no auto generated parts
	// and the codes do not equal then match is false
	else if (!$contains_auto_gen && ($old_code != $new_code))
	{
		$match = false;
	}
	else if ($contains_auto_gen)
	{
		// loop through a product code and compare elements
		// part size are equal for this case
		$part_count = count($old_code_parts);

		$process_pos = 0; // counter to process the parts of the code
		
		// loop through all possible positions of the code
		for ($i = 0; $i < $default_part_size; $i++)
		{
			// if the code does not contain a null value
			// at this position then process and increment the counter
			// if it doesn't then counter will not increment
			// and we will skip processing this part of the code
			if (!$null_parts[$i])
			{
				$old_part = $old_code_parts[$process_pos];
				$new_part = $new_code_parts[$process_pos];
				
				// if the part is autogenated only
				// test the component on the length not the value
				if ($auto_gen_parts[$i])
				{
					// if the autogenerated part is not
					// the same size then the match is false
					// and no further processing required
					if (strlen($old_part) != strlen($new_part))
					{
						$match = false;
						break;
					}
				}
				// if the part is not auto generated then they should
				// match if not match is false and no further processing required 
				else if ($old_part != $new_part)
				{
					$match = false;
					break;
				}
				
				$process_pos++;
			}
		}
	}
	
	return $match;
}
/***********
 *
* Function to update the product codes in the database
*
***********/
function fn_tsppcg_update_product_codes($product_ids, $display_output, $return_url = null)
{
	$product_count = count($product_ids);
	$store_lang = (DEFAULT_LANGUAGE != null) ? DEFAULT_LANGUAGE : CART_LANGUAGE;
	
	if ($product_count > 0)
	{
		if ($display_output)
		{
			echo sprintf(__("tsppcg_patient",array(),$store_lang), $product_count)."<br><br><br>\n\n\n";
			fn_flush();
		}//end if
		
		$counter = 1;
		foreach ($product_ids as $product_id)
		{
			$product_data = array();
			$product_code = null;
			
			// Get current product data
			$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, false);
			
			if (fn_allowed_for('ULTIMATE') && !empty($product_data['shared_product']) && $product_data['shared_product'] == 'Y')
			{
				$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, true);
			}//endif
			
			$name = $product_data['product'];		
			if (strlen($name) > 25)
			{
				$name = substr($product_data['product'], 0, 25) . "...";
			}//end if
			
			$current_product_code = $product_data['product_code'];
			
			// replace the product code
			$product_data['product_code'] = fn_tsppcg_generate_product_code($product_id, $product_data);
			
			// Update product data
			@fn_update_product($product_data, $product_id);
			
			if ($display_output)
			{
				echo sprintf(__("tsppcg_updated",array(),$store_lang), $counter, "<strong>{$name}</strong>", "<strong>[$current_product_code]</strong>", "<strong>[{$product_data['product_code']}]</strong>")."<br>\n";
				fn_flush();
				
				usleep(500000); // sleep for half a second
				$counter++;
			}//end if
		}//end foreach
		
		if ($display_output)
		{
			echo "<br><br>\n\n".__("tsppcg_done",array(),$store_lang);
			if ($return_url)
			{
				echo "..<a href='$return_url'>[".__("tsppcg_continue",array(),$store_lang)."]</a>.";
			}
			fn_flush();
			
			sleep(10);
		}//end if
	}
}
?>