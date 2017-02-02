<!-- PineDocs - https://github.com/xy2z/PineDocs -->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= PineDocs::$config->title ?></title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="css/base.css">
		<link rel="stylesheet" type="text/css" href="themes/<?= strtolower(PineDocs::$config->theme) ?>.css">
		<link rel="stylesheet" type="text/css" href="themes/color-schemes/<?= strtolower(PineDocs::$config->color_scheme) ?>.css">
	</head>

	<body>
		<div id="main" class="container">

				<div id="menu_wrapper" class="">
					<div id="menu_close">X</div>

					<div id="menu" class="navbar-default">
						<div id="menu_top">
							<a href="."><img id="logo" src="<?= PineDocs::$config->logo ?>" /></a>
							<!-- <input type="text" id="search" name="search" value="<?= $search_value ?>" placeholder="<?= $search_placeholder ?>" autofocus> -->
						</div>
						<?= $menu ?>
						<?php if (PineDocs::$config->render_footer): ?>
							<footer>
								PineDocs <?= PineDocs::version ?> (<a target="_blank" href="https://github.com/xy2z/PineDocs/releases">Check for updates</a>)
							</footer>
						<?php endif ?>
					</div>
				</div>

				<div id="content_wrapper">
					<div id="content_top">
						<a id="mobile_nav_icon"><i class="fa fa-bars" aria-hidden="true"></i></a>
						<span id="content_path"></span>
					</div>
					<div id="loading"></div>
					<div id="file_content"></div>
				</div>

		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/0.3.6/marked.min.js"></script>
		<script src="js/PineDocs.js"></script>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/styles/<?= PineDocs::$config->highlight_theme ?>.min.css">
		<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/highlight.min.js"></script>
		<script>
			var config = <?= json_encode($js_vars) ?>
		</script>
	</body>

</html>
