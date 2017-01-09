<?php


	class PineDocsFile {


		public $full_path; // Full system path.
		public $relative_path; // Relative path to content_dir.
		public $basename;
		public $pathinfo;
		public $filesize;
		private $type;
		private $is_binary = false;


		public function __construct(string $full_path) {
			if (strpos($full_path, '../') || PineDocs::exclude_file($full_path)) {
				// The client must never have access to anything but the 'content_dir'.
				exit;
			}

			$this->full_path = mb_convert_encoding($full_path, 'UTF-8', 'ISO-8859-1');

			$this->relative_path = str_replace(PineDocs::$config->content_dir, '', $this->full_path);
			$this->basename = utf8_encode(basename($this->full_path));
			$this->pathinfo = pathinfo($this->full_path);
			$this->filesize = filesize($this->full_path);

			if (isset($this->pathinfo['extension'])) {
				$this->pathinfo['extension'] = strtolower($this->pathinfo['extension']);
			}

			$this->set_file_type();
		}


		public function get_data() {
			$data = array(
				'relative_path' => $this->relative_path,
				'basename' => $this->basename,
				'extension' => $this->pathinfo['extension'],
				'filesize' => $this->filesize,
				'type' => $this->type
			);

			if ($this->is_binary) {
				$data['content'] = base64_encode(file_get_contents($this->full_path));
			} else {
				// $data['content'] = utf8_encode(file_get_contents($this->full_path));
				$data['content'] = file_get_contents($this->full_path);
			}

			return $data;
		}


		private function set_file_type() {
			if (!isset($this->pathinfo['extension'])) {
				return;
			}

			if (in_array($this->pathinfo['extension'], array('md', 'markdown'))) {
				// Markdown.
				$this->type = 'markdown';
			} else if (in_array($this->pathinfo['extension'], array('jpg', 'jpeg', 'png', 'gif'))) {
				// Image.
				$this->type = 'image';
				$this->is_binary = true;
			} else if (in_array($this->pathinfo['extension'], array('mp3', 'ogg'))) {
				$this->type = 'audio';
				$this->is_binary = true;
			} else if (in_array($this->pathinfo['extension'], array('mp4'))) {
				$this->type = 'video';
				$this->is_binary = true;
			} else if (in_array($this->pathinfo['extension'], array('css', 'php', 'js', 'xml', 'c', 'cpp', 'h', 'bat', 'sh', 'bash', 'scss', 'sql', 'yaml', 'yml', 'conf', 'ini', 'cf', 'pre'))) {
				// Code.
				$this->type = 'code';
			}
		}

	}
