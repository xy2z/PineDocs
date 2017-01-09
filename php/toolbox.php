<?php

	function xy_format_path($path, $dir = false) {
		$path = str_replace(array('\\', '//'), '/', $path);

		if ($dir && (substr($path, -1) != '/')) {
			$path .= '/';
		}


		return $path;
	}
