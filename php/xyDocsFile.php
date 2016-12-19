<?php


	class xyDocsFile {


		public $full_path;
		public $basename;
		public $pathinfo;
		public $content;


		public function __construct(string $full_path) {
			$this->full_path = $full_path;
			$this->basename = basename($full_path);
			$this->pathinfo = pathinfo($full_path);

			// Only get content if it's not binary.
			if (isset($this->pathinfo['extension']) && in_array(strtolower($this->pathinfo['extension']), array('txt', 'md', 'markdown', 'php'))) {
				$this->content = file_get_contents($full_path);
			}
		}

	}
