<?php

	class PineDocs {

		const version = '1.0.0-beta.5';

		static public $config;

		static public $errors = array();

		static public function load_config() {
			$config_path = '../config/config.yaml';
			if (!file_exists($config_path)) {
				exit('Error: Could not find config.yaml. Copy config/config-example.yaml to config/config.yaml');
			}

			// Load config
			$config = yaml_parse_file($config_path);

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

			// Make sure $theme is set.
			if (!isset(self::$config->theme) || (empty(self::$config->theme))) {
				self::$config->theme = 'default';
			} else if (!file_exists('../public/themes/' . basename(self::$config->theme) . '.css')) {
				self::$errors[] = 'Theme not found: "' . self::$config->theme . '". Using default.';
				self::$config->theme = 'default';
			}

			// Make sure $color_scheme is set.
			if (!isset(self::$config->color_scheme) || (empty(self::$config->color_scheme))) {
				self::$config->color_scheme = 'PineDocs';
			} else if (!file_exists('../public/themes/color-schemes/' . basename(self::$config->color_scheme) . '.css')) {
				// Validate color_scheme exists.
				self::$errors[] = 'Color-scheme not found: "' . self::$config->color_scheme . '". Using default.';
				self::$config->color_scheme = 'PineDocs';
			}

			if (!isset(self::$config->logo) || empty(self::$config->logo)) {
				// Use default logo.
				self::$config->logo = 'PineDocs.png';
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


	}
