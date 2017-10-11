<?php

	// Validate PHP version
	$phpversion = explode('.', phpversion());
	if ($phpversion[0] < 7) {
		exit('Error: This PHP version (' . phpversion() . ') is not supported. Please use PHP 7+');
	}

	// Check if yaml extension is installed
	if (!extension_loaded('yaml')) {
		exit('Error: Missing PHP YAML extension. Get it at https://pecl.php.net/package/yaml and https://github.com/xy2z/PineDocs/wiki/Install-YAML-extension-for-PHP-7.0-(Windows---Ubuntu)');
	}

	// Autoloader
	spl_autoload_register(function ($class_name) {
		require_once '../src/' . $class_name . '.php';
	});

	require_once '../src/toolbox.php';


	// Load config.
	PineDocs::load_config();
	$param_action = $_GET['action'] ?? null;
	$param_relative_path = $_GET['relative_path'] ?? null;


	// Get file from ajax call.
	if (isset($param_action) && $param_action === 'get_file_data' && isset($param_relative_path)) {
		header('Content-Type: application/json');
		$relative_path = utf8_decode($param_relative_path);
		$PineDocsFile = new PineDocsFile(xy_format_path(PineDocs::$config->content_dir . $relative_path));
		echo json_encode($PineDocsFile->get_data(), JSON_PRETTY_PRINT);
		exit;
	}

    if (isset($param_action) && $param_action === 'download' && isset($param_relative_path)) {
        $relative_path = utf8_decode($param_relative_path);

        $PineDocsFile = new PineDocsFile(xy_format_path(PineDocs::$config->content_dir . $relative_path));
        $quoted_name = sprintf('"%s"', addcslashes($PineDocsFile->basename, '"\\'));
        $size   = $PineDocsFile->filesize;

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $quoted_name);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        readfile(xy_format_path(PineDocs::$config->content_dir . $relative_path));
        exit;
    }


	// Get tree
	$tree = new PineDocsTree();


	// Get index file
	if (isset(PineDocs::$config->index)) {
		$indexes = [PineDocs::$config->index];
	} else {
		$indexes = ['index.md', 'index.html', 'index.txt', 'index'];
	}

	$index_data = 'Hello. No index file found.';
	$index_found = false;
	foreach ($indexes as $index) {
		$path = PineDocs::$config->content_dir . $index;
		if (file_exists($path)) {
			$PineDocsFile = new PineDocsFile($path);
			$index_data = $PineDocsFile->get_data();
		}
	}

	if (!$index_found) {
		// No index file added.
		// Use the first file found?
		$content = 'No index file found. Create a file called index.md in the content_dir.';
		$content_path = '';
	}


	// Prepare template.
	$main = new xyTemplate('main');
	$main->set_data(array(
		'search_value' => '',
		'search_placeholder' => 'Search here...',
	));


	// Set HTML variables
	$main->set_html(array(
		'menu' => $tree->render_tree_return(),
		'config' => array(
			'title' => PineDocs::$config->title,
			'code_transparent_bg' => PineDocs::$config->code_transparent_bg,
			'open_dirs' => PineDocs::$config->open_dirs,
			'index_data' => $index_data,
		),
		'errors' => PineDocs::$errors,
	));


	// Render main template.
	$main->render();
