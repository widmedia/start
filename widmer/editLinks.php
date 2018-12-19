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

      // Form processing
      $actionFromPost = htmlspecialchars($_POST["action"]); // this should be an integer
      $actionFiltered = 0;
      $dispErrorMsg = false;
      if (filter_var($actionFromPost, FILTER_VALIDATE_INT)) {
        $actionFiltered = $actionFromPost;
        if($actionFiltered == 1) {  // have a valid action (is a post variable)
          if (filter_var($_POST["link"], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) { // have a validUrl
            // filtering it for both HTML display and sqli insertion
            $textSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["text"]));                               
            $linkSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["link"]));
          
            // TODO: actually execute the data base insertion of a new entry.
            
            echo "<h3 class=\"section-heading\">Link added</h3><div class=\"row\">";
            echo "<div class=\"three columns linktext\"><a href=\"".$linkSafe."\" target=\"_blank\" class=\"button button-primary\">".
            $textSafe."</a><span class=\"counter\">0</span></div>\n";
            echo "<div class=\"nine columns linktext\">&nbsp</div>\n";
            echo "</div>";   
          } else { $dispErrorMsg = true; } // have a validUrl
        } else { $dispErrorMsg = true; } // have a valid action, would like to add a link              
      } else { $dispErrorMsg = true; } // form processing: have an integer

      if ($dispErrorMsg) {
        echo "<h3 class=\"section-heading\">Error</h3><div class=\"row\">";
        echo "<div class=\"three columns linktext\">Something went wrong when processing user input data. Might try again?</div>\n";
        echo "<div class=\"nine columns linktext\">&nbsp</div>\n</div>";           
        exit(); // finish the php part
      }

     
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
    <div class="container">
      <form action="editLinks.php" method="post">
      <h3 class="section-heading">New link<input name="action" type="hidden" value="1"></h3>
      <div class="row">          
          <div class="four columns"><input name="link" type="url"  maxlength="1023" value="https://" required></div>
          <div class="four columns"><input name="text" type="text" maxlength="255"  value="text" required></div>
          <div class="four columns"><input name="submit" type="submit" value="Add link"></div>
      </div>
      </form>
    </div>
    
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
