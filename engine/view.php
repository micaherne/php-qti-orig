<?php 
require_once 'engine.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>View item - PHP-QTI</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<!-- Le styles -->
<link href="bootstrap/docs/assets/css/bootstrap.css" rel="stylesheet">
<style>
body {
	padding-top: 60px;
	/* 60px to make the container go all the way to the bottom of the topbar */
}
</style>
<link href="bootstrap/docs/assets/css/bootstrap-responsive.css"
	rel="stylesheet">

<!-- Stylesheet for rendering items -->
<link href="engine.css" rel="stylesheet">
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

<!-- Le fav and touch icons -->
<link rel="shortcut icon" href="bootstrap/docs/assets/ico/favicon.ico">
<link rel="apple-touch-icon-precomposed" sizes="144x144"
	href="bootstrap/docs/assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114"
	href="bootstrap/docs/assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72"
	href="bootstrap/docs/assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed"
	href="bootstrap/docs/assets/ico/apple-touch-icon-57-precomposed.png">
</head>

<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    MMLorHTML: { prefer: { Firefox: "MML" } }
  });
</script>
<script type="text/javascript"
  src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>

<?php echo $controller->getCSS(); ?>

<body>

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse"> <span class="icon-bar"></span> <span
					class="icon-bar"></span> <span class="icon-bar"></span>
				</a> <a class="brand" href="#">PHP-QTI</a>
				<div class="nav-collapse">
					<ul class="nav">
						<li class="active"><a href="index.php">Home</a></li>
						<li><a href="#about">About</a></li>
						<li><a href="#contact">Contact</a></li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</div>

	<div class="container">

		<header id="overview" class="jumbotron page-header">
			<h1>PHP-QTI</h1>
			<p class="lead">PHP-QTI is a PHP implementation of a QTI 2.1
				rendering engine.</p>
		</header>

		<div class="page-header">
			<h2><?php echo htmlspecialchars($controller->title) ?></h2>
		</div>

		<div class="row show-grid">
			<div class="span6">
				<?php $controller->run(); ?>
			</div>
		</div>

		<footer class="footer">
		
		</footer>

	</div>
	<!-- /container -->

	<!-- Le javascript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="bootstrap/docs/assets/js/jquery.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-transition.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-alert.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-modal.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-dropdown.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-scrollspy.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-tab.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-tooltip.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-popover.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-button.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-collapse.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-carousel.js"></script>
	<script src="bootstrap/docs/assets/js/bootstrap-typeahead.js"></script>

</body>
</html>
