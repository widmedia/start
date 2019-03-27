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
      background: none;
      color: inherit;
      border: none; 
      padding: 0;
      font: inherit;      
      border-bottom: 1px solid #444; 
      cursor: pointer;
    }
    .editLeft {
      margin: auto;
      display: inline-block;
      border: none; 
      padding: 0;      
    }
    .editRight { /* need to copy some properties from button-primary to have the 2 edits align vertically */      
      display: inline-block;
      border: none;
      padding-top: 20px;      
      font-size: 1.5rem;
      text-align: left;
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
      require_once('php/dbConnection.php'); // this will return the $dbConnection variable as 'new mysqli'
      if ($dbConnection->connect_error) { die('Connection failed: ' . $dbConnection->connect_error); }
      require_once('functions.php');
      
      // function to output several links in a formatted way
      // creating a div for every link and div-rows for every $module-th entry
      // TODO: merge this again with the printLinks function
      function printLinksToEdit($userid, $category, $dbConnection) {
        // TODO: change the ORDER BY. It should depend on the count (and maybe after that on the "sort" column, especially important after resetting all counts)
        $sql = 'SELECT * FROM `links` WHERE userid = '.$userid.' AND category = '.$category.' ORDER BY `links`.`sort` ASC LIMIT 100';
        
        // Have 12 columns. Means with modulo 3, I have "class four columns" and vice versa
        $modulo = 3;
        $divClass = '<div class="four columns linktext">';
        if ($category == 2) { // this category prints more dense
          $modulo = 4;
          $divClass = '<div class="three columns linktext">';      
        }
        
        // currently have 2cols for the link, 2cols for the edit/delete which are separated by a <br>.
        // most probably have to write a class on its own to do this nicer...?
        if ($result = $dbConnection->query($sql)) {
          $counter = 0;         
          while ($row = $result->fetch_assoc()) {            
            // link itself will point to "edit one link", additionally have two symbols
            // TODO: change the color of the link itself
            echo $divClass.'<span class="editLeft"><a href="editLinks.php?id='.$row['id'].'&do=4" class="button button-primary">'.$row['text'].'</a></span>
                 <span class="editRight">
                   <div><a href="editLinks.php?id='.$row['id'].'&do=4"><img src="images/edit.png"   width="16" height="16" border="0"> edit</a></div>
                   <div><a href="editLinks.php?id='.$row['id'].'&do=5"><img src="images/delete.png" width="16" height="16" border="0"> delete</a></div>
                 </span></div>';
            $counter++;

            if (($counter % $modulo) == 0) {
              echo '</div><div class="row">';
            }
          } // while    
          $result->close(); // free result set
        } // if  
      } // function 

      
      function printEntryPoint($userid, $dbConnection) {
        // TODO: this output needs a redesign. The buttons as links are not that nice...
        echo '<h2 class="section-heading">What would you like to edit?</h2><div class="row">';          
        for ($i = 1; $i <= 3; $i++) {
          echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
          <input name="categoryInput" type="hidden" value="'.$i.'">
          <input name="submit" type="submit" value="Category '.getCategory($userid, $i, $dbConnection).'"></form></div>';         
        }                
        echo '</div><div class="row"><div class="twelve columns"><hr /></div></div>';        
        // TODO: image looks quite shitty. Go without img? echo '<div class="row"><div class="six columns"><img width="60" height="30" src="images/linkCntRst.png"></div><div class="six columns">&nbsp;</div></div>'; 
        echo '<div class="row"><div class="six columns"><form action="editLinks.php?do=3" method="post"><input name="submit" type="submit" value="set all counters to 0"></form>
              </div><div class="six columns"><a class="button differentColor" href="#">(account management)</a></div></div></div> <!-- /container -->';
        printFooter('editLinks');
      } // function 

      
      
      $userid = 1;   // TODO: userid is fixed... 
      
      // TODO: the account management functionality
      // possible actions: 
      // 1=> present links of one category
      // 2=> add one link to db      
      // 3=> reset all cnt to 0
      // 4=> edit one link
      // 5=> delete one link
      
      // Form processing
      $doUnsafe       = substr($_GET['do'], 0, 1); // limit the length of this string to 1. Leaves me with enough values but no damage potential
      $categoryUnsafe = substr($_POST['categoryInput'], 0, 1); // this should either be an integer (when action is set) or non-existing
      $doSafe = 0;
      $categorySafe = 0;
      $dispErrorMsg = 0;
      $heading = ' '; // default value, in case some error happens

      if (filter_var($doUnsafe, FILTER_VALIDATE_INT)) { 
        $doSafe = $doUnsafe; 
        if (filter_var($categoryUnsafe, FILTER_VALIDATE_INT)) { 
          $categorySafe = $categoryUnsafe;
          $heading = getCategory($userid, $categorySafe, $dbConnection);                    
        } elseif (($doSafe == 1) or ($doSafe == 2)) { // I"m expecting a category only for dos 1 and 2
          $dispErrorMsg = 2; 
        } // have an integer on category
      } else { // entry point of this site   
        printEntryPoint($userid, $dbConnection);
        die(); // exit the php part
      }
      
      if ($doSafe > 0) {                
        // TODO: if-else-switch monster construct is kind of, well, a monster... and still growing
        switch ($doSafe) {
        case 1: // category selection thing        
            // I need fields to 
            // done: a) add a new link with 'link name' / 'link href' 
            // b) edit existing links: edit 'link name' / 'link href'. 
            // done: c) delete the whole link
            

            // b/c: TODO: this currently just prints the present state                        
            echo '<h3 class="section-heading">'.$heading.'</h3><div class="row">';
            // printLinks($userid, $categorySafe, $dbConnection); // TODO: merge the functions
            printLinksToEdit($userid, $categorySafe, $dbConnection); // this function is defined in the functions.php file
            echo '</div>';
            
            // this implements a) add a new link. TODO: could also serve as edit link? 
            echo '<form action="editLinks.php?do=2" method="post">
                    <div class="row"><div class="twelve columns"><h3 class="section-heading">New link</h3><input name="categoryInput" type="hidden" value="'.$categorySafe.'"></div></div>
                    <div class="row">
                      <div class="four columns"><input name="link" type="url"  maxlength="1023" value="https://" required></div>
                      <div class="four columns"><input name="text" type="text" maxlength="63"  value="text" required></div>
                      <div class="four columns"><input name="submit" type="submit" value="Add link"></div>
                    </div>
                  </form>';          
          break;  
        case 2: // have a valid do. 2 = add a link 
          if (filter_var($_POST['link'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { // have a validUrl. require the http(s)://-part as well. 
            // filtering it for sqli insertion
            $textSqlSafe = mysqli_real_escape_string($dbConnection, $_POST['text']);                               
            $linkSqlSafe = mysqli_real_escape_string($dbConnection, $_POST['link']);
          
            // NB: a statement like INSERT INTO `links` VALUES (.. ((SELECT MAX(sort) FROM `links` WHERE ..) + 1), ..)' is not allowed as the same table is used for insert and for data generation. 
            // Need to split into two operations
            $sqlGetMax = 'SELECT MAX(sort) FROM `links` WHERE `userid` = '.$userid.' AND `category` = '.$categorySafe;
            
            if ($result = $dbConnection->query($sqlGetMax)) {
              $row = $result->fetch_row(); // guaranteed to get only one row and one column
              $maxPlus1 = ($row[0]) + 1;              
                          
              $sqlInsert = 'INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$userid.'", "'.$categorySafe.'", "'.$maxPlus1.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'", "0")';
              if ($result = $dbConnection->query($sqlInsert)) {
                echo '<h3 class="section-heading">Link added</h3><div class="row">';
                echo '<div class="three columns linktext"><a href="'.htmlspecialchars($linkSqlSafe).'" target="_blank" class="button button-primary">'.htmlspecialchars($textSqlSafe).'</a><span class="counter">0</span></div>';
                echo '<div class="nine columns linktext">&nbsp</div>';
                echo '</div>';                   
              } else { $dispErrorMsg = 23; } // insert query did work
            } else { $dispErrorMsg = 22; } // getMax query did work
          } else { $dispErrorMsg = 21; } // have a validUrl -> TODO: add an additional error msg here because this really depends on user input
          break;
        case 3: // I want to reset all the link counters to 0
          $sqlCntReset = 'UPDATE `links` SET `cntTot` = 0 WHERE `userid` = '.$userid;
          if ($dbConnection->query($sqlCntReset)) { // should return true
            echo '<h3 class="section-heading">Counters have been reset to 0</h3><div class="row">';
            echo '<div class="six columns linktext"><a href="index.php" class="button button-primary">home</a></div>';
            echo '<div class="six columns linktext">&nbsp</div>';
            echo '</div>';                   
          } else { $dispErrorMsg = 31; } // insert query did work
          break;
        case 4: // edit one link. TODO
          echo '<h3 class="section-heading">Edit one link</h3><div class="row">';
          echo '<div class="nine columns linktext">Do = 4, editing one link</div>';
          echo '<div class="three columns linktext">&nbsp;</div></div>';                   
          break;
        case 5: // delete a link. Might want to display a confirmation message?
          $idSafe = 0;
          $idUnsafe = substr($_GET['id'], 0, 11); // this should be an integer (max 11 characters)
          if (filter_var($idUnsafe, FILTER_VALIDATE_INT)) { 
            $idSafe = $idUnsafe; 
            // need an additional userid condition. May be ignored by SQL because `id` is a primary key?
            $sqlPart = 'WHERE `userid` = '.$userid.' AND `id` = '.mysqli_real_escape_string($dbConnection, $idSafe);
            $sqlSelect = 'SELECT * FROM `links` '.$sqlPart;
            if($result = $dbConnection->query($sqlSelect)) { 
              $row = $result->fetch_assoc();
              $sqlDelete = 'DELETE FROM `links` '.$sqlPart; 
              if ($dbConnection->query($sqlDelete)) { // should return true
                echo '<h3 class="section-heading">Did delete one link</h3><div class="row">';
                echo '<div class="nine columns linktext">Deleted the '.htmlspecialchars($row['text']).'-link</div>';
                echo '<div class="three columns linktext">&nbsp;</div></div>';  
              } else { $dispErrorMsg = 53; } // delete sql did work out
            } else { $dispErrorMsg = 52; } // select sql did work out
          } else { $dispErrorMsg = 51; } // integer check did work out          
          break;
        default: 
          $dispErrorMsg = 1;
        } // switch
        if ($dispErrorMsg > 0) {
          echo '<h3 class="section-heading">Error</h3><div class="row">';
          echo '<div class="nine columns linktext">"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?</div>';
          echo '<div class="three columns linktext">&nbsp;</div></div>';          
          echo '</div> <!-- /container -->';
          printFooter('editLinks');
          die(); // finish the php part
        } // dispErrorMsg > 0        
        echo '</div> <!-- /container -->';
        printFooter('editLinks');
      } // action = integer          
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
