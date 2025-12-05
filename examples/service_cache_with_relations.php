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
		include_once get_lib("cache.service.memcache.MemcacheServiceCacheHandler");
		
		$CacheHandler = new MemcacheServiceCacheHandler();
		$CacheHandler->setMemcacheHandler($MemcacheHandler);
		break;
	
	case "mongodb":
		include_once get_lib("cache.service.mongodb.MongoDBServiceCacheHandler");
		
		$CacheHandler = new MongoDBServiceCacheHandler();
		$CacheHandler->setMongoDBHandler($MongoDBHandler);
		break;
	
	case "redis":
		//TODO
		break;
	
	default:
		include_once get_lib("cache.service.filesystem.FileSystemServiceCacheHandler");
		
		//If maximum_size and folder_total_num_manager_active are defined then the FileSystemServiceCacheHandler will cache the files in the multiple different directories according with these settings. this is very important because the OS have maximum of inodes and we can define this here.
		$CacheHandler = new FileSystemServiceCacheHandler($maximum_size = false, $folder_total_num_manager_active = false);
}

echo $style;

echo "<h1>Service Cache with Relations</h1>
<p>Cache contents that may have other objects related</p>";

echo '<div class="note">
		<span>
To be used to cache objects/arrays/string/etc that may have other objects related, and everytime that we create a new cache, it automatically disable the related cached objects.<br/>
Is used for caching services or complex data structures, often utilizing a `$prefix` for categorization and supporting related keys for complex invalidation.<br/>
All cached content can be serialized or saved as plain content.<br/>
All methods are based in the prefix and key arguments, where the cached file path is created from the root path defined before and the prefix and key.<br/>
Note that the prefix is a string that could be a relative folder path.<br/>
Note that if the root path does not exist, it will be created automatically.<br/>
The cached objects are saved inside of multiple folders (inside of the root path) to avoid to reach the maximum inodes limit of the OS. This handlers take care of this issue, so you don\'t need to worry about it.<br/>
Deletion based in regex and other conditions are also allowed!<br/>
<br/>
Although, is not allowed to get cached objects based in regex or other conditions, the idea is to create this feature in the future.
		</span>
</div>';

echo '<h5>Usage sample - based in file system:</h5>
<div class="code">
	<textarea readonly>
$CacheHandler = new FileSystemServiceCacheHandler();
$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/service/" );
$CacheHandler->setDefaultTTL(60); //in seconds
$CacheHandler->setDefaultType("php"); //type can be: "php" or "text"

$prefix = "foo/bar/"; //optional
$key = "my_data_key"; //this must be unique for each cached service, because the cached file will be created based in this key.

//get cached contents
if ($CacheHandler->isValid($prefix, $key)) //isValid($prefix, $key, $ttl = false, $type = false)
	$data = $CacheHandler->get($prefix, $key); //get($prefix, $key, $type = false)
else { //otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); //contents to cache
	$CacheHandler->create($prefix, $key, $data); //create($prefix, $key, $data, $type = false)

	//relate this service with some parent services that start with "select_item1_id-"
	$related_parent_service_keys = array(
		array("key" => "select_item1_id-", "type" => "prefix")
	);
	$status = $CacheHandler->addServiceToRelatedKeysToDelete($prefix, $key, $related_parent_service_keys, "php");
}

//then later on...
//delete cache based in the related services that starts with "my_data_"
$CacheHandler->delete($prefix, "my_data_", array("delete_mode" => 3, "cache_type" => "php", "key_type" => "prefix", "original_key" => "select_item1_id-"))
	</textarea>
</div>

<h5>Tests:</h5>';

testCache($CacheHandler, array("foo" => "bar", "bar" => "foo"), 30);
testCache($CacheHandler, "this is a test", 20, "text");
testCache($CacheHandler, (object) array('foo' => 'bar'), 25, "php");

