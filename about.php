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
        <div class="four columns"><a href="#" class="button button-primary">TODO: some image</a></div>
        <div class="eight columns textBox">
          <h4>Contact</h4>
          <p>widmedia.ch/start is developed by Daniel Widmer. Please find the complete code at <a href="https://github.com/widmedia/start">github</a> (open source)</p>
          <p>Contact (German, English): <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>Data privacy</h4>
          <p>Be aware: without password protection for your account, all your user data are openly visible and may be edited. When using the password protection, widmedia tries to secure your data as good as possible, however, widmedia cannot guarantee full protection.</p>
          <p>Only data required for the functionality of this website is stored. The data base layout and structure as well as the underlying code is available for inspection on the open source <a href="https://github.com/widmedia/start">github project.</a></p>
          <p>The data will not be sold or transferred otherwise to any external party.</p>
          <p>On the other hand, widmedia cannot guarantee regular backups of your data, your data might be deleted or get lost in a different way. <br /> 
          Do not rely on <a href="https://widmedia.ch/start">widmedia.ch/start</a> as your only data source and do not store any sensitive information on this site.</p>
          <p>widmedia will not be held accountable for the material created, stored or available on this site, especially the links to external sites.</p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>External sources</h4>
          <p>No external sources are used.</p>          
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>Financing</h4>
          <p>...well, there is none. If you like to contribute, please contact me: <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>          
        </div>        
      </div>
    </div> <!-- /container -->
    <?php
    require_once('functions.php');
    printFooter(); 
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
