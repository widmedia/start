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
  <!-- some site specific code (might be moved to custom.css later) -->
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
      require_once("php/dbConnection.php"); // this will return the $dbConnection variable as "new mysqli"
      if ($dbConnection->connect_error) { die("Connection failed: " . $dbConnection->connect_error); }
      require_once("functions.php");
            
      $userid = 1;   // TODO: userid is fixed...      
      // TODO the numbering of the 'action' is not logical (2 comes before 1). Could also work with enums/list     
      
      // Form processing
      $actionFromPost = htmlspecialchars($_POST["action"]); // this should be either an integer or not set.
      $categoryFromPost = htmlspecialchars($_POST["categoryInput"]); // this should be an integer (when action is set)
      $actionSafe = 0;
      $categorySafe = 0;
      $dispErrorMsg = 0;
      $heading = " "; // default value, in case some error happens
      
      if (filter_var($actionFromPost, FILTER_VALIDATE_INT)) {
        $actionSafe = $actionFromPost;        
        
        if (filter_var($categoryFromPost, FILTER_VALIDATE_INT)) {
          $categorySafe = $categoryFromPost;          
          $heading = getCategory($userid,$categoryFromPost,$dbConnection);                    
        } elseif (($actionSafe == 2) or ($actionSafe == 1)) { // I'm expecting a category only for actions 1 and 2
          $dispErrorMsg = 5; 
        } // have an integer on category
        
        // TODO: if-else-switch monster construct is kind of, well, a monster...
       
        switch ($actionSafe) {
        case 2: // category selection thing        
            // I need fields to 
            // done: a) add a new link with "link name" / "link href" 
            // b) edit/delete existing links: edit "link name" / "link href". Delete the whole link

            // b: TODO: this currently just prints the present state                        
            $divClass3Columns = "<div class=\"three columns linktext\">";  // using the smallest size (for four links in one row)
            $sqlString = "SELECT * FROM `links` WHERE userid = ".$userid." AND category = ".$categorySafe." ORDER BY `links`.`sort` ASC LIMIT 100"; // more than 100 links do not make sense
            
            echo "<h3 class=\"section-heading\">".$heading."</h3><div class=\"row\">";
            printLinks(4, $divClass3Columns, $sqlString, $dbConnection); // this function is defined in the functions.php file
            echo "</div>";
            
            // this implements a) add a new link. TODO: could also serve as edit link? 
            echo "<form action=\"editLinks.php\" method=\"post\">
                    <div class=\"row\"><div class=\"twelve columns\"><h3 class=\"section-heading\">New link</h3><input name=\"action\" type=\"hidden\" value=\"1\"><input name=\"categoryInput\" type=\"hidden\" value=\"".$categorySafe."\"></div></div>
                    <div class=\"row\">
                      <div class=\"four columns\"><input name=\"link\" type=\"url\"  maxlength=\"1023\" value=\"https://\" required></div>
                      <div class=\"four columns\"><input name=\"text\" type=\"text\" maxlength=\"255\"  value=\"text\" required></div>
                      <div class=\"four columns\"><input name=\"submit\" type=\"submit\" value=\"Add link\"></div>
                    </div>
                  </form>";          
          break;  
        case 1: // have a valid action. 1 = add a link 
          if (filter_var($_POST["link"], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { // have a validUrl. require the http(s)://-part as well. 
            // filtering it for both HTML display and sqli insertion
            $textSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["text"]));                               
            $linkSafe = htmlspecialchars(mysqli_real_escape_string($dbConnection, $_POST["link"]));
          
            // NB: statement below is not allowed as the same table is used for insert and for data generation. Need to split into two operations...
            // $sql = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES 
            // (NULL, '1', '2', ((SELECT MAX(sort) FROM `links` WHERE `userid` = 1 AND `category` = 2) + 1), '".$textSafe"', '".$linkSafe."', '0')";
            $sqlGetMax = "SELECT MAX(sort) FROM `links` WHERE `userid` = ".$userid." AND `category` = ".$categorySafe;
            
            if ($result = $dbConnection->query($sqlGetMax)) {
              $row = $result->fetch_row(); // guaranteed to get only one row and one column
              $maxPlus1 = ($row[0]) + 1;              
                          
              $sqlInsert = "INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, '".$userid."', '".$categorySafe."', '".$maxPlus1."', '".$textSafe."', '".$linkSafe."', '0')";
              if ($result = $dbConnection->query($sqlInsert)) {
                echo "<h3 class=\"section-heading\">Link added</h3><div class=\"row\">\n";
                echo "<div class=\"three columns linktext\"><a href=\"".$linkSafe."\" target=\"_blank\" class=\"button button-primary\">".$textSafe."</a><span class=\"counter\">0</span></div>\n";
                echo "<div class=\"nine columns linktext\">&nbsp</div>\n";
                echo "</div>";                   
              } else { $dispErrorMsg = 4; } // insert query did work
            } else { $dispErrorMsg = 3; } // getMax query did work
          } else { $dispErrorMsg = 2; } // have a validUrl -> TODO: add an additional error msg here because this really depends on user input
          break;
        case 3: // I want to reset all the link counters to 0
          $sqlCntReset = "UPDATE `links` SET `cntTot` = 0 WHERE `userid` = ".$userid;
          if ($dbConnection->query($sqlCntReset)) { // should return true
            echo "<h3 class=\"section-heading\">Counters have been reset to 0</h3><div class=\"row\">\n";
            echo "<div class=\"six columns linktext\"><a href=\"index.php\" class=\"button button-primary\">home</a></div>\n";
            echo "<div class=\"six columns linktext\">&nbsp</div>\n";
            echo "</div>";                   
          } else { $dispErrorMsg = 6; } // insert query did work
          break;
        default: 
          $dispErrorMsg = 1;
        } // switch
        if ($dispErrorMsg > 0) {
          echo "<h3 class=\"section-heading\">Error</h3><div class=\"row\">";
          echo "<div class=\"nine columns linktext\">'Something' at step ".$dispErrorMsg." went wrong when processing user input data (very helpful error message, I know...). Might try again?</div>\n";
          echo "<div class=\"three columns linktext\">&nbsp</div>\n</div>";                   
          exit(); // finish the php part
        } // dispErrorMsg > 0        
      } else { // form processing: do not have a valid integer. When entering the page, there is no $actionFromPost set... Most probably it's not a fault but just the entry point
        echo "<h2 class=\"section-heading\">What would you like to edit?</h2><div class=\"row\">";          
        for ($i = 1; $i <= 3; $i++) {
          echo "<div class=\"four columns\"><form action=\"editLinks.php\" method=\"post\">
          <input name=\"action\" type=\"hidden\" value=\"2\"><input name=\"categoryInput\" type=\"hidden\" value=\"".$i."\">
          <input name=\"submit\" type=\"submit\" value=\"Category ".getCategory($userid, $i, $dbConnection)."\"></form></div>";         
        }        
        echo "</div>\n"; // row
        echo "<div class=\"row\"><div class=\"twelve columns\"><hr /></div></div>\n";
        // TODO: might need an image in counter reset to make it more clear..?
        // images/linkCnt3.png -> images/linkCnt0.png. Img is: width_114 x height_64
        // TODO: the account management functionality
        echo "<div class=\"row\"><div class=\"six columns\">
                <form action=\"editLinks.php\" method=\"post\"><input name=\"action\" type=\"hidden\" value=\"3\"><input name=\"submit\" type=\"submit\" value=\"set all counters to 0\"></form>
              </div>
              <div class=\"six columns\"><a href=\"#\">(account management)</a></div></div>\n";        
      } // action = integer          
    
    echo "</div> <!-- /container -->\n";
    printFooter("editLinks");
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
