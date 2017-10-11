<?php


	class PineDocsFile {


		public $full_path; // Full system path.
		public $relative_path; // Relative path to content_dir.
		public $basename;
		public $pathinfo;
		public $filesize;
		public $mimetype;
		private $content;
		private $type;
		private $base64_encode = false;


		public function __construct(string $full_path) {
			if (strpos($full_path, '../') || PineDocs::exclude_file($full_path)) {
				// The client must never have access to anything but the 'content_dir'.
				exit;
			}

			$this->full_path = $full_path;
			$this->relative_path = utf8_encode(str_replace(PineDocs::$config->content_dir, '', $this->full_path));
			$this->basename = $this->get_basename();
			$this->pathinfo = pathinfo($this->full_path);
			$this->filesize = filesize($this->full_path);

			if (isset($this->pathinfo['extension'])) {
				$this->pathinfo['extension'] = strtolower($this->pathinfo['extension']);
			}

			$this->set_file_type();
			$this->set_content();
		}


		public function get_data() {
			$data = array(
				'relative_path' => $this->relative_path,
				'basename' => $this->basename,
				'extension' => $this->pathinfo['extension'],
				'filesize' => $this->filesize,
				'type' => $this->type,
                'content' => $this->content
			);

			return $data;
		}


		private function set_file_type() {
            $mimetype = mime_content_type($this->full_path);
            preg_match("/(?<common>[a-z]+)\/(?<type>.*)/i", $mimetype, $this->mimetype);

			if (!isset($this->pathinfo['extension'])) {
				return;
			}

			if (in_array($this->pathinfo['extension'], array('md', 'markdown')) || ($this->mimetype['type'] === 'markdown') ) {
				// Markdown.
				$this->type = 'markdown';
			} else if ($this->mimetype['common'] === 'image' && $this->mimetype['type'] !== 'svg+xml') {
				// Image.
				$this->type = 'image';
				$this->base64_encode = true;
			} else if ($this->mimetype['common'] === 'image' && $this->mimetype['type'] === 'svg+xml') {
				$this->type = 'svg';
				$this->base64_encode = true;
			} else if ($this->mimetype['common'] === 'audio') {
				$this->type = 'audio';
				$this->base64_encode = true;
			} else if (in_array($this->pathinfo['extension'], array('mp4')) || $this->mimetype['type'] === 'mp4') {
				$this->type = 'video';
				$this->base64_encode = true;
			} else if (in_array($this->pathinfo['extension'], array('css', 'php', 'js', 'xml', 'c', 'cpp', 'h', 'bat', 'sh', 'bash', 'scss', 'sql', 'yaml', 'yml', 'conf', 'ini', 'cf', 'pre'))) {
				// Code.
				$this->type = 'code';
			}
			
			return $this->type;
		}


		private function get_basename() {

			if (PineDocs::$config->show_file_extension) {
				return utf8_encode(basename($this->full_path));
			} else {
				$basename = utf8_encode(pathinfo($this->full_path, PATHINFO_FILENAME));
			}

			return $basename;
		}

		private function set_content() {
            $content = file_get_contents($this->full_path);

            /*
            * if we still didn't know type,
            * better to check for unsafe content (which can broke json_encode) and set null.
            * is_executable not work for all cases, .msi e.g. So we use mimetypes.
            */
            if ($this->type === null) {
                if ($this->mimetype['common'] !== 'text') {
                    $this->content = null;
                    return false;
                }
            }

            if ($this->base64_encode) {
                $this->content = base64_encode($content);
            } else {
                $this->content = $content;
            }

            return $this->content;
        }

	}
