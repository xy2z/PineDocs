<?php

	// Validate PHP version
	$phpversion = explode('.', phpversion());
	if ($phpversion[0] < 7) {
		exit('Error: This PHP version (' . phpversion() . ') is not supported. Please use PHP 7+');
	}

	// Load composer
	$composer_autoload_path = __DIR__ . '/../src/vendor/autoload.php';
	if (!file_exists($composer_autoload_path)) {
		exit('Error: Missing composer libraries. Try running "composer install" to complete the installation.');
	}
	require $composer_autoload_path;

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
		$relative_path = urldecode($param_relative_path);
		$PineDocsFile = new PineDocsFile(xy_format_path(PineDocs::$config->content_dir . $relative_path));
		echo json_encode($PineDocsFile->get_data(), JSON_PRETTY_PRINT);
		exit;
	}

    if (isset($param_action) && $param_action === 'download' && isset($param_relative_path)) {
        $relative_path = urldecode($param_relative_path);

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
	foreach ($indexes as $index) {
		$path = PineDocs::$config->content_dir . $index;
		if (file_exists($path)) {
			$PineDocsFile = new PineDocsFile($path);
			$index_data = $PineDocsFile->get_data();
		}
	}


	// Prepare template.
	$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/templates');
	$twig = new \Twig\Environment($loader);

	// Set template replacements
	$template_replacements = array(
		'menu' => $tree->render_tree_return(),
		'version' => PineDocs::version,
		'search' => array(
			'value' => '',
			'placeholder' => 'Filter...',
		),
		'config' => array(
			'title' => PineDocs::$config->title,
			'code_transparent_bg' => PineDocs::$config->code_transparent_bg,
			'open_dirs' => PineDocs::$config->open_dirs,
			'layout' => strtolower(basename(PineDocs::$config->layout)),
			'color_scheme' => strtolower(basename(PineDocs::$config->color_scheme)),
			'highlight_theme' => PineDocs::$config->highlight_theme,
			'index_data' => $index_data,
			'render_footer' => PineDocs::$config->render_footer,
			'render_max_file_size' => PineDocs::$config->render_max_file_size,
			'font_family' => PineDocs::$config->font_family,
			'font_size' => PineDocs::$config->font_size,
			'break_code_blocks' => PineDocs::$config->break_code_blocks,
			// 'hide_folders_in_navigation' => PineDOcs::$config->hide_folders_in_navigation,
			'enable_mathjax' => PineDocs::$config->enable_mathjax,
			'mathjax_configuration' => PineDocs::$config->mathjax_configuration,
		),
		'errors' => PineDocs::$errors,
	);

	// Render main template.
	echo $twig->render('main.html', $template_replacements);
