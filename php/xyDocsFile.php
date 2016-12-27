<?php


	class xyDocsFile {


		static public $content_dir;
		public $full_path; // Full system path.
		public $relative_path; // Relative path to content_dir.
		public $basename;
		public $pathinfo;
		public $content;
		private $type;
		private $is_binary = false;


		public function __construct(string $full_path) {
			// var_dump($full_path);
		// public function __construct(string $content_dir, string $relative_path) {
			// $this->full_path = $content_dir . $relative_path;
			$this->full_path = $full_path;
			$this->relative_path = str_replace(self::$content_dir, '', $full_path);
			$this->basename = basename($full_path);
			$this->pathinfo = pathinfo($full_path);
			$this->pathinfo['extension'] = strtolower($this->pathinfo['extension']);
			$this->set_file_type();

			// Only get content if it's not binary.
			// if (isset($this->pathinfo['extension']) && in_array(strtolower($this->pathinfo['extension']), array('txt', 'md', 'markdown', 'php'))) {
				// $this->content = file_get_contents($full_path);
			// }
		}


		public function get_json_data() {
			$data = array(
				// 'full_path' => $this->full_path,
				'relative_path' => $this->relative_path,
				'basename' => $this->basename,
				'pathinfo' => $this->pathinfo,
				'type' => $this->type
			);

			if ($this->is_binary) {
				$data['content'] = base64_encode(file_get_contents($this->full_path));
			} else {
				$data['content'] = file_get_contents($this->full_path);
			}

			return json_encode($data);
		}


		private function set_file_type() {
			if (in_array($this->pathinfo['extension'], array('md', 'markdown'))) {
				// Markdown.
				$this->type = 'markdown';
			} else if (in_array($this->pathinfo['extension'], array('jpg', 'jpeg', 'png', 'gif'))) {
				$this->type = 'image';
				$this->is_binary = true;
			} else if (in_array($this->pathinfo['extension'], array('mp3'))) {
				$this->type = 'audio';
				$this->is_binary = true;
			}
		}

	}
