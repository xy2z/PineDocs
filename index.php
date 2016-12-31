<?php

	// Constants
	define('xyDocs_version', '1.0.0-beta.1');

	// Autoloader
	spl_autoload_register(function ($class_name) {
		require_once 'php/' . $class_name . '.php';
	});

	error_reporting(E_ALL);

	require_once 'php/toolbox.php';


	// Load config.
	$config = (object) yaml_parse_file('config.yaml');
	xyDocsFile::$content_dir = xy_format_path($config->content_dir);

	if (isset($_GET['action']) && $_GET['action'] == 'get_file_data' && isset($_GET['relative_path'])) {
		header('Content-Type: application/json');
		$xyDocsFile = new xyDocsFile(xy_format_path($config->content_dir) . $_GET['relative_path']);
		echo $xyDocsFile->get_json_data();
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
		'js_vars' => array(
			'title' => $config->title ?? 'xyDocs',
			'code_transparent_bg' => $config->code_transparent_bg ?? false,
			'open_dirs' => $config->open_dirs ?? 0
		)
	));

	if (isset($_GET['file'])) {
		$content = nl2br(file_get_contents($tree->all_files[$_GET['file']]->full_path));
		$content_path = $tree->all_files[$_GET['file']]->full_path;
		$tree->active_hash = $_GET['file'];
	} else {
		$content = nl2br(file_get_contents('default-welcome.md'));
		$content_path = 'default-welcome.md';
	}

	// Set HTML variables
	$main->set_html(array(
		'menu' => $tree->render_tree_return(),
		'content_path' => $content_path,
		'content' => $content
	));

	// Render main template.
	$main->render();
