<?
require_once ('config.php');
$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
?>
<? ?>

<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta charset="utf-8" />
    <title>имена</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src = "http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <form class="navbar-form navbar-right">
            <div class="form-group">
              <input type="text" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>


    <div class="container">
      <br><br><br>
      <!-- Example row of columns -->
      <div class="col-md-12 column">
			<table class="table table-bordered text-center" id="tab_logic">
				<thead>
					<tr >
						<th class="text-center">
							#
						</th>
						<th class="text-center">
							ID
						</th>
						<th class="text-center">
							Name
						</th>
            <th class="text-center">
							prefix
						</th>
						<th class="text-center">
							enabled
						</th>
						<th class="text-center">
							##
						</th>
					</tr>
				</thead>
				<tbody>
					<tr id='dev_0'>
				</tbody>
			</table>
		</div>


      <hr>

      <footer>
        <p>&copy; 2016 Company, Inc.</p>
      </footer>
    </div> <!-- /container -->

 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
 <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
</body>

</html>
