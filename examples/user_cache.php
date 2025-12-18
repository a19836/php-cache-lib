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

include_once __DIR__ . "/config.php";

switch($type) {
	case "memcache":
		include_once get_lib("cache.user.memcache.MemcacheUserCacheHandler");
		
		$CacheHandler = new MemcacheUserCacheHandler();
		$CacheHandler->setMemcacheHandler($MemcacheHandler);
		break;
	
	case "mongodb":
		include_once get_lib("cache.user.mongodb.MongoDBUserCacheHandler");
		
		$CacheHandler = new MongoDBUserCacheHandler();
		$CacheHandler->setMongoDBHandler($MongoDBHandler);
		break;
	
	case "redis":
		//TODO
		break;
	
	default:
		include_once get_lib("cache.user.filesystem.FileSystemUserCacheHandler");
		
		$CacheHandler = new FileSystemUserCacheHandler();
}

echo $style;

echo "<h1>User Cache</h1>
<p>Cache objects/arrays/string/etc in a specific folder</p>";


echo '<div class="note">
		<span>
To be used to cache objects/arrays/string/etc in a specific folder.<br/>
All cached content can be serialized or saved as plain content.<br/>
All methods are based in the file name argument, where the cached file path is created from the root path defined before and the correspondent file name.<br/>
Note that if root path, defined before, does not exist, it will be created automatically.<br/>
Also if you cache to many files, you can reach the maximum inodes limit of your OS. In this case you should use the ServiceCacheHandler instead, because it takes care this issue.<br/>
Note that cache deletion based in regex is not allowed! For this case please use the ServiceCacheHandler instead.
		</span>
</div>';

echo '<h5>Usage sample - based in file system:</h5>
<div class="code">
	<textarea readonly>
$CacheHandler = new FileSystemUserCacheHandler();
$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/user/" );
$CacheHandler->config(60, true); //config($ttl, $serialize), ttl is in seconds

$file_name = "my_cached_file_name"; //this must be unique for each cache, because the cached file will be created based in this file name.

//get cached contents
if ($CacheHandler->isValid($file_name))
	$data = $CacheHandler->read($file_name);
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->write($file_name, $data);	
}
	</textarea>
</div>

<h5>Tests:</h5>';

testCache($CacheHandler, array("foo" => "bar", "bar" => "foo"), 5);
testCache($CacheHandler, "this is a test", 10, false);
testCache($CacheHandler, (object) array('foo' => 'bar'), 15, true);

function testCache(UserCacheHandler $CacheHandler, $data, $ttl = 60, $serialize = true) {
	$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/user/" );
	$CacheHandler->config($ttl, $serialize);
	
	$cached_data = null;
	$file_name = "test_" . CacheHandlerUtil::getFilePathKey(serialize($data));
	
	if ($CacheHandler->isValid($file_name))
		$cached_data = $CacheHandler->read($file_name);
	else {
		$cached_data = "cached expired";
		
		//Note that when the 'write' method is called, the file will be recreated automatically, without needing to delete the file. This is just an example to show you how can you use diferent methods from UserCacheHandler class.
		if ($CacheHandler->exists($file_name)) {
			if ($CacheHandler->delete($file_name))
				$cached_data .= " and cached deleted. Please refresh browser to recreate cache again...";
		}
		else {
			$cached_data .= " but will be created again";
			
			if ($CacheHandler->write($file_name, $data))
				$cached_data = $CacheHandler->read($file_name);
		}
	}
	
	$root_path = $CacheHandler->getRootPath();
	printCache($file_name, $root_path, $data, $cached_data);
}
?>
