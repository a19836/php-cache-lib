<?php
/*
 * Copyright (c) 2025 Bloxtor (http://bloxtor.com) and Joao Pinto (http://jplpinto.com)
 * 
 * Multi-licensed: BSD 3-Clause | Apache 2.0 | GNU LGPL v3 | HLNC License (http://bloxtor.com/LICENSE_HLNC.md)
 * Choose one license that best fits your needs.
 *
 * Original PHP Cache Lib Repo: https://github.com/a19836/phpcachelib/
 * Original Bloxtor Repo: https://github.com/a19836/bloxtor
 *
 * YOU ARE NOT AUTHORIZED TO MODIFY OR REMOVE ANY PART OF THIS NOTICE!
 */

include_once dirname(__DIR__) . "/lib/app.php";

$type = ""; //empty, mongodb, memcache or redis

switch($type) {
	case "memcache":
		include_once get_lib("memcache.MemcacheHandler");
		
		$MemcacheHandler = new MemcacheHandler();
		$MemcacheHandler->connect($host = "", $port = "", $timeout = null);
		break;
	
	case "mongodb":
		include_once get_lib("mongodb.MongoDBHandler");
		
		$MongoDBHandler = new MongoDBHandler();
		$MongoDBHandler->connect($host = "",  $db_name = "", $username = "", $password = "", $port = "", $options = null);
		break;
	
	case "redis":
		//TODO
		break;
}

//SET SOME STYLING
$style = '<style>
select {background:#eee; border:1px solid #ccc; border-radius:3px; padding:3px 2px;}
h1 {margin-bottom:0; text-align:center;}
h5 {font-size:1em; margin:40px 0 0; font-weight:bold;}
p {margin:0 0 20px; text-align:center;}

.note {text-align:center;}
.note span {text-align:center; margin:0 20px 20px; padding:10px; color:#aaa; border:1px solid #ccc; background:#eee; display:inline-block; border-radius:3px;}

.code {display:block; margin:10px 0; padding:0; background:#eee; border:1px solid #ccc; border-radius:3px; position:relative;}
.code:before {content:"php"; position:absolute; top:5px; left:5px; display:block; font-size:80%; opacity:.5;}
.code textarea {width:100%; height:300px; padding:30px 10px 10px; display:inline-block; background:transparent; border:0; resize:vertical; font-family:monospace;}

.test {display:block; margin:20px 0; padding:20px; background:#eee; border:1px solid #ccc; border-radius:3px; position:relative;}
.test:before {content:"test"; position:absolute; top:5px; left:5px; display:block; font-size:80%; opacity:.5;}
.test h4 {position:absolute; top:5px; right:5px; font-size:.9em; margin:0; font-size:80%; opacity:.5;}
.test ul {margin:0;}
</style>';

//SET SOME FUNCTIONS
function printCache($label, $root_path, $original_data, $cached_data) {
	echo '<div class="test">
		<h4>Start test for "' . $label . '"</h4>
		<div>Original Data: ' . print_r($original_data, true) . '</div>
		<div>Cached Data: ' . print_r($cached_data, true) . '</div>
		<div>Cached Files:';
	
	echo printCachedFolder($root_path);
	
	echo '</div>
	</div>';
}

function printCachedFolder($path) {
	if ($path && is_dir($path)) {
		$files = array_diff(scandir($path), array('.', '..'));

		echo '<ul>';

		if ($files)
			foreach ($files as $file) {
				$fp = $path . $file;
				
				echo '<li>' . $file . ': ';
				
				if (is_dir($fp))
					echo printCachedFolder($fp . "/");
				else
					echo file_get_contents($fp);
				
				echo '</li>';
			}
		else
			echo '<li>No files!</li>';
		
		echo '</ul>';
	}
	else if ($path)
		echo '<div>Folder "' . $path . '" does NOT exists!</div>';
}
?>
