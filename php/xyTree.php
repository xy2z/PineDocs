<?php


	class xyTree {

		private $content_dir;
		private $tree; // files are duplicate in $tree and $all_files...
		private $files;
		public $all_files;
		public $active_hash;


		public function __construct(string $dir) {
			$this->content_dir = $dir;
			$this->tree = $this->get_tree();
		}


		public function get_tree($dir = null) : stdClass {
			$tree = (object) array(
				'dirs' => array(),
				'files' => array()
			);

			if (is_null($dir)) {
				// Start from $this->content_dir.
				$dir = $this->content_dir;
			}

			foreach (scandir($dir) as $item) {
				if (in_array($item, array('.', '..'))) {
					continue;
				}

				// $full_path = str_replace('\\', '/', $dir . '/' . $item);
				$full_path = xy_format_path($dir . '/' . $item);

				if (is_dir($full_path)) {
					$tree->dirs[$item] = $this->get_tree($full_path);
				} else {
					$sha1 = sha1($full_path);
					$this->all_files[$sha1] = $tree->files[$sha1] = new xyDocsFile($full_path);

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
				$return .= '<li><a href="#" class="link_dir"><i class="fa fa-folder" aria-hidden="true"></i>' . $dir . '</a></li>';
				$return .= $this->render_tree_return($content);
			}

			foreach ($tree->files as $file) {
				$return .= '<li><a href="#' . xy_format_path($file->relative_path) . '" class="link_file"><i class="fa fa-file-o" aria-hidden="true"></i>' . $file->basename . '</a></li>';
			}

			$return .= '</ul>';
			return $return;
		}

	}
