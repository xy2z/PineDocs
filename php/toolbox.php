<?php

	function xy_format_path($path) {
		return str_replace('\\', '/', $path);
	}

	function xy_urlencode($path) {
		$path = str_replace(array('/', '\\'), '|', $path);
		return $path;
	}

	function xy_urldecode($path) {
		$path = str_replace('|', '/', $path);
		return $path;
	}
