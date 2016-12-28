<?php


	class xyTemplate {


		private $template_name;
		private $data;
		private $html;


		public function __construct(string $template_name) {
			$this->template_name = $template_name;
		}


		public function set_data(array $data) {
			// All $data will be htmlspecialchars().
			$this->data = $data;
		}


		public function set_html(array $html) {
			// HTML Data that won't be htmlspecialchars()
			$this->html = $html;
		}


		public function render() {
			extract(self::multi_escape($this->data));
			extract($this->html);
			$template_content = require_once('templates/' . $this->template_name . '.php');
		}


		static public function multi_escape(array $arr) : array {
			foreach ($arr as $key => $value) {
				if (is_array($value)) {
					$arr[$key] = self::multi_escape($value);
					// yield multi_escape($value);
				} else {
					$arr[$key] = htmlspecialchars($value);
				}
			}
			return $arr;
		}

	}

	/*
	$xytemplate = new xyTemplate('test-template');

	$xytemplate->render_template('test-template', array(
		'description' => 'This is php native template',
		'users' => array(
			'dude',
			'al',
			'xyzz',
			'ap',
		),
		'bye' => 'see you later :)'
	), array(
		'h1' => 'PHP <strong>Template</strong>',
	));
	*/