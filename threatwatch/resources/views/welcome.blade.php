<html>
	<head>
		<title>ThreatWatch</title>

		<link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

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

		<style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: 'Lato';
			}

			.container {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 96px;
				margin-bottom: 40px;
			}

			.quote {
				font-size: 24px;
			}
		</style>
		<script>
			$(function() {
				$("#search-form").submit(function(e) {
					doSearch();
					e.preventDefault();
				});
			});

			function doSearch() {
				window.location.href = "/" + $('#search-select').val() + "/" + $('#search-field').val();
			}
		</script>
	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="title">ThreatWatch</div>
				<div>
					<form id="search-form" class="form-inline">
						<div class="form-group">
							<select id="search-select" class="form-control">
								<option value="ip">IP</option>
								<option value="domain">Domain</option>
							</select>
						</div>
						<div class="form-group" style="width: 50%">
							<label class="sr-only" for="search-field">Password</label>
							<input type="text" class="form-control" id="search-field" placeholder="IP or Domain" style="width: 100%">
						</div>
						<button type="submit" class="btn btn-primary">Search</button>
					</form>
				</div>
				<div class="quote">/ip/{IP Address}</div>
				<div class="quote">/ip-details/{IP Address}</div>
				<div class="quote">/domain/{Domain Name}</div>
				<div class="quote">/config</div>
			</div>
		</div>
	</body>
</html>
