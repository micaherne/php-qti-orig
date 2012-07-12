<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>PHP-QTI</title>
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
						<li class="active"><a href="#">Home</a></li>
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



		<div class="row show-grid">
			<div class="span4">
				<h2>Upload a QTI item</h2>
				<form class="well" action="upload.php" method="post" enctype="multipart/form-data">
					<label>QTI Item</label> <input name="qti-file" type="file" class="span3"
						placeholder="Choose your QTI item..."> <span class="help-block">QTI
						items can include:
						<ul>
							<li>Single item XML file</li>
							<li>Single item content package</li>
						</ul>
					</span>
					<button type="submit" class="btn">Submit</button>
				</form>
			</div>
			<div class="span4">
				<h2>View existing items</h2>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>File</th>
							<th>Uploaded by</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>choice.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=choice/choice">view</a></td>
						</tr>
						<tr>
							<td>choice_fixed.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=choice_fixed/choice_fixed">view</a></td>
						</tr>
						<tr>
							<td>choice_multiple.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=choice_multiple/choice_multiple">view</a></td>
						</tr>
						<tr>
							<td>choice_multiple_chocolade.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=choice_multiple_chocolade/choice_multiple_chocolade">view</a></td>
						</tr>
						<tr>
							<td>order.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=order/order">view</a></td>
						</tr>
						<tr>
							<td>order_partial_scoring.xml</td>
							<td>Michael Aherne</td>
							<td><a class="btn" href="view.php?item=order_partial_scoring/order_partial_scoring">view</a></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="span3 offset1">
				<h2>Download PHP-QTI</h2>
				<p>PHP-QTI is available as a PHP library which can be used in other 
				    applications</p>
				<a class="btn btn-primary btn-large"
					href="https://github.com/micaherne/">View project on GitHub</a>
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
