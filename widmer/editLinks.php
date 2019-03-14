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
  <style>
    button.link {
      background:none;
      color:inherit;
      border:none; 
      padding:0;
      font: inherit;
      /*border is optional*/
      border-bottom:1px solid #444; 
      cursor: pointer;
    }
</style>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">

</head>
<body>
  <!-- Primary Page Layout -->
  <div class="section categories">
    <div class="container">
     <?php     
      require_once("php/dbConnection.php"); // this will return the $dbConnection variable

      // Check connection
      if ($dbConnection->connect_error) { die("Connection failed: " . $dbConnection->connect_error); }      

      $category = 2; // TODO: category is fixed...
      $userid = 1;   // TODO: userid is fixed...
      
      // Form processing
      $actionFromPost = htmlspecialchars($_POST["action"]); // this should be either an integer or not set.
      $actionFiltered = 0;
      $dispErrorMsg = 0;      
      if (filter_var($actionFromPost, FILTER_VALIDATE_INT)) {
        $actionFiltered = $actionFromPost;
        if($actionFiltered == 1) {  // have a valid action (is a post variable)
          if (filter_var($_POST["link"], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { // have a validUrl. require the http(s)://-part as well. 
            // filtering it for both HTML display and sqli insertion
            $textSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["text"]));                               
            $linkSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["link"]));
            
            // NB: statement below is not allowed as the same table is used for insert and for data generation. Need to split into two operations...
            // $sql = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES 
            // (NULL, '1', '2', ((SELECT MAX(sort) FROM `links` WHERE `userid` = 1 AND `category` = 2) + 1), '".$textSafe"', '".$linkSafe."', '0')";
            $sqlGetMax = "SELECT MAX(sort) FROM `links` WHERE `userid` = ".$userid." AND `category` = ".$category;
            
            if ($result = $dbConnection->query($sqlGetMax)) {
              $row = $result->fetch_row(); // guaranteed to get only one row and one column
              $maxPlus1 = ($row[0]) + 1;              
                          
              $sqlInsert = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, '".$userid."', '".$category."', '".$maxPlus1."', '".$textSafe."', '".$linkSafe."', '0')";
              if ($result = $dbConnection->query($sqlInsert)) {
                echo "<h3 class=\"section-heading\">Link added</h3><div class=\"row\">\n";
                echo "<div class=\"three columns linktext\"><a href=\"".$linkSafe."\" target=\"_blank\" class=\"button button-primary\">".$textSafe."</a><span class=\"counter\">0</span></div>\n";
                echo "<div class=\"nine columns linktext\">&nbsp</div>\n";
                echo "</div>";                   
              } else { $dispErrorMsg = 4;} // insert query did work
            } else {$dispErrorMsg = 3;} // getMax query did work
          } else { $dispErrorMsg = 2;} // have a validUrl -> TODO: add an additional error msg here because this really depends on user input
        } else { $dispErrorMsg = 1;} // have a valid action, would like to add a link  
        
        if ($dispErrorMsg > 0) {
          echo "<h3 class=\"section-heading\">Error</h3><div class=\"row\">";
          echo "<div class=\"nine columns linktext\">'Something' at step ".$dispErrorMsg." went wrong when processing user input data (very helpful error message, I know...). Might try again?</div>\n";
          echo "<div class=\"three columns linktext\">&nbsp</div>\n</div>";                   
          exit(); // finish the php part
        }
      } else { // form processing: have an integer. When entering the page, there is no $actionFromPost set... It's not a fault
        echo "<h2 class=\"section-heading\">What would you like to edit?</h2><div class=\"row\">";
        
        // --- working here --- //     
        echo "<div class=\"four columns\"><form action=\"editLinks.php\" method=\"post\"><h3 class=\"section-heading\"><input name=\"action\" type=\"hidden\" value=\"2\"><button class=\"link\" name=\"submit\" type=\"submit\" value=\"Category News\"></h3></form></div>";
        echo "<div class=\"four columns\"><h3 class=\"section-heading\">Category Work</h3></div>";
        echo "<div class=\"four columns\"><h3 class=\"section-heading\">Category Div</h3></div></div>";
      
        echo "<div class=\"row\"><div class=\"twelve columns\"><hr /></div></div>";
        echo "<div class=\"row\"><div class=\"six columns\"><a href=\"#\">set all counters to 0</a></div><div class=\"six columns\"><a href=\"#\">(account management)</a></div></div>";
      }

      require_once("functions.php");
      
      $sqlString = "SELECT * FROM `links` WHERE userid = ".$userid." AND category = ".$category." ORDER BY `links`.`sort` ASC LIMIT 100"; // more than 100 links do not make sense

      $divClass3Columns = "<div class=\"three columns linktext\">";
      $href             = "<a href=\"";
      $endHref          =  "\" target=\"_blank\" class=\"button button-primary\">";
      $endDiv           = "</a></div>";
      
      echo "<h3 class=\"section-heading\">Work</h3><div class=\"row\">";
      printLinks(4, $divClass3Columns, $sqlString, $dbConnection); // this function is defined in the functions.php file
      echo "</div>";
      
      
      // code above prints the current state. Now I need fields to 
      // a) add a new link with "link name" / "link href" 
      // b) edit/delete existing links: edit "link name" / "link href". Delete the whole link
      // c) Change link ordering: add +/- buttons  --> this submits instantly. Needs to swap values (is more robust than +/- 1, works with gaps as well and basically same operation)
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
