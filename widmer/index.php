 <?php
    require_once("php/dbConnection.php"); // this will return the $dbConnection variable

    // Check connection
    if ($dbConnection->connect_error) {
        die("Connection failed: " . $dbConnection->connect_error);
    }
?> 


<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Startpage</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT -->
  <link href='//fonts.googleapis.com/css?family=Raleway:400,300,600' rel='stylesheet' type='text/css'>

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->

  <div class="section categories">
    <div class="container">
      <h3 class="section-heading">News</h3>
      <div class="row">
        <div class="four columns category linktext"><a href="http://www.watson.ch" target="_blank" class="button button-primary">watson</a></div>
        <div class="four columns category linktext"><a href="https://www.heise.de" target="_blank" class="button button-primary">heise</a></div>
        <div class="four columns category linktext"><a href="http://www.tagesanzeiger.ch" target="_blank" class="button button-primary">tagi</a></div>
      </div> <!-- /row -->
      <h3 class="section-heading">Work</h3>
      <div class="row">        
        <div class="three columns linktext"><a href="https://mail.google.com/" target="_blank" class="button button-primary">gmail</a></div>
        <div class="three columns linktext"><a href="https://varian.okta.com/" target="_blank" class="button button-primary">varian okta</a></div>
        <div class="three columns linktext"><a href="http://dict.leo.org/" target="_blank" class="button button-primary">dict</a></div>
        <div class="three columns linktext"><a href="https://direct.credit-suisse.com/" target="_blank" class="button button-primary">cs</a></div>        
      </div>
      <div class="row">        
        <div class="three columns linktext"><a href="https://www.metanet.ch/" target="_blank" class="button button-primary">metanet</a></div>        
      </div>
      <h3 class="section-heading">Div</h3>
      <div class="row">
        <div class="four columns category linktext"><a href="http://www.9gag.com" target="_blank" class="button button-primary">9 gag</a></div>
        <div class="four columns category linktext"><a href="https://web.whatsapp.com/" target="_blank" class="button button-primary">WA</a></div>
        <div class="four columns category linktext"><a href="https://www.sports-tracker.com/login" target="_blank" class="button button-primary">sports-tracker</a></div>
      </div>
    </div> <!-- /container -->
    
    <div class="section get-help">
      <div class="container">
	<div class="row">
          <div class="twelve columns category"><hl></div>
	</div>
	<div class="row">
          <div class="six columns category"><a href="linkEdit.html">edit</a></div>
          <div class="six columns category"><a href="about.html">about</a></div>
	</div>
      </div>
    </div>
  </div> <!-- /section categories -->



<!-- End Document -->
</body>
</html>
