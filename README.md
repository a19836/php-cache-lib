# PHP Cache Lib

> Original Repos:   
> - PHP Cache Lib: https://github.com/a19836/php-cache-lib/   
> - Bloxtor: https://github.com/a19836/bloxtor/

## Overview

**PHP Cache Lib** is a simple cache library designed for caching contents, objects, arrays, strings, and more.
It offers flexible backend support, allowing you to cache your data using **file system, MongoDB, Memcache, or Redis**.

To see a working example, open [index.php](index.php) on your server.

---

## Handlers

### User Cache Handler

To be used to cache content (objects/arrays/string/etc) in a specific folder/place, regardless of the underlying storage engine (file system, MongoDB, MemCache, Redis etc.).
All cached content can be **serialized** or saved as **plain** content.

All methods are **based in the file name argument**, where the cached file path is created from the **root path defined before** and the correspondent file name.
Note that if root path, defined before, does not exist, it will be created automatically.

Also if you cache to many files, you can reach the maximum inodes limit of your OS. In this case you should use the ServiceCacheHandler instead, because it takes care this issue.
Note that cache deletion based in regex is not allowed! For this case please use the ServiceCacheHandler instead.

Main available methods:

| Method | Description |
| :--- | :--- |
| `read($file_name)` | **Retrieves** the cached data associated with the given file name. |
| `write($file_name, $data)` | **Writes** the provided data to the cache using the specified file name as the key. |
| `isValid($file_name)` | **Checks** if the cached entry exists *and* has not expired (is still valid based on its Time-To-Live). |
| `exists($file_name)` | **Checks** if a cache entry physically exists for the given file name. |
| `delete($file_name)` | **Removes** the cached entry associated with the given file name. |

### Serialized Cache Handler

To be used to cache content (objects/arrays/string/etc) in a specific file path, regardless of the underlying storage engine (file system, MongoDB, MemCache, Redis etc.).
All cached content is **serialized**.

All methods are based in the **full file path argument**, where the cached file path is this path - without any kind of path treatment.
Note that for the **file system engine, the parent directory of the file path must exists**!

Also if you cache to many files, you can reach the maximum inodes limit of your OS. In this case you should use the ServiceCacheHandler instead, because it takes care this issue.
Note that cache deletion based in regex is not allowed! For this case please use the ServiceCacheHandler instead.

Main available methods:

| Method | Description |
| :--- | :--- |
| `getCache($file_path)` | **Retrieves** the cached data stored at the given `$file_path` (which acts as the unique key). |
| `setCache($file_path, $data)` | **Writes** the provided `$data` to the cache, using the `$file_path` as the storage identifier. |
| `isCacheValid($file_path)` | **Checks** if the cache entry at `$file_path` exists and is still valid (not expired). |
| `deleteCache($file_path)` | **Removes** the cached entry associated with the `$file_path`. |

### Service Cache Handler

To be used to cache content (objects/arrays/string/etc), regardless of the underlying storage engine (file system, MongoDB, MemCache, Redis etc.), that may have other objects related, and everytime that we create a new cache, it automatically disable the related cached objects.
Is used for caching **services** or complex data structures, often utilizing a `$prefix` for categorization and supporting **related keys** for complex invalidation.
All cached content can be **serialized** or saved as **plain** content.

All methods are based in the **prefix and key arguments**, where the cached file path is created from the **root path defined before** and the prefix and key.
Note that the prefix is a string that could be a relative folder path.
Note that if the root path does not exist, it will be created automatically.

The cached objects are saved inside of **multiple folders** (inside of the root path) to **avoid to reach the maximum inodes limit** of the OS. This handlers take care of this issue, so you don't need to worry about it.
**Deletion based in regex and other conditions** are also allowed!

Although, is not allowed to get cached objects based in regex or other conditions, the idea is to create this feature in the future.

Main available methods:

| Method | Description |
| :--- | :--- |
| `create($prefix, $key, $result, $type = false)` | **Creates** a new cache entry for a service using a `$prefix` and `$key`. |
| `addServiceToRelatedKeysToDelete(...)` | **Adds** the current service (`$prefix`, `$key`) to a list of services to be deleted when one of the parents are invalidated. |
| `deleteAll($prefix, $type = false)` | **Removes all** cache entries that fall under the specified `$prefix`. |
| `delete($prefix, $key, $settings = array())` | **Removes** a specific cache entry defined by `$prefix` and `$key`. It can also be used to delete related services. See a livde example [here](examples/service_cache_with_relations.php). |
| `get($prefix, $key, $type = false)` | **Retrieves** the cached data for the service specified by `$prefix` and `$key`. |
| `isValid($prefix, $key, $ttl = false, $type = false)` | **Checks** if the cached service entry is currently valid (exists and has not expired). |

---

## Usage

The library provides different handlers for various caching needs and engines.