function testCache(ServiceCacheHandler $CacheHandler, $data, $ttl = 60, $type = "php") {
	$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/service/" );
	$CacheHandler->setDefaultTTL($ttl);
	$CacheHandler->setDefaultType($type);
	
	$cached_data = null;
	$prefix = "foo/bar/";
	$key = "test_" . CacheHandlerUtil::getFilePathKey(serialize($data));
	
	//create some dummy related service cache
	/*$CacheHandler->create($prefix, "select_item1_id-1000", 1000, "text");
	$CacheHandler->create($prefix, "select_item1_id-1001", 1001, "text");
	$CacheHandler->create($prefix, "select_item2_id-2000", 2000, "text");
	$CacheHandler->create($prefix, "select_item2_id-2001", 2001, "text");*/
	
	//Explanation of some arguments from the methods below:
	//- $prefix is folder path
	//- $key is the service name to be cached
	//- $ttl is optional, in case we wish to only apply a ttl for this specific cache
	//- $type is optional, in case we wish to only apply a type for this specific cache
	
	if ($CacheHandler->isValid($prefix, $key)) //isValid($prefix, $key, $ttl = false, $type = false)
		$cached_data = $CacheHandler->get($prefix, $key); //get($prefix, $key, $type = false)
	else {
		$cached_data = "cached expired";
		
		if ($CacheHandler->create($prefix, $key, $data)) { //create($prefix, $key, $data, $type = false)
			$cached_data .= "<span style='color:green'> but cache created again</span>";
			$cached_aux = $CacheHandler->get($prefix, $key); //get($prefix, $key, $type = false)
			
			if ($cached_aux)
				$cached_data .= ": " . print_r($cached_aux, true);
			
			/* Add this service to some parent_services
			 * 1. loop the $related_parent_service_keys array
			 * 2. for each element get the related folder
			 * 	$dir_path = .../cache/.../select_item/PHP/__related/
			 * 3. then call the getFilePathKey function to get the $file_path
			 * 4. if the correspondent service does NOT exist yet, call the registerKey function and add a new record for the registration_status in the service key.
			*/
			$related_parent_service_keys = array(
				array("key" => "select_item1_id-", "type" => "prefix"),
				//...
			);
			$status = $CacheHandler->addServiceToRelatedKeysToDelete($prefix, $key, $related_parent_service_keys, $type); //addServiceToRelatedKeysToDelete($prefix, $key, $related_parent_service_keys, $type = false)
			//or, which checks if the getRegistrationKeyStatus is still valid or only after calls addServiceToRelatedKeysToDelete
			//$status = $CacheHandler->checkServiceToRelatedKeysToDelete($prefix, $key, $related_parent_service_keys, $type); //checkServiceToRelatedKeysToDelete($prefix, $key, $related_parent_service_keys, $type = false)
		}
		
		$random = rand(0, 20);
		
		if ($random >= 10) {
			/* delete($prefix, $key, $settings = array())
			 * $settings = array(
			 * 	"cache_type" => $type, //text or php.
			 *		"key_type" => $search_type, //prefix, start, begin, regex, regexp, middle, suffix, finish, end
			 * 	"original_key" => $original_key, //parent service key that was used to define the $related_parent_service_keys var. This is only used if the delete_mode is 3.
			 * 	"delete_mode" => $delete_mode, 
			 * 		$delete_mode:
			 * 		1 or empty: Only deletes if exact key matches
			 * 		2: Deletes all keys according with key_type. Gets all the items according with the $key_type (prefix, regex, etc...) and then delete that items.
			 * 		3: Only deletes the related services. Gets all the related keys for $key and for each related key returned, gets the correspondent key type (prefix, regex, etc...), gets the correspondent items and then delete them.
			 * )
			 * 
			 * Example:
			 * 	$prefix = a/b 
			 * 	$key = "test_"
			 * 	$cache_type = php
			 * 	$key_type = prefix
			 * 	$original_key = select_item_id-
			 * 	$delete_mode = 3
			 * 	
			 * 	$dir_path = .../cache/..../xxx/php/__related/prefix/
			 */
			
			//Inactive cache for services that start with 'test_' and are related with the parent services that start with 'select_item1_id-'. Note that this won't delete the cache, but it will inactivate it, so next time the system creates new cache.
			if ($CacheHandler->delete($prefix, "test_", array("delete_mode" => 3, "cache_type" => $type, "key_type" => "prefix", "original_key" => "select_item1_id-")))
				$cached_data .= "<span style='color:orange'> and cache inactivated</span>, which means next time the cache will be re-creatd again.";
			
			$cached_data .= ". Please refresh browser to recreate cache again...";
		}
	}
	
	$root_path = $CacheHandler->getRootPath();
	printCache($key, $root_path, $data, $cached_data);
}
?>
