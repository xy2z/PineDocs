<?php


	class PineDocsTree {

		private $tree;


		public function __construct() {
			$this->tree = $this->get_tree();
		}


		public function get_tree($dir = null) : stdClass {
			$tree = (object) array(
				'dirs' => array(),
				'files' => array()
			);

			if (is_null($dir)) {
				// Start from content_dir.
				$dir = PineDocs::$config->content_dir;
			}

			foreach (scandir($dir) as $item) {
				if (in_array($item, array('.', '..'))) {
					continue;
				}

				// $full_path = str_replace('\\', '/', $dir . '/' . $item);
				$full_path = xy_format_path($dir . '/' . $item);

				// Exclude item?
				if (PineDocs::exclude_file($full_path)) {
					continue;
				}

				if (is_dir($full_path)) {
					$tree->dirs[$item] = $this->get_tree($full_path);
				} else {
					if (realpath($full_path)) {
						$tree->files[] = new PineDocsFile($full_path);
					}
				}
			}

			return $tree;
		}


		public function render_tree_return($tree = null, $return = '') : string {
			$return .= '<ul>';

			if (is_null($tree)) {
				// Start from $this->tree.
				$tree = $this->tree;
			}

			foreach ($tree->dirs as $dir => $content) {
				// Hide this folder from frontend tree.
				if (in_array(strtolower($dir), PineDocs::$config->hide_folders_in_navigation)) {
					continue;
				}


				$return .= '<li class="folder"><a href="#" class="link_dir alert"><i class="fa fa-folder" aria-hidden="true"></i>' . self::format_menu_item($dir) . '</a></li>';
				$return .= $this->render_tree_return($content);
			}

			foreach ($tree->files as $file) {
				$return .= '<li class="link"><a href="#' . xy_format_path($file->relative_path) . '" class="link_file"><i class="fa fa-file-o alert" aria-hidden="true"></i>' . self::format_menu_item($file->basename) . '</a></li>';
			}

			$return .= '</ul>';
			return $return;
		}


		static public function format_menu_item(string $item) {
			if (PineDocs::$config->menu_link_format == 'ucfirst') {
				return ucfirst($item);
			}

			if (PineDocs::$config->menu_link_format == 'uppercase') {
				return strtoupper($item);
			}

			if (PineDocs::$config->menu_link_format == 'lowercase') {
				return strtolower($item);
			}

			if (PineDocs::$config->menu_link_format == 'ucwords') {
				return ucwords($item);
			}

			// Default/unknown format.
			return $item;
		}


	}