### 1. User Cache (File System)

This sample demonstrates how to use the `FileSystemUserCacheHandler` for basic data caching with an unique file name.

```php
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.user.filesystem.FileSystemUserCacheHandler");

$CacheHandler = new FileSystemUserCacheHandler();
$CacheHandler->setRootPath( sys_get_temp_dir() . "/cache/user/" );
$CacheHandler->config(60, true); // 60 seconds of cache ttl

$file_name = "my_cached_file_name"; // Must be unique.

// Get cached contents
if ($CacheHandler->isValid($file_name))
	$data = $CacheHandler->read($file_name);
else { // Otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); // Contents to cache
	$CacheHandler->write($file_name, $data);	
}

// Then use $data...
```

### 2. Serialized Cache (File System)

This sample uses `FileSystemXmlSettingsCacheHandler` and requires preparing the cache folder beforehand.

```php
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.xmlsettings.filesystem.FileSystemXmlSettingsCacheHandler");

$CacheHandler = new FileSystemXmlSettingsCacheHandler();
$CacheHandler->setCacheTTL(60); // 60 seconds of cache ttl

// Prepare cache folder - must create the cached folder first
$root_path = sys_get_temp_dir() . "/cache/xml_settings/";

if (!is_dir($root_path))
	mkdir($root_path, 0755, true);

// Set file path to be cached
$file_path = $root_path . "my_cached_file_name"; // Must be unique.

// Get cached contents
if ($CacheHandler->isCacheValid($file_path))
	$cached_data = $CacheHandler->getCache($file_path);
else { // Otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); // Contents to cache
	$CacheHandler->setCache($file_path, $data); // setCache($file_path, $data, $renew_data = false)
}

// Then use $data...
```

### 3. Serialized Cache (MongoDB)

This sample shows how to integrate with MongoDB using `MongoDBXmlSettingsCacheHandler`.

```php
include_once __DIR__ . "/lib/app.php";
include_once get_lib("mongodb.MongoDBHandler");
include_once get_lib("cache.xmlsettings.mongodb.MongoDBXmlSettingsCacheHandler");

$MongoDBHandler = new MongoDBHandler();
$MongoDBHandler->connect("192.168.1.68", "my_cache_db", "mdbu", "mdbp", 8097);

$CacheHandler = new MongoDBXmlSettingsCacheHandler();
$CacheHandler->setMongoDBHandler($MongoDBHandler);
$CacheHandler->setCacheTTL(60); // 60 seconds of cache ttl

// Set file path to be cached
$file_path = "my_cached_file_name"; // Must be unique.

// Get cached contents
if ($CacheHandler->isCacheValid($file_path))
	$cached_data = $CacheHandler->getCache($file_path);
else { // Otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); // Contents to cache
	$CacheHandler->setCache($file_path, $data); // setCache($file_path, $data, $renew_data = false)
}

// Then use $data...
```

### 4. Service Cache (Memcache)

This sample utilizes `MemcacheServiceCacheHandler` and introduces concepts like prefixes and service relations.

```php
include_once __DIR__ . "/lib/app.php";
include_once get_lib("cache.service.memcache.MemcacheServiceCacheHandler");

$MemcacheHandler = new MemcacheHandler();
$MemcacheHandler->connect("192.168.1.68", 8090);

$CacheHandler = new MemcacheServiceCacheHandler();
$CacheHandler->setMemcacheHandler($MemcacheHandler);
$CacheHandler->setRootPath("service_cache/"); // if file system handler: sys_get_temp_dir() . "/cache/service_cache/";
$CacheHandler->setDefaultTTL(60); // 60 seconds of cache ttl
$CacheHandler->setDefaultType("php"); // type can be: "php" or "text"

$prefix = "foo/bar/"; // optional
$key = "my_data_key"; // Must be unique for each cached service.

// Get cached contents
if ($CacheHandler->isValid($prefix, $key)) // isValid($prefix, $key, $ttl = false, $type = false)
	$data = $CacheHandler->get($prefix, $key); // get($prefix, $key, $type = false)
else { // Otherwise create cache for next time
	$data = array("foo" => "bar", "bar" => "foo"); // Contents to cache
	$CacheHandler->create($prefix, $key, $data); // create($prefix, $key, $data, $type = false)
}

// Then use $data...

// Note: You can also relate services to other services and then delete those related services. 
// For more details, check the file examples/service_cache_with_relations.php
```

### 5. Cache (Redis)

A Redis usage sample is planned.

```php
// TODO: Redis usage sample
```

---

## Further Examples

The repository includes more detailed examples to guide your implementation:
- [User Cache Example](examples/user_cache.php)
- [Serialized Cache Example](examples/xml_settings_cache.php)
- [Service Cache Example without Related Services](examples/service_cache.php)
- [Service Cache Example with Related Services](examples/service_cache_with_relations.php)

