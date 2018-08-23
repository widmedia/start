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

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="section categories">
    <div class="container">
     <?php
      //                  ...those vary              ...  ... those are equal for all 3 cases                   ...
      function printLinks($modulo, $divClass, $sqlString, $dbConnection, $href, $hrefEnd, $linkEndSpanStart, $endDiv) {
        if ($result = $dbConnection->query($sqlString)) {
          $counter = 0;
          while ($row = $result->fetch_assoc()) {
            echo $divClass.$href.$row["link"].$hrefEnd.$row["text"].$linkEndSpanStart.$row["cntTot"].$endDiv;
            $counter++;

            if (($counter % $modulo) == 0) {
              echo "</div><div class=\"row\">";
            }
          } // while    
          $result->close(); // free result set
        } // if  
      } // function  
      // get the data out from the data base
      $sqlStringCat1 = "SELECT * FROM `links` WHERE userid = 1 AND category = 1 ORDER BY `links`.`sort` ASC LIMIT 1000"; // <ToDo> change userid to be variable 
      $sqlStringCat2 = "SELECT * FROM `links` WHERE userid = 1 AND category = 2 ORDER BY `links`.`sort` ASC LIMIT 1000";
      $sqlStringCat3 = "SELECT * FROM `links` WHERE userid = 1 AND category = 3 ORDER BY `links`.`sort` ASC LIMIT 1000";
      
      $divClass4Columns = "<div class=\"four columns linktext\">";
      $divClass3Columns = "<div class=\"three columns linktext\">";
      $href             = "<a href=\"";
      $hrefEnd          = "\" target=\"_blank\" class=\"button button-primary\">";
      $linkEndSpanStart = "</a><span class=\"counter\">";
      $endDiv           = "</span></div>";
      
      echo "<h3 class=\"section-heading\">News</h3><div class=\"row\">";
      printLinks(3, $divClass4Columns, $sqlStringCat1, $dbConnection,$href,$hrefEnd,$linkEndSpanStart,$endDiv);
      
      echo "</div><h3 class=\"section-heading\">Work</h3><div class=\"row\">";
      printLinks(4, $divClass3Columns, $sqlStringCat2, $dbConnection,$href,$hrefEnd,$linkEndSpanStart,$endDiv);
      
      echo "</div><h3 class=\"section-heading\">Div</h3><div class=\"row\">";
      printLinks(3, $divClass4Columns, $sqlStringCat3, $dbConnection,$href,$hrefEnd,$linkEndSpanStart,$endDiv);
    ?>                
      </div>
    </div> <!-- /container -->
    
    <div class="section get-help">
      <div class="container">
	<div class="row">
          <div class="twelve columns"><hl></div>
	</div>
	<div class="row">
          <div class="six columns"><a href="linkEdit.html">edit</a></div>
          <div class="six columns"><a href="about.html">about</a></div>
	</div>
      </div>
    </div>
  </div> <!-- /section categories -->

<!-- End Document -->
</body>
</html>
