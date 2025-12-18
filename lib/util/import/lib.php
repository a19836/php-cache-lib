<?php
/*
 * Copyright (c) 2025 Bloxtor (http://bloxtor.com) and Joao Pinto (http://jplpinto.com)
 * 
 * Multi-licensed: BSD 3-Clause | Apache 2.0 | GNU LGPL v3 | HLNC License (http://bloxtor.com/LICENSE_HLNC.md)
 * Choose one license that best fits your needs.
 *
 * Original PHP Cache Lib Repo: https://github.com/a19836/php-cache-lib/
 * Original Bloxtor Repo: https://github.com/a19836/bloxtor
 *
 * YOU ARE NOT AUTHORIZED TO MODIFY OR REMOVE ANY PART OF THIS NOTICE!
 */

function get_lib($str, $type = "php") {
	$settings = get_lib_settings($str);
	
	return $settings[0] . str_replace(".", "/", $settings[1] ) . "." . $type;
}

function get_lib_settings($str) {
	$index = strpos($str, ".");
	$sub_str = substr($str, 0, $index);
	
	$prefix_path = "";
	$app_path = dirname(dirname(dirname(__DIR__))) . "/";
	
	switch(strtolower($sub_str)) {
		case "lib": $prefix_path = $app_path . "lib/"; break;
		case "vendor": $prefix_path = $app_path . "vendor/"; break;
		case "root": $prefix_path = $app_path; break;
		default: 
			$prefix_path = $app_path . "lib/";
			$index = -1;
	}
	
	return array($prefix_path, substr($str, $index + 1));
}
?>
