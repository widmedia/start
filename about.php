<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>About</title>
  <meta name="description" content="a modifiable page containing various links, intended to be used as a personal start page">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT -->
  <link rel="stylesheet" href="css/font.css" type="text/css">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">
  <link rel="stylesheet" href="css/custom.css" type="text/css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">

</head>
<body>
  <div class="section categories noBottom">
    <div class="container">
      <h3 class="section-heading">About</h3>
      <div class="row">
        <div class="four columns linktext"><a href="#" class="button button-primary">TODO: some image</a></div>
        <div class="eight columns linktext" style="text-align: left;">
          <p>widmedia.ch/start is developed by Daniel Widmer. Please find the complete code at <a href="https://github.com/widmedia/start">github</a> (open source)</p>
          <p>Contact (German, English): <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>
        </div>        
      </div>  
    </div> <!-- /container -->
    <?php
    require_once('functions.php');
    printFooter('about'); 
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
