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


	// Get file from ajax call.
	if (isset($_GET['action']) && $_GET['action'] == 'get_file_data' && isset($_GET['relative_path'])) {
		header('Content-Type: application/json');
		$relative_path = utf8_decode($_GET['relative_path']);
		$PineDocsFile = new PineDocsFile(xy_format_path(PineDocs::$config->content_dir . $relative_path));
		echo json_encode($PineDocsFile->get_data());
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
		'js_vars' => array(
			'title' => PineDocs::$config->title,
			'code_transparent_bg' => PineDocs::$config->code_transparent_bg,
			'open_dirs' => PineDocs::$config->open_dirs,
			'index_data' => $index_data
		)
	));


	// Render main template.
	$main->render();
