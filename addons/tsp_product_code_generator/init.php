<?php
/*
 * TSP Product Code Generator CS-Cart Addon
 *
 * @package		TSP Product Code Generator CS-Cart Addon
 * @filename	init.php
 * @version		1.1.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported (http://creativecommons.org/licenses/by-nc-nd/3.0/)
 * @brief		Hook initializer for addon
 * 
 */

if ( !defined('AREA') )	{ die('Access denied');	}

fn_register_hooks(
	'update_product_post'
);
?>