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
  <meta name="description" content="page to add or edit links">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT -->
  <link rel="stylesheet" href="css/font.css" type="text/css">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">

</head>
<body>

  <!-- Primary Page Layout -->
  <div class="section categories">
    <div class="container">
     <?php     
      $category = 2;  // TODO: category is fixed... (and userid)
      
      // Form processing
      $actionFromPost = htmlspecialchars($_POST["action"]); // this should be either an integer or not set.
      $actionFiltered = 0;
      $dispErrorMsg = 0;      
      if (filter_var($actionFromPost, FILTER_VALIDATE_INT)) {
        $actionFiltered = $actionFromPost;
        if($actionFiltered == 1) {  // have a valid action (is a post variable)
          if (filter_var($_POST["link"], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { // have a validUrl
            // filtering it for both HTML display and sqli insertion
            $textSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["text"]));                               
            $linkSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["link"]));
            
            // NB: statement below is not allowed as the same table is used for insert and for data generation. Need to split into two operations...
            // $sql = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES 
            // (NULL, '1', '2', ((SELECT MAX(sort) FROM `links` WHERE `userid` = 1 AND `category` = 2) + 1), '".$textSafe"', '".$linkSafe."', '0')";
            $sqlGetMax = "SELECT MAX(sort) FROM `links` WHERE `userid` = 1 AND `category` = ".$category;
            
            if ($result = $dbConnection->query($sqlGetMax)) {
              $row = $result->fetch_row(); // guaranteed to get only one row and one column
              $maxPlus1 = ($row[0]) + 1;              
                          
              $sqlInsert = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, '1', '".$category."', '".$maxPlus1."', '".$textSafe."', '".$linkSafe."', '0')";
              if ($result = $dbConnection->query($sqlInsert)) {
                echo "<h3 class=\"section-heading\">Link added</h3><div class=\"row\">\n";
                echo "<div class=\"three columns linktext\"><a href=\"".$linkSafe."\" target=\"_blank\" class=\"button button-primary\">".$textSafe."</a><span class=\"counter\">0</span></div>\n";
                echo "<div class=\"nine columns linktext\">&nbsp</div>\n";
                echo "</div>";                   
              } else { $dispErrorMsg = 4;} // insert query did work
            } else {$dispErrorMsg = 3;} // getMax query did work
          } else { $dispErrorMsg = 2;} // have a validUrl
        } else { $dispErrorMsg = 1;} // have a valid action, would like to add a link  
      } // form processing: have an integer. When entering the page, there is not action set, 

      if ($dispErrorMsg > 0) {
        echo "<h3 class=\"section-heading\">Error</h3><div class=\"row\">";
        echo "<div class=\"nine columns linktext\">'Something' at step ".$dispErrorMsg." went wrong when processing user input data (very helpful error message, I know...). Might try again?</div>\n";
        echo "<div class=\"three columns linktext\">&nbsp</div>\n</div>";                   
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
      
      
      $sqlString = "SELECT * FROM `links` WHERE userid = 1 AND category = ".$category." ORDER BY `links`.`sort` ASC LIMIT 1000";

      $divClass3Columns = "<div class=\"three columns linktext\">";
      $href             = "<a href=\"";
      $endHref          =  "\" target=\"_blank\" class=\"button button-primary\">";
      $endDiv           = "</a></div>";
      
      echo "<h3 class=\"section-heading\">Work</h3><div class=\"row\">";
      printLinks(4, $divClass3Columns, $sqlString, $dbConnection);
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
