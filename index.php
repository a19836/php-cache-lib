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

include_once __DIR__ . "/examples/config.php";

echo $style;
?>
<h1>Cache Lib</h1>
<p>Simple Cache library.</p>
<div class="note">
		<span>
		Learn how to use this Cache library very easy.<br/>
		You can use 3 different engines to cache your contents/objects/arrays/string/etc... in <b>file system, mongodb, memcache or redis</b>
		</span>
</div>
<div style="text-align:center;">
	<div style="display:inline-block; text-align:left;">
		<div>Some tutorials:</div>
		<ul>
			<li><a href="examples/user_cache.php" target="user_cache">User Cache Example</a></li>
			<li><a href="examples/xml_settings_cache.php" target="xml_settings_cache">Serialized Cache Example</a></li>
			<li><a href="examples/service_cache.php" target="service_cache">Service Cache Example without Related Services</a></li>
			<li><a href="examples/service_cache_with_relations.php" target="service_cache_with_relations">Service Cache Example with Related Services</a></li>
		</ul>
	</div>
</div>

<div>
	<h5>User Cache usage sample - based in file system:</h5>
	<div class="code">
		<textarea readonly>
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.user.filesystem.FileSystemUserCacheHandler");

$CacheHandler = new FileSystemUserCacheHandler();
$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/user/" );
$CacheHandler->config(60, true); //60 seconds of cache ttl

$file_name = "my_cached_file_name"; //this must be unique for each cache, because the cached file will be created based in this file name.

//get cached contents
if ($CacheHandler->isValid($file_name))
	$data = $CacheHandler->read($file_name);
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->write($file_name, $data);	
}

//then use $data...
		</textarea>
	</div>
	
	<h5>Serialized Cache usage sample - based in file system:</h5>
	<div class="code">
		<textarea readonly>
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.xmlsettings.filesystem.FileSystemXmlSettingsCacheHandler");

$CacheHandler = new FileSystemXmlSettingsCacheHandler();
$CacheHandler->setCacheTTL(60); //60 seconds of cache ttl

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

//then use $data...
		</textarea>
	</div>
	
	<h5>Serialized Cache usage sample - based in mongodb:</h5>
	<div class="code">
		<textarea readonly>
include_once __DIR__ . "/lib/app.php";
include_once get_lib("mongodb.MongoDBHandler");
include_once get_lib("cache.xmlsettings.mongodb.MongoDBXmlSettingsCacheHandler");

$MongoDBHandler = new MongoDBHandler();
$MongoDBHandler->connect("192.168.1.68", "my_cache_db", "mdbu", "mdbp", 8097);

$CacheHandler = new MongoDBXmlSettingsCacheHandler();
$CacheHandler->setMongoDBHandler($MongoDBHandler);
$CacheHandler->setCacheTTL(60); //60 seconds of cache ttl

//set file path to be cached
$file_path = "my_cached_file_name"; //this must be unique for each cache, because the cached file will be created based in this file name.

//get cached contents
if ($CacheHandler->isCacheValid($file_path))
	$cached_data = $CacheHandler->getCache($file_path);
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->setCache($file_path, $data); //setCache($file_path, $data, $renew_data = false)
}

//then use $data...
		</textarea>
	</div>
	
	<h5>Service Cache usage sample - based in memcache:</h5>
	<div class="code">
		<textarea readonly>
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.service.memcache.MemcacheServiceCacheHandler");

$MemcacheHandler = new MemcacheHandler();
$MemcacheHandler->connect("192.168.1.68", 8090);

$CacheHandler = new MemcacheServiceCacheHandler();
$CacheHandler->setMemcacheHandler($MemcacheHandler);
$CacheHandler->setRootPath("service_cache/"); //if file system handler: sys_get_temp_dir() . "/cache/service_cache/";
$CacheHandler->setDefaultTTL(60); //60 seconds of cache ttl
$CacheHandler->setDefaultType("php"); //type can be: "php" or "text"

$prefix = "foo/bar/"; //optional
$key = "my_data_key"; //this must be unique for each cached service, because the cached file will be created based in this key.

//get cached contents
if ($CacheHandler->isValid($prefix, $key)) //isValid($prefix, $key, $ttl = false, $type = false)
	$data = $CacheHandler->get($prefix, $key); //get($prefix, $key, $type = false)
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->create($prefix, $key, $data); //create($prefix, $key, $data, $type = false)
}

//then use $data...

//note that you can also relate services to other services and then delete that related services. For more details check the file examples/service_cache_with_relations.php
		</textarea>
	</div>
	
	<h5>Cache usage sample - based in redis:</h5>
	<div class="code">
		<textarea readonly>
//TODO
		</textarea>
	</div>
	
	<br/>
</div>


