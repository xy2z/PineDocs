<?php

	// Autoloader
	spl_autoload_register(function ($class_name) {
		require_once 'php/' . $class_name . '.php';
	});

	require_once 'php/toolbox.php';


	// Load config.
	PineDocs::load_config();


	// Get file from ajax call.
	if (isset($_GET['action']) && $_GET['action'] == 'get_file_data' && isset($_GET['relative_path'])) {
		header('Content-Type: application/json');
		$PineDocsFile = new PineDocsFile(xy_format_path(PineDocs::$config->content_dir . $_GET['relative_path']));
		echo json_encode($PineDocsFile->get_data());
		exit;
	}


	// Get tree
	require_once 'php/xyTree.php';
	$tree = new xyTree();


	// Get index file
	if (isset(PineDocs::$config->index)) {
		$indexes = [PineDocs::$config->index];
	} else {
		$indexes = ['index.md', 'index.html', 'index.txt', 'index'];
	}

	$index_found = false;
	foreach ($indexes as $index) {
		$path = PineDocs::$config->content_dir . $index;
		if (file_exists($path)) {
			// $content = nl2br(file_get_contents($index));
			// $content_path = $index;
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
		// 'search_value' => '',
		// 'search_placeholder' => 'Search here...',
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
