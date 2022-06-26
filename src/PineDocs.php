<?php

	use Symfony\Component\Yaml\Yaml;

	class PineDocs {

		const version = '1.2.5';

		static public $config;

		static public $errors = array();

		static public function load_config() {
			$config_path = '../config/config.yaml';
			if (!file_exists($config_path)) {
				// Create config.yaml by copying config-example.yaml.
				$create = copy('../config/config-example.yaml', '../config/config.yaml');
				if (!$create) {
					exit('Error: Could not automatically create config/config.yaml. You need to manually copy config/config-example.yaml to config/config.yaml');
				}
			}

			// Load config
			$config = Yaml::parseFile($config_path);

			if (!$config) {
				exit('Error: Could not parse/read config.yaml.');
			}

			self::$config = (object) $config;

			// Make sure the 'content_dir' is ending on a '/' for security.
			self::set_content_dir();

			// Set defaults
			if (!isset(self::$config->title) || (empty(self::$config->title))) {
				self::$config->title = 'PineDocs';
			}

			// Make sure $layout is set.
			if (!isset(self::$config->layout) || (empty(self::$config->layout))) {
				self::$config->layout = 'default';
			} else if (!file_exists('../public/layouts/' . basename(self::$config->layout) . '.css')) {
				self::$errors[] = 'Layout not found: "' . self::$config->layout . '". Using default.';
				self::$config->layout = 'default';
			}

			// Make sure $color_scheme is set.
			if (!isset(self::$config->color_scheme) || (empty(self::$config->color_scheme))) {
				self::$config->color_scheme = 'PineDocs';
			} else if (!file_exists('../public/color-schemes/' . basename(self::$config->color_scheme) . '.css')) {
				// Validate color_scheme exists.
				self::$errors[] = 'Color-scheme not found: "' . self::$config->color_scheme . '". Using default.';
				self::$config->color_scheme = 'PineDocs';
			}

			if (!isset(self::$config->highlight_theme) || (empty(self::$config->highlight_theme))) {
				self::$config->highlight_theme = 'default';
			}

			if (!isset(self::$config->code_transparent_bg)) {
				self::$config->code_transparent_bg = false;
			}

			if (!isset(self::$config->open_dirs)) {
				self::$config->open_dirs = 0;
			}

			if (!isset(self::$config->show_file_extension)) {
				# Set default value.
				self::$config->show_file_extension = true;
			}

			if (!isset(self::$config->menu_link_format)) {
				self::$config->menu_link_format = 'default';
			}

			if (!isset(self::$config->render_max_file_size)) {
				self::$config->render_max_file_size = 50;
			}

			if (!isset(self::$config->font_family)) {
				self::$config->font_family = '';
			}

			if (!isset(self::$config->font_size)) {
				self::$config->font_size = '';
			}

			if (!isset(self::$config->no_extension_markdown)) {
				self::$config->no_extension_markdown = true;
			}

			if (!isset(self::$config->break_code_blocks)) {
				self::$config->break_code_blocks = false;
			}

			if (!isset(self::$config->hide_folders_in_navigation)) {
				self::$config->hide_folders_in_navigation = [];
			} else {
				// Format all values as lowercase.
				foreach (self::$config->hide_folders_in_navigation as $key => $value) {
					self::$config->hide_folders_in_navigation[$key] = strtolower($value);
				}
			}

			if (!isset(self::$config->enable_mathjax)) {
				self::$config->enable_mathjax = false;
			}

			if (self::$config->enable_mathjax) {
				self::$config->mathjax_configuration = self::load_config_mathjax();
			} else {
				self::$config->mathjax_configuration = '';
			}

			// Load Marked configuration
			self::$config->marked_configuration = self::load_config_marked();
		}


		static public function exclude_file(string $full_path) : bool {
			if (isset(self::$config->exclude_files)) {
				foreach (self::$config->exclude_files as $value) {
					if (substr($value, 0, 1) == '/') {
						// Regex
						if (preg_match($value, $full_path)) {
							return true;
						}
					} else {
						// Not regex.
						if (stripos($full_path, $value) !== false) {
							return true;
						}
					}
				}
			}
			return false;
		}


		static private function set_content_dir() {
			if (!empty(self::$config->content_dir) && !is_dir(self::$config->content_dir)) {
				exit("Error: 'content_dir' doesn't exist or isn't readable");
			}

			if (empty(self::$config->content_dir)) {
				// Set default content dir.
				self::$config->content_dir = preg_replace('/public$/', 'content', getcwd());
			}

			self::$config->content_dir = xy_format_path(self::$config->content_dir, true);
		}

		static private function load_config_mathjax() {
			$config_mathjax_path = '../config/mathjax.js';
			if (!file_exists($config_mathjax_path)) {
				// Create mathjax.js by copying mathjax-example.js.
				$create = copy('../config/mathjax-example.js', '../config/mathjax.js');
				if (!$create) {
					exit('Error: Could not automatically create config/mathjax.js. You need to manually copy config/mathjax-example.js to config/mathjax.js');
				}
			}

			// Read data
			$data_mathjax = file_get_contents($config_mathjax_path);

			if (!$data_mathjax) {
				exit('Error: Could not read config/mathjax.js.');
			}

			return $data_mathjax;
		}

		static private function load_config_marked() {
			$config_marked_path = '../config/marked.js';

			if (!file_exists($config_marked_path)) {
				// Create marked.js by copying marked-example.js.
				$create = copy('../config/marked-example.js', '../config/marked.js');
				if (!$create) {
					exit('Error: Could not automatically create config/marked.js. You need to manually copy config/marked-example.js to config/marked.js');
				}
			}

			// Read data
			$data_marked = file_get_contents($config_marked_path);

			if (!$data_marked) {
				exit('Error: Could not read config/marked.js.');
			}

			return $data_marked;
		}

	}
