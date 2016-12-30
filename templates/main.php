<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?= $title ?></title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="css/base.css">
		<?php if (is_array($theme)): ?>
			<?php foreach ($theme as $theme_file): ?>
				<link rel="stylesheet" type="text/css" href="css/<?= strtolower($theme_file) ?>.css">
			<?php endforeach ?>
		<?php endif ?>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/0.3.6/marked.min.js"></script>
		<script src="js/xyDocs.js"></script>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/styles/<?= $highlight_theme ?>.min.css">
		<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/highlight.min.js"></script>
		<script>
			var config = <?= json_encode($js_vars) ?>
		</script>
	</head>

	<body>
		<div id="main">

			<div id="menu_wrapper">
				<div id="menu">
					<div id="menu_top">
						<a href="."><img id="logo" src="<?= $logo ?>" /></a>
						<!-- <input type="text" id="search" name="search" value="<?= $search_value ?>" placeholder="<?= $search_placeholder ?>" autofocus> -->
					</div>
					<?= $menu ?>
					<?php if ($render_footer): ?>
						<footer>
							xyDocs <?= $xyDocs_version ?> (<a target="_blank" href="https://github.com/xy2z/xyDocs/releases">Check for updates</a>)
						</footer>
					<?php endif ?>
				</div>
			</div>

			<div id="content">
				<div id="content_path"><?= $content_path ?></div>
				<div id="loading"></div>
				<div id="file_content">
					<?= $content ?>
				</div>
			</div>

		</div>
	</body>

</html>
