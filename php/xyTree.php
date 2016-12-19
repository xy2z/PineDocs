<?php


	class xyTree {

		private $dir;
		private $tree; // files are duplicate in $tree and $all_files...
		public $all_files;


		public function __construct(string $dir) {
			$this->dir = $dir;
			$this->tree = $this->get_tree();
		}


		public function get_tree($dir = null) : stdClass {
			$result = (object) array(
				'dirs' => array(),
				'files' => array()
			);

			if (is_null($dir)) {
				// Start from $this->dir.
				$dir = $this->dir;
			}

			foreach (scandir($dir) as $item) {
				if (in_array($item, array('.', '..'))) {
					continue;
				}

				$full_path = str_replace('\\', '/', $dir . '/' . $item);

				if (is_dir($full_path)) {
					$result->dirs[$item] = $this->get_tree($full_path);
				} else {
					$this->all_files[sha1($full_path)] = $result->files[] = new xyDocsFile($full_path);

				}
			}

			return $result;
		}


		public function render_tree_return($tree = null, $return = '') : string {
			$return .= '<ul>';

			if (is_null($tree)) {
				// Start from $this->tree.
				$tree = $this->tree;
			}

			foreach ($tree->dirs as $dir => $content) {
				$return .= '<li><a href="#"><i class="fa fa-folder" aria-hidden="true"></i>' . $dir . '</a></li>';
				$return .= $this->render_tree_return($content);
			}

			foreach ($tree->files as $file) {
				$return .= '<li><a href="?file=' . urlencode(sha1($file->full_path)) . '"><i class="fa fa-file-o" aria-hidden="true"></i>' . $file->basename . '</a></li>';
			}

			$return .= '</ul>';
			return $return;
		}

	}
