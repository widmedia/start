<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  function printEntryPoint($dbConnection, $userid) {
    echo '
    <h3 class="section-heading">What would you like to edit?</h3>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">';          
    for ($i = 1; $i <= 3; $i++) {
      echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
      <input name="categoryInput" type="hidden" value="'.$i.'">
      <input name="submit" type="submit" value="Category '.getCategory($dbConnection, $userid, $i).'"></form></div>';         
    }                
    echo '</div><div class="row"><div class="twelve columns">&nbsp;</div></div>';                    
    echo '
    <div class="row">
      <div class="six columns"><a class="button differentColor" href="editUser.php?do=1"><img src="images/db_green.png" class="logoImg"> account management</a></div>
      <div class="six columns"><a class="button differentColor" href="editLinks.php?do=3"><img src="images/zero_green.png" class="logoImg"> set all link counters to zero</a></div>
    </div>
    </div> <!-- /container -->';
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
  
  // returning a single row for the matching id. 
  // NB: id will get sql-escaped, userid not.
  function getSingleLinkRow ($id, $userid, $dbConnection) {
    // need an additional userid condition. May be ignored by SQL because `id` is a primary key?
    if($result = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.mysqli_real_escape_string($dbConnection, $id).'"')) {
      $row = $result->fetch_assoc();
      return $row;
    } else { return false; }
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
      // 1=> present links of one category + text field for category + add-Link option
      // 2=> add one link to db (of action 1)
      // 3=> reset all cnt to 0
      // 4=> edit one link
      // 5=> delete one link
      // 6=> do the update of one link (of action 4)
      // 7=> do the update of a category (of action 1)
      
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
          $heading = htmlspecialchars(getCategory($dbConnection, $userid, $categorySafe));
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
        printEntryPoint($dbConnection, $userid);
        die(); // exit the php part
      } elseif ($doSafe > 0) {
        // TODO: if-else-switch monster construct is kind of, well, a monster... and still growing
        if ($doSafe == 1) { // present links of one category, have category name as text field
          echo '<form action="editLinks.php?do=7" method="post"><input name="categoryInput" type="hidden" value="'.$categorySafe.'">
          <input name="text" type="text" maxlength="63" value="'.$heading.'" required> &nbsp;<input name="submit" type="submit" value="change category name"></form>
          <div class="row">';
          printLinks($dbConnection, true, $userid, $categorySafe);
          echo '</div>';          
          printSingleLinkFields($categorySafe, 2, 'Add', 0, 'https://', 'text');          
        } elseif ($doSafe == 2) { // add a link 
          if ($linkOk) { // have a validUrl
            if (testUserCheck($userid)) {                      
              if ($result = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`) VALUES ("'.$userid.'", "'.$categorySafe.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'")')) {
                redirectRelative('main.php?msg=5');
              } else { $dispErrorMsg = 22; } // insert query did work            
            } else { $dispErrorMsg = 21; } // testuser check
          } else { $dispErrorMsg = 20; printConfirm('Wrong URL', 'For the URL input, you need to have something in the format "http://somewebsite.ch" or "https://somewebsite.ch"'); } // have a validUrl
        } elseif ($doSafe == 3) { // I want to reset all the link counters to 0          
            if ($dbConnection->query('UPDATE `links` SET `cntTot` = "0" WHERE `userid` = "'.$userid.'"')) { // should return true
              redirectRelative('main.php?msg=4');            
            } else { $dispErrorMsg = 30; } // update query did work
        } elseif ($doSafe == 4) { // edit one link
          if ($idSafe > 0) {
            if ($singleRow = getSingleLinkRow($idSafe, $userid, $dbConnection)) {
              printSingleLinkFields(0, 6, 'Edit', $idSafe, $singleRow['link'], $singleRow['text']);
            } else { $dispErrorMsg = 61; }
          } else { $dispErrorMsg = 60; }
        } elseif ($doSafe == 5) { // delete a link. Displaying a confirmation message                 
          if ($idSafe > 0) {
            if (testUserCheck($userid)) {               
              if ($singleRow = getSingleLinkRow($idSafe, $userid, $dbConnection)) {
                if ($dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) { // should return true
                  redirectRelative('main.php?msg=3');               
                } else { $dispErrorMsg = 53; } // delete sql did work out
              } else { $dispErrorMsg = 52; } // select sql did work out
            } else { $dispErrorMsg = 51; } // testuser check
          } else { $dispErrorMsg = 50; } // integer check did work out          
        } elseif ($doSafe == 6) { // update a link
          if ($idSafe > 0) {            
            if (testUserCheck($userid)) {  
              if ($linkOk) {                                                                         
                $sql = 'UPDATE `links` SET `text` = "'.$textSqlSafe.'", `link` = "'.$linkSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'" LIMIT 1';
                if ($result = $dbConnection->query($sql)) {
                  redirectRelative('main.php?msg=1');
                } else { $dispErrorMsg = 63; } // update sql did work out            
              } else { $dispErrorMsg = 62; } // url check did work out
            } else { $dispErrorMsg = 61; } // testuser check           
          } else { $dispErrorMsg = 60; } // id check did work out            
        } elseif ($doSafe == 7) { // update a category name
          $textSqlSafe = mysqli_real_escape_string($dbConnection, $textUnsafe);
          if (testUserCheck($userid)) { 
            if ($categorySafe > 0) {            
              if ($result = $dbConnection->query('UPDATE `categories` SET `text` = "'.$textSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `category` = "'.$categorySafe .'" LIMIT 1')) {
                $textHtmlSafe = htmlspecialchars($textUnsafe);          
                redirectRelative('main.php?msg=2');
              } else { $dispErrorMsg = 72; } // update sql did work out                        
            } else { $dispErrorMsg = 71; } // category check did work out
          } else { $dispErrorMsg = 70; } // testuser check    
        } else {
          $dispErrorMsg = 1;
        } // switch
        
        printError($dispErrorMsg);
        echo '</div> <!-- /container -->';
        printFooter();
      } // action = integer          
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
