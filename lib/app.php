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

include_once __DIR__ . "/util/import/lib.php";

$log_level = isset($log_level) && is_numeric($log_level) ? $log_level: 2;//only exceptions and errors
$log_file = !empty($log_file) ? $log_file : (sys_get_temp_dir() ? sys_get_temp_dir() : "/tmp") . "/" . basename(dirname(__DIR__)) . ".log"; //eg: /tmp/phpdblib.log

function launch_exception(Exception $exception) {
	throw $exception;
	
	$message = $exception->getMessage();
	$problem = isset($exception->problem) ? $exception->problem : null;
	$msg = $message != $problem ? "$message\n$problem" : $problem;
	$msg .= "\n" . $exception->getTraceAsString();
	
	debug_log($msg, "exception");
	
	return false;
}

function debug_log_function($func, $args, $log_type = "debug") {
	$message = $func . "(";
	
	if (is_array($args))
		foreach($args as $arg) {
			$message .= $message ? ", " : "";
			
			if (is_array($arg)) 
				$message .= stripslashes(json_encode($arg));
			else if (is_object($arg)) 
				$message .= "Object(" . get_class($arg) . ")";
			else if ($arg === true)
				$message .= "true";
			else if ($arg === false) 
				$message .= "false";
			else if ($arg == null)
				$message .= "null";
			else if (is_numeric($arg)) 
				$message .= (int)$arg;
			else 
				$message .= "'" . $arg . "'";
		}
		
	$message .= ")";
	debug_log($message, $log_type);
}

function debug_log($message, $log_type = "debug") {
	global $log_file, $log_level;
	
	if ($log_file) {
		$msg = "[" . date("Y-m-d H:i:s") . "][$log_type] " . $message . PHP_EOL;
		
		if ($log_level >= 1) {
			$log = false;
			
			switch (strtolower($log_type)) {
				case "exception":
					if ($log_level >= 1)
						$log = true;
					break;
				case "error":
					if ($log_level >= 2)
						$log = true;
					break;
				case "info":
					if ($log_level >= 3)
						$log = true;
					break;
				default:
					if ($log_level >= 4)
						$log = true;
			}
			
			if ($log) {
				//error_log($msg, 3, $log_file); //do not use error_log otherwise the color will not shown
				file_put_contents($log_file, $msg, FILE_APPEND);
			}
		}
	}
}
?>
