<?php

	// Autoloader
	spl_autoload_register(function ($class_name) {
		require_once 'php/' . $class_name . '.php';
	});


	// Load config.
	$config = (object) yaml_parse_file('config.yaml');


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
		'title' => $config->title ?? 'xyDocs v0.1',
		'theme' => $theme,
		'logo' => $config->logo ?? '../Logo.png',
		'search_value' => '',
		'search_placeholder' => 'Search here...',
	));

	if (isset($_GET['file'])) {
		$content = nl2br(file_get_contents($tree->all_files[$_GET['file']]->full_path));
		$content_path = $tree->all_files[$_GET['file']]->full_path;
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
