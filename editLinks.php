<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  function printEntryPoint($dbConnection, $userid) {
    echo '
    <h3 class="section-heading">'.getLanguage($dbConnection,35).'</h3>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">';          
    for ($i = 1; $i <= 3; $i++) {
      echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
      <input name="categoryInput" type="hidden" value="'.$i.'">
      <input name="submit" type="submit" value="'.getLanguage($dbConnection,36).getCategory($dbConnection, $userid, $i).'"></form></div>';         
    }                
    echo '</div><div class="row twelve columns">&nbsp;</div>';                    
    echo '
    <div class="row">
      <div class="six columns"><a class="button differentColor" href="editUser.php"><img src="images/icon_db.png" class="logoImg"> '.getLanguage($dbConnection,28).'</a></div>
      <div class="six columns"><a class="button differentColor" href="editLinks.php?do=3"><img src="images/icon_zero.png" class="logoImg"> '.getLanguage($dbConnection,37).'</a></div>
    </div>';    
  } // function 

  
  // prints 1 row to either add a new link or edit an existing one  
  function printSingleLinkFields ($dbConnection, $doAdd, $category, $linkId, $link, $text) {
    if ($doAdd) { // this means I edit a link
      $submitText = getLanguage($dbConnection,38);      
      $deleteText = '';
    } else {
      $submitText = getLanguage($dbConnection,39);
      $deleteText = '&nbsp;&nbsp;&nbsp;<a href="editLinks.php?id='.$linkId.'&do=4"><img src="images/icon_delete.png" class="logoImg"> '.getLanguage($dbConnection,40).'</a>';
    }
    echo '
    <form action="editLinks.php?do=2&id='.$linkId.'" method="post">      
      <div class="row">
        <div class="four columns"><input name="link" type="url"  maxlength="1023" value="'.$link.'" required></div>
        <div class="four columns"><input name="text" type="text" maxlength="63"  value="'.$text.'" required></div>
        <div class="four columns"><input name="categoryInput" type="hidden" value="'.$category.'"><input name="submit" type="submit" value="'.$submitText.'">'.$deleteText.'</div>
      </div>
    </form>';   
  } // function
  
  function printCategoryForm($dbConnection, $categorySafe, $heading) { 
    echo '<div class="row twelve columns">
    <form action="editLinks.php?do=5" method="post"><input name="categoryInput" type="hidden" value="'.$categorySafe.'">
    <input name="text" type="text" maxlength="63" value="'.$heading.'" required> &nbsp;<input name="submit" type="submit" value="'.getLanguage($dbConnection,41).'"></form><div>';
  }
  
  $userid = getUserid();      
  
  // possible actions: 
  // 1=> present links of one category + text field for category + add-Link option
  // 2=> add one link to db or edit one link (of action 1)
  // 3=> reset all cnt to 0
  // 4=> delete one link
  // 5=> do the update of a category (of action 1)
  
  // Form processing
  $doSafe       = makeSafeInt($_GET['do'], 1);             // this is an integer (range 1 to 5) or non-existing
  $categorySafe = makeSafeInt($_POST['categoryInput'], 1); // this is an integer (range 0 to 3) or non-existing
  $idSafe       = makeSafeInt($_GET['id'], 11);            // this is an integer (max 11 characters) or non-existing. The link id     
  
  // non-integer values are more complicated, text may be HTML-safe or sqli-safe
  $linkUnsafe = filter_var(substr($_POST['link'], 0, 1023), FILTER_SANITIZE_URL);  // this is an url (max 1023 characters) or non-existing
  $textUnsafe = filter_var(substr($_POST['text'], 0, 63), FILTER_SANITIZE_STRING); // this is a generic string (max 63 characters) or non-existing

  $linkOk = false;
  $linkSqlSafe  = '';
  $linkHtmlSafe = '';
  $textSqlSafe  = '';
  $textHtmlSafe = '';      
  $dispErrorMsg = 0;
  $heading = ''; // default value, stays empty if some error happens
  
  // sanity checking: I check get or post parameters (which may not always evaluate true even for valid use cases)
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

  if ($doSafe == 0) { // entry point of this site
    printStartOfHtml($dbConnection);
    printEntryPoint($dbConnection, $userid);        
  } elseif ($doSafe == 1) { // present links of one category, have category name as text field
    printStartOfHtml($dbConnection);
    printCategoryForm($dbConnection, $categorySafe, $heading);
    echo '<div class="row twelve columns"><h3 class="section-heading">'.getLanguage($dbConnection,42).'</h3></div>';          
    printSingleLinkFields($dbConnection, true, $categorySafe, 0, 'https://', 'text');
    printHr();
    // print one form per row, an edit form for every link
    if ($result = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `category` = "'.$categorySafe.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100')) {
      while ($row = $result->fetch_assoc()) {        
        printSingleLinkFields($dbConnection, false, 0, $row['id'], $row['link'], $row['text']); // category 0 means I'm editing an existing link
      } // while
    } // query ok
  } elseif ($doSafe == 2) { // add or edit a link
    // distinction between adding or editing is done by the category: category = 0 means I'm editing a link
    if ($linkOk) { // have a validUrl
      if (testUserCheck($dbConnection, $userid)) {
        if ($categorySafe == 0) { // means I update one link
          if ($idSafe > 0) { // this means I need a link id
            if ($result = $dbConnection->query('UPDATE `links` SET `text` = "'.$textSqlSafe.'", `link` = "'.$linkSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'" LIMIT 1')) {
              redirectRelative('links.php?msg=1');
            } else { $dispErrorMsg = 24; } // update sql did work out
          } else { $dispErrorMsg = 23; } // id check did work out
        } else { // I'm adding a new link
          if ($result = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`) VALUES ("'.$userid.'", "'.$categorySafe.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'")')) {
            redirectRelative('links.php?msg=5');
          } else { $dispErrorMsg = 22; } // insert query did work
        } // distinction between adding and editing
      } // testuser check
    } else { $dispErrorMsg = 20; printConfirm($dbConnection, getLanguage($dbConnection,43), getLanguage($dbConnection,44)); } // have a validUrl
  } elseif ($doSafe == 3) { // I want to reset all the link counters to 0          
    if ($dbConnection->query('UPDATE `links` SET `cntTot` = "0" WHERE `userid` = "'.$userid.'"')) { // should return true
      redirectRelative('links.php?msg=4');            
    } else { $dispErrorMsg = 30; } // update query did work        
  } elseif ($doSafe == 4) { // delete a link. Displaying a confirmation message                 
    if ($idSafe > 0) {
      if (testUserCheck($dbConnection, $userid)) {
        if($result = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) {
          if ($result->num_rows == 1) {
            if ($dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) { // should return true
              redirectRelative('links.php?msg=3');
            } else { $dispErrorMsg = 44; } // delete sql did work out
          } else { $dispErrorMsg = 43; } // did get one result
        } else { $dispErrorMsg = 42; } // select sql did work out
      } // testuser check
    } else { $dispErrorMsg = 40; } // integer check did work out          
  } elseif ($doSafe == 5) { // update a category name
    $textSqlSafe = mysqli_real_escape_string($dbConnection, $textUnsafe);
    if (testUserCheck($dbConnection, $userid)) { 
      if ($categorySafe > 0) {            
        if ($result = $dbConnection->query('UPDATE `categories` SET `text` = "'.$textSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `category` = "'.$categorySafe .'" LIMIT 1')) {
          $textHtmlSafe = htmlspecialchars($textUnsafe);          
          redirectRelative('links.php?msg=2');
        } else { $dispErrorMsg = 52; } // update sql did work out                        
      } else { $dispErrorMsg = 51; } // category check did work out
    } // testuser check    
  } else {
    $dispErrorMsg = 1;
  } // switch              
  printError($dbConnection, $dispErrorMsg);        
  echo '</div> <!-- /container -->';
  printFooter($dbConnection);
?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
