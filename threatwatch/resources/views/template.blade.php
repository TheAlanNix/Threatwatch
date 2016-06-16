<!DOCTYPE html>
<html lang="en">
	<head>
		<title>@yield('title')</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<meta name="author" content="Alan Nix" />
<?
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
	header('X-UA-Compatible: IE=edge');
?>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- Import Font from Google -->
		<link href="http://fonts.googleapis.com/css?family=Lato:300,600,700" rel="stylesheet" type="text/css" />

		<!-- Latest Bootstrap compiled and minified CSS -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

		<!-- Latest Font Awesome Icons -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">

		<!-- Latest compiled and minified jQuery/jQueryUI JavaScript -->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

		<!-- Latest compiled and minified Bootstrap JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

		@yield('script')

		<style>
			body {
				font-family: Lato;
				margin-bottom: 20px;
			}

			.box-shadow {
			    box-shadow: 0 4px 5px 0 rgba(0, 0, 0, .14), 0 1px 10px 0 rgba(0, 0, 0, .12), 0 2px 4px -1px rgba(0, 0, 0, .2)
			}

			.buffer {
				margin: 10px 0px;
				padding: 5px;
			}

			.center {
				text-align: center;
			}

			@yield('style')
		</style>

	</head>
	<body>

    	<div class="container">

	    	@yield('content')

    	</div>

	</body>
</html>
