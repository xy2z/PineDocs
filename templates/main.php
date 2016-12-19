<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?= $title ?></title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="css/base.css">
		<?php if (is_array($theme)): ?>
			<?php foreach ($theme as $theme_file): ?>
				<link rel="stylesheet" type="text/css" href="css/<?= strtolower($theme_file) ?>.css">
			<?php endforeach ?>
		<?php endif ?>
		<script>
			window.onload = function() {
			}
		</script>
		<style>
			body {
				font-family: Tahoma;
			}
		</style>
	</head>

	<body>
		<div id="main">
			<div id="menu">
				<a href="?"><img id="logo" src="<?= $logo ?>" /></a>
				<input type="text" id="search" name="search" value="<?= $search_value ?>" placeholder="<?= $search_placeholder ?>" autofocus>
				<?= $menu ?>
			</div>
			<div id="content">
				<div id="content_path"><?= $content_path ?></div>
				<div id="file_content"><?= $content ?></div>
			</div>
		</div>
	</body>

</html>
