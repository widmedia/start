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
      
      function printEntryPoint($userid, $dbConnection) {
        // TODO: this output needs a redesign. The buttons as links are not that nice...
        echo '<h2 class="section-heading">What would you like to edit?</h2><div class="row">';          
        for ($i = 1; $i <= 3; $i++) {
          echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
          <input name="categoryInput" type="hidden" value="'.$i.'">
          <input name="submit" type="submit" value="Category '.getCategory($userid, $i, $dbConnection).'"></form></div>';         
        }                
        echo '</div><div class="row"><div class="twelve columns"><hr /></div></div>';                
        echo '<div class="row"><div class="six columns"><form action="editLinks.php?do=3" method="post"><input name="submit" type="submit" value="set all counters to 0"></form>
              </div><div class="six columns"><a class="button differentColor" href="#">(account management)</a></div></div></div> <!-- /container -->';
        printFooter('editLinks');
      } // function 
      
      // prints 2 rows to either add a new link or edit an existing one
      function printSingleLinkFields ($category, $do, $verb, $id, $link, $text) {
        // add a new link (link and text are 'text' fields in the db, must be smaller than 4 GB in total)
        echo '
        <form action="editLinks.php?do='.$do.'" method="post">
          <div class="row">
            <div class="twelve columns">
              <h3 class="section-heading">'.$verb.' link</h3><input name="categoryInput" type="hidden" value="'.$category.'"><input name="id" type="hidden" value="'.$id.'">
            </div>
          </div>
          <div class="row">
            <div class="four columns"><input name="link" type="url"  maxlength="1023" value="'.$link.'" required></div>
            <div class="four columns"><input name="text" type="text" maxlength="63"  value="'.$text.'" required></div>
            <div class="four columns"><input name="submit" type="submit" value="'.$verb.' link"></div>
          </div>
        </form>';   
      } // function
      
      //prints the h3 title and one row
      function printConfirmation($heading, $text, $leftSize, $rightSize) {
        echo '
        <h3 class="section-heading">'.$heading.'</h3>
        <div class="row">
          <div class="'.$leftSize.' columns linktext">'.$text.'</div>
          <div class="'.$rightSize.' columns linktext">&nbsp</div>
        </div>';                           
      }     
      
      // -------------------------------------------
      // 'real' code starts here...
      
      $userid = 1;   // TODO: userid is fixed... 
      // TODO: the account management functionality
      
      // possible actions: 
      // 1=> present links of one category
      // 2=> add one link to db      
      // 3=> reset all cnt to 0
      // 4=> edit one link
      // 5=> delete one link
      // 6=> edit one link
      
      // Form processing
      $doUnsafe       = substr($_GET['do'], 0, 1); // limit the length of this string to 1. Leaves me with enough values but no damage potential
      $categoryUnsafe = substr($_POST['categoryInput'], 0, 1); // this should either be an integer (range 1 to 3) or non-existing
      $idUnsafe       = substr($_GET['id'], 0, 11); // this should be an integer (max 11 characters)
      $linkUnsafe     = substr($_POST['link'], 0, 1023); //
      $textUnsafe     = substr($_POST['text'], 0, 63);      
      
      $doSafe = 0;
      $categorySafe = 0;
      $idSafe = 0;        
      $linkOk = false;  // the link/text requires an additional signal because it's not an integer
      $linkSqlSafe  = '';
      $linkHtmlSafe = '';
      $textSqlSafe  = '';
      $textHtmlSafe = '';
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens

      // sanity checking. Check first if I have a valid 'do'. If so, I check others (which may not always evaluate true even for valid use cases)           
      if (filter_var($doUnsafe, FILTER_VALIDATE_INT)) { 
        $doSafe = $doUnsafe; 
        if (filter_var($categoryUnsafe, FILTER_VALIDATE_INT)) { 
          $categorySafe = $categoryUnsafe;
          $heading = htmlspecialchars(getCategory($userid, $categorySafe, $dbConnection));
        } // have an integer on category
        if (filter_var($idUnsafe, FILTER_VALIDATE_INT)) { 
          $idSafe = $idUnsafe; 
        } // id
        if (filter_var($linkUnsafe, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { // have a validUrl. require the http(s)://-part as well.
          $linkOk = true;
          $linkSqlSafe = mysqli_real_escape_string($dbConnection, $linkUnsafe); // filtering it for sqli insertion
          $linkHtmlSafe = htmlspecialchars($linkUnsafe);
          // assuming that having a link always goes together with having a text. Cannot verify anything for the text itself (just cut it to 63 characters)
          $textSqlSafe = mysqli_real_escape_string($dbConnection, $textUnsafe);
          $textHtmlSafe = htmlspecialchars($textUnsafe);
        } // link
      } // do variable
      
      if ($doSafe == 0) { // entry point of this site   
        printEntryPoint($userid, $dbConnection);
        die(); // exit the php part
      } elseif ($doSafe > 0) {
        // TODO: if-else-switch monster construct is kind of, well, a monster... and still growing
        switch ($doSafe) {
        case 1: // present links of one category
          echo '<h3 class="section-heading">'.$heading.'</h3><div class="row">';
          printLinks(true, $userid, $categorySafe, $dbConnection); // this function is defined in the functions.php file
          echo '</div>';          
          printSingleLinkFields($categorySafe, 2, 'Add', 0, 'https://', 'text');          
          break;  
        case 2: // add a link 
          if ($linkOk) { // have a validUrl                      
            // NB: a statement like INSERT INTO `links` VALUES (.. ((SELECT MAX(sort) FROM `links` WHERE ..) + 1), ..)' is not allowed as the same table is used for insert and for data generation. 
            // Need to split into two operations            
            if ($result = $dbConnection->query('SELECT MAX(sort) FROM `links` WHERE `userid` = '.$userid.' AND `category` = '.$categorySafe)) {
              $row = $result->fetch_row(); // guaranteed to get only one row and one column
              $maxPlus1 = ($row[0]) + 1;              
                          
              $sqlInsert = 'INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$userid.'", "'.$categorySafe.'", "'.$maxPlus1.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'", "0")';
              if ($result = $dbConnection->query($sqlInsert)) {
                printConfirmation('Link added', '<a href="'.$linkHtmlSafe.'" target="_blank" class="button button-primary">'.$textHtmlSafe.'</a><span class="counter">0</span>', 'three', 'nine');
              } else { $dispErrorMsg = 23; } // insert query did work
            } else { $dispErrorMsg = 22; } // getMax query did work
          } else { $dispErrorMsg = 21; } // have a validUrl -> TODO: add an additional error msg here because this really depends on user input
          break;
        case 3: // I want to reset all the link counters to 0          
          if ($dbConnection->query('UPDATE `links` SET `cntTot` = 0 WHERE `userid` = '.$userid)) { // should return true
            printConfirmation('Counters have been reset to 0', '<a href="index.php" class="button button-primary">home</a>', 'six', 'six');            
          } else { $dispErrorMsg = 31; } // insert query did work
          break;
        case 4: // edit one link
          if ($idSafe > 0) {
            if ($singleRow = getSingleLinkRow($idSafe, $userid, $dbConnection)) {
              printSingleLinkFields(0, 6, 'Edit', $idSafe, $singleRow['link'], $singleRow['text']);
            } else { $dispErrorMsg = 62; }
          } else { $dispErrorMsg = 61; }
          break;
        case 5: // delete a link. Displaying a confirmation message                 
          if ($idSafe > 0) {             
            if ($singleRow = getSingleLinkRow($idSafe, $userid, $dbConnection)) {
              if ($dbConnection->query('DELETE FROM `links` WHERE `userid` = '.$userid.' AND `id` = '.$idSafe)) { // should return true
                printConfirmation('Did delete one link', 'Deleted the '.htmlspecialchars($singleRow['text']).'-link', 'nine', 'three');                
              } else { $dispErrorMsg = 53; } // delete sql did work out
            } else { $dispErrorMsg = 52; } // select sql did work out
          } else { $dispErrorMsg = 51; } // integer check did work out          
          break;
        case 6: // update a link
          if ($idSafe > 0) {            
            if ($linkOk) {                                                                         
              $sql = 'UPDATE `links` SET `text` = '.$textSqlSafe.', `link` = '.$linkSqlSafe.' WHERE `userid` = '.$userid.' AND `id` = '.$idSafe.' LIMIT 1';
              if ($result = $dbConnection->query($sql)) {
                printConfirmation('Link edited', '<a href="'.$linkHtmlSafe.'" target="_blank" class="button button-primary">'.$textHtmlSafe.'</a>', 'three', 'nine');
              } else { $dispErrorMsg = 63; } // update sql did work out            
            } else { $dispErrorMsg = 62; } // url check did work out          
          } else { $dispErrorMsg = 61; } // id check did work out            
          break;  
        default: 
          $dispErrorMsg = 1;
        } // switch
        if ($dispErrorMsg > 0) {
          printConfirmation('Error', '"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?', 'nine', 'three');
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
