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

include_once __DIR__ . "/config.php";

switch($type) {
	case "memcache":
		include_once get_lib("cache.xmlsettings.memcache.MemcacheXmlSettingsCacheHandler");
		
		$CacheHandler = new MemcacheXmlSettingsCacheHandler();
		$CacheHandler->setMemcacheHandler($MemcacheHandler);
		break;
	
	case "mongodb":
		include_once get_lib("cache.xmlsettings.mongodb.MongoDBXmlSettingsCacheHandler");
		
		$CacheHandler = new MongoDBXmlSettingsCacheHandler();
		$CacheHandler->setMongoDBHandler($MongoDBHandler);
		break;
	
	case "redis":
		//TODO
		break;
	
	default:
		include_once get_lib("cache.xmlsettings.filesystem.FileSystemXmlSettingsCacheHandler");
		
		$CacheHandler = new FileSystemXmlSettingsCacheHandler();
}

echo $style;

echo "<h1>Serialized Cache</h1>
<p>To cache objects/arrays/string/etc in a specific file path.</p>";

echo '<div class="note">
		<span>
To be used to cache objects/arrays/string/etc in a specific file path.<br/>
All cached content is serialized.<br/>
All methods are based in the full file path argument, where the cached file path is this path - without any kind of path treatment.<br/>
Note that for the file system engine, the parent directory of the file path must exists!<br/>
Also if you cache to many files, you can reach the maximum inodes limit of your OS. In this case you should use the ServiceCacheHandler instead, because it takes care this issue.<br/>
Note that cache deletion based in regex is not allowed! For this case please use the ServiceCacheHandler instead.
		</span>
</div>';

echo '<h5>Usage sample - based in file system:</h5>
<div class="code">
	<textarea readonly>
$CacheHandler = new FileSystemXmlSettingsCacheHandler();
$CacheHandler->setCacheTTL($ttl);

//prepare cache folder - must create the cached folder first
$root_path = sys_get_temp_dir() . "/cache/xml_settings/";

if (!is_dir($root_path))
	mkdir($root_path, 0755, true);

//set file path to be cached
$file_path = $root_path . "my_cached_file_name"; //this must be unique for each cache, because the cached file will be created based in this file name.

//get cached contents
if ($CacheHandler->isCacheValid($file_path))
	$cached_data = $CacheHandler->getCache($file_path);
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->setCache($file_path, $data); //setCache($file_path, $data, $renew_data = false)
}
	</textarea>
</div>

<h5>Tests:</h5>';

testCache($CacheHandler, array("foo" => "bar", "bar" => "foo"), 5);
testCache($CacheHandler, "this is a test", 10);
testCache($CacheHandler, (object) array('foo' => 'bar'), 15);

function testCache(XmlSettingsCacheHandler $CacheHandler, $data, $ttl = 60) {
	$CacheHandler->setCacheTTL($ttl);
	$root_path = sys_get_temp_dir() . "/cache/xml_settings/";
	
	if (!is_dir($root_path))
		mkdir($root_path, 0755, true);
	
	$cached_data = null;
	$file_path = $root_path . "test_" . CacheHandlerUtil::getFilePathKey(serialize($data));
	
	if ($CacheHandler->isCacheValid($file_path))
		$cached_data = $CacheHandler->getCache($file_path);
	else {
		$cached_data = "cached expired";
		
		//Note that when the 'setCache' method is called, the file will be recreated automatically, without needing to delete the file. This is just an example to show you how can you use diferent methods from XmlSettingsCacheHandler class.
		if (file_exists($file_path)) {
			if ($CacheHandler->deleteCache($file_path))
				$cached_data .= " and cached deleted. Please refresh browser to recreate cache again...";
		}
		else {
			$cached_data .= " but will be created again";
			
			if ($CacheHandler->setCache($file_path, $data)) //setCache($file_path, $data, $renew_data = false)
				$cached_data = $CacheHandler->getCache($file_path);
		}
	}
	
	printCache(basename($file_path), $root_path, $data, $cached_data);
}
?>
