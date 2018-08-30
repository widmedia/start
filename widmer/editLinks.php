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
  <title>Edit my links</title>
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
     <?php      
      function printLinks($modulo, $divClass, $sqlString, $dbConnection) {
        if ($result = $dbConnection->query($sqlString)) {
          $counter = 0;
          
          while ($row = $result->fetch_assoc()) {
            echo $divClass."<a href=\"link.php?id=".$row["id"]."\" target=\"_blank\" class=\"button button-primary\">".
                 $row["text"]."</a><span class=\"counter\">".$row["cntTot"]."</span></div>\n";
            $counter++;

            if (($counter % $modulo) == 0) {
              echo "</div><div class=\"row\">";
            }
          } // while    
          $result->close(); // free result set
        } // if  
      } // function  
      // get the data out from the data base
      
      
      $sqlStringCat2 = "SELECT * FROM `links` WHERE userid = 1 AND category = 2 ORDER BY `links`.`sort` ASC LIMIT 1000";

      $divClass3Columns = "<div class=\"three columns linktext\">";
      $href             = "<a href=\"";
      $endHref          =  "\" target=\"_blank\" class=\"button button-primary\">";
      $endDiv           = "</a></div>";
      
      echo "<h3 class=\"section-heading\">Work</h3><div class=\"row\">";
      printLinks(4, $divClass3Columns, $sqlStringCat2, $dbConnection);
      echo "</div>";
      
      
      // code above prints the current state. Now I need to have the edit fields:
      // - link name
      // - link href
      // - link ordering: add +/- buttons  --> this submits instantly. Needs to swap values (is more robust than +/- 1, works with gaps as well and basically same operation)
      
      
    ?>                
    </div> <!-- /container -->
    
      <!-- ToDo: code below should be included. Somehow...  -->
    <div class="section noBottom">
      <div class="container">
        <div class="row">
          <div class="twelve columns"><hr /></div>
        </div>
        <div class="row">
          <div class="six columns"><a href="editLinks.php">edit</a></div>
          <div class="six columns"><a href="about.html">about</a></div>
        </div>
      </div>
    </div>
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
