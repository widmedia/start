<?php
  require_once('functions.php');
  $dbConnection = initialize();

  
  function printEntryPoint($userid, $dbConnection) {
    echo '
    <h3 class="section-heading">What would you like to edit?</h3>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">';          
    for ($i = 1; $i <= 3; $i++) {
      echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
      <input name="categoryInput" type="hidden" value="'.$i.'">
      <input name="submit" type="submit" value="Category '.getCategory($userid, $i, $dbConnection).'"></form></div>';         
    }                
    echo '</div><div class="row"><div class="twelve columns">&nbsp;</div></div>';                    
    echo '<div class="row">
    <div class="six columns"><a class="button differentColor" href="editLinks.php?do=3"><img src="images/zero_green.png" class="logoImg"> set all link counters to zero</a></div>
    <div class="six columns"><a class="button differentColor" href="editUser.php?do=2"><img src="images/db_green.png" class="logoImg"> account management</a></div>
    </div></div> <!-- /container -->';
    printFooter();
  } // function 

  
  // prints 2 rows to either add a new link or edit an existing one
  function printSingleLinkFields ($category, $do, $verb, $id, $link, $text) {
    // add a new link (link and text are 'text' fields in the db)
    echo '
    <form action="editLinks.php?do='.$do.'&id='.$id.'" method="post">
      <div class="row">
        <div class="twelve columns">
          <h3 class="section-heading">'.$verb.' link</h3><input name="categoryInput" type="hidden" value="'.$category.'">
        </div>
      </div>
      <div class="row">
        <div class="four columns"><input name="link" type="url"  maxlength="1023" value="'.$link.'" required></div>
        <div class="four columns"><input name="text" type="text" maxlength="63"  value="'.$text.'" required></div>
        <div class="four columns"><input name="submit" type="submit" value="'.$verb.' link"></div>
      </div>
    </form>';   
  } // function
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
      $userid = getUserid();      
      
      // possible actions: 
      // 1=> present links of one category
      // 2=> add one link to db      
      // 3=> reset all cnt to 0
      // 4=> edit one link
      // 5=> delete one link
      // 6=> do the update of one link (of action 4)
      // 7=> TODO: change the name of a category
      
      // Form processing
      $doSafe       = makeSafeInt($_GET['do'], 1);             // this is an integer (range 1 to 6) or non-existing
      $categorySafe = makeSafeInt($_POST['categoryInput'], 1); // this is an integer (range 1 to 3) or non-existing
      $idSafe       = makeSafeInt($_GET['id'], 11);            // this is an integer (max 11 characters) or non-existing
      
      // non-integer values are more complicated, text may be HTML-safe or sqli-safe
      $linkUnsafe = filter_var(substr($_POST['link'], 0, 1023), FILTER_SANITIZE_URL);  // this is an url (max 1023 characters) or non-existing
      $textUnsafe = filter_var(substr($_POST['text'], 0, 63), FILTER_SANITIZE_STRING); // this is a generic string (max 63 characters) or non-existing

      $linkOk = false;  // the link/text requires an additional signal because it's not an integer
      $linkSqlSafe  = '';
      $linkHtmlSafe = '';
      $textSqlSafe  = '';
      $textHtmlSafe = '';
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens

      // sanity checking. Check first if I have a valid 'do'. If so, I check others (which may not always evaluate true even for valid use cases)           
      if ($doSafe) {         
        if ($categorySafe) {           
          $heading = htmlspecialchars(getCategory($userid, $categorySafe, $dbConnection));
        } // have an integer on category
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
            $sqlInsert = 'INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$userid.'", "'.$categorySafe.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'", "0")';
            if ($result = $dbConnection->query($sqlInsert)) {
              printConfirmation('Link added', '<a href="'.$linkHtmlSafe.'" target="_blank" class="button button-primary">'.$textHtmlSafe.'</a><span class="counter">0</span>', 'three', 'nine');
            } else { $dispErrorMsg = 22; } // insert query did work            
          } else { $dispErrorMsg = 21; } // have a validUrl. Some additional error info is printed when this one happens because it depends on a user input
          break;
        case 3: // I want to reset all the link counters to 0          
          if ($dbConnection->query('UPDATE `links` SET `cntTot` = "0" WHERE `userid` = "'.$userid.'"')) { // should return true
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
              if ($dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) { // should return true
                printConfirmation('Did delete one link', 'Deleted the '.htmlspecialchars($singleRow['text']).'-link', 'nine', 'three');                
              } else { $dispErrorMsg = 53; } // delete sql did work out
            } else { $dispErrorMsg = 52; } // select sql did work out
          } else { $dispErrorMsg = 51; } // integer check did work out          
          break;
        case 6: // update a link
          if ($idSafe > 0) {            
            if ($linkOk) {                                                                         
              $sql = 'UPDATE `links` SET `text` = "'.$textSqlSafe.'", `link` = "'.$linkSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'" LIMIT 1';
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
          if ($dispErrorMsg == 21) { // validUrl-check did not work out
            printConfirmation('Wrong URL', 'For the URL input, you need to have something in the format "http://somewebsite.ch" or "https://somewebsite.ch"', 'nine', 'three');
          }
          echo '</div> <!-- /container -->';
          printFooter();
          die(); // finish the php part
        } // dispErrorMsg > 0        
        echo '</div> <!-- /container -->';
        printFooter();
      } // action = integer          
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
