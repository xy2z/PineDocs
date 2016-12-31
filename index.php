<?php

	// Constants
	define('xyDocs_version', '1.0.0-beta.2');

	// Autoloader
	spl_autoload_register(function ($class_name) {
		require_once 'php/' . $class_name . '.php';
	});

	require_once 'php/toolbox.php';


	// Load config.
	$config = (object) yaml_parse_file('config.yaml');
	// Make sure the 'content_dir' is ending on a '/' for security.
	$config->content_dir = xy_format_path($config->content_dir, true);
	xyDocsFile::$content_dir = $config->content_dir;

	if (isset($_GET['action']) && $_GET['action'] == 'get_file_data' && isset($_GET['relative_path'])) {
		header('Content-Type: application/json');
		$xyDocsFile = new xyDocsFile(xy_format_path($config->content_dir . $_GET['relative_path']));
		echo json_encode($xyDocsFile->get_data());
		exit;
	}

	// Get tree
	require_once 'php/xyTree.php';
	$tree = new xyTree($config->content_dir);

	// Make sure $theme is array.
	$theme = $config->theme ?? array('default');
	if (!is_array($theme)) {
		$theme = array($theme);
	}


	// Get index file
	if (isset($config->index)) {
		$indexes = [$config->index];
	} else {
		$indexes = ['index.md', 'index.html', 'index.txt', 'index'];
	}

	$index_found = false;
	foreach ($indexes as $index) {
		$path = $config->content_dir . $index;
		if (file_exists($path)) {
			// $content = nl2br(file_get_contents($index));
			// $content_path = $index;
			$xyDocsFile = new xyDocsFile($path);
			$index_data = $xyDocsFile->get_data();
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
		'title' => $config->title ?? 'xyDocs 1.0',
		'theme' => $theme,
		'highlight_theme' => strtolower(str_replace(' ', '_', $config->highlight_theme)) ?? 'default',
		'logo' => $config->logo ?? '../Logo.png',
		'render_footer' => $config->render_footer ?? true,
		'search_value' => '',
		'search_placeholder' => 'Search here...',
		'xyDocs_version' => xyDocs_version,
	));


	// Set HTML variables
	$main->set_html(array(
		'menu' => $tree->render_tree_return(),
		'js_vars' => array(
			'title' => $config->title ?? 'xyDocs',
			'code_transparent_bg' => $config->code_transparent_bg ?? false,
			'open_dirs' => $config->open_dirs ?? 0,
			'index_data' => $index_data
		)
	));


	// Render main template.
	$main->render();
