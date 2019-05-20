<?php
  require_once('functions.php');
  $dbConnection = initialize();

  
  function printErrorNoDo() { // TODO: whole function
    echo '<h2 class="section-heading">Error</h2><div class="row">';          
    echo '<div class="row"><div class="six columns">No valid action given</div><div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div></div></div> <!-- /container -->';
    printFooter();
  } // function

  function printUserEdit($row) {    
    echo '<h3 class="section-heading">Userid: '.$row['id'].'</h3>
          <form action="editUser.php?do=4" method="post">
          <div class="row">            
            <div class="six columns">email: <input name="email" type="email" maxlength="255" value="'.$row['email'].'" required size="20"></div>
            <div class="six columns">last login: '.$row['lastLogin'].'</div>
          </div>
          <div class="row">
            <div class="twelve columns"><hr /></div>
          </div>          
          <div class="row">
            <div class="six columns"><input name="submit" type="submit" value="save changes"></div>
            <div class="six columns">delete this account (without any further confirmation): <br/>
            <a href="editUser.php?do=3"><img src="images/delete.png" width="16" height="16" border="0"> delete</a></div>                        
          </div>
          </form>';
  } // function
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Add, edit or delete user accounts</title>
  <meta name="description" content="page to add, edit or delete user accounts">
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
      // 1=> add a new user
      // 2=> edit an existing user: present the form
      // 3=> delete an existing user: db operations
      // 4=> update an existing user: db operations. TODO: not yet implemented
      
      // Form processing
      $doSafe = makeSafeInt($_GET['do'], 1); // this is an integer (range 1 to 3)
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens

      // sanity checking. Check first if I have a valid 'do'
      if ($doSafe == 0) { // error, there is no valid thing to 'do'. Might occur because one can access this site directly
        printErrorNoDo();
        die(); // exit the php part
      } elseif ($doSafe > 0) {
        
        switch ($doSafe) {
        case 1:
          // TODO:
          // form with some basic information: email.
          // - some selection: with or without pw? ... stuff like that
          
          // user-dB structure:   `id` int(11), `email` text, `lastLogin` timestamp
          // titels-db structure: `id` int(11), `userid` int(11), `category` int(11), `text` text
          // links-db structure:  `id` int(11), `userid` int(11), `category` int(11), `sort` int(11), `text` text, `link` text, `cntTot` int(11)                    

          // Adding 1 user, 3 categories, 4 links
          // add a new user but only if number of users is less than 10 (TODO: developer limitation for now)
          if ($result = $dbConnection->query('SELECT count(*) AS `total` FROM `user`')) {
              $row = $result->fetch_assoc();
              $rowCnt = $row['total'];              
              if($rowCnt < 10) {
                $sqlInsertUser = 'INSERT INTO `user` (`id`, `email`, `lastLogin`) VALUES (NULL, "testUser@bla.ch", CURRENT_TIMESTAMP)';
                if ($result = $dbConnection->query($sqlInsertUser)) { 
                  $newUserId = $dbConnection->insert_id;
                  $dbConnection->query('INSERT INTO `titels` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "1", "News")');
                  $dbConnection->query('INSERT INTO `titels` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "2", "Work")');
                  $result = $dbConnection->query('INSERT INTO `titels` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "3", "Div")');
                  if ($result) { // assuming that if the last one did work, the other two did work as well
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "1", "1", "NZZ", "https://www.nzz.ch", "0")');
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "2", "1", "leo", "https://dict.leo.org", "0")');
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "2", "2", "gmail", "https://mail.google.com", "0")');
                    $result = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "3", "1", "WhatsApp", "https://web.whatsapp.com", "0")');
                    if ($result) { // assuming that if the last one did work, the other three did work as well
                      printConfirmation('Did add a new user', 'Userid: '.$newUserId.'. Login with this user: <a href="index.php?userid='.$newUserId.'">login</a>', 'six', 'six');
                    } else { $dispErrorMsg = 15; } // links insert
                  } else { $dispErrorMsg = 14; } // titels insert
                } else { $dispErrorMsg = 13; } // user insert
              } else { $dispErrorMsg = 12; } // have less than 10
          } else { $dispErrorMsg = 11; } // query to do the counting did not work          
          break;  
        case 2: // edit an existing user
          if ($userid) { // have a valid userid
            if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
              $row = $result->fetch_assoc(); // guaranteed to get only one row
              printUserEdit($row);              
            } else { $dispErrorMsg = 22; } // select query did work
          } else { $dispErrorMsg = 21; } // have a valid userid
          break;
        case 3: // update/delete an existing user
          if ($userid) { // have a valid userid
            if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {
              // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
              $rowCnt = $result->num_rows;
              if (($rowCnt == 1) and ($userid > 2)) {                
                $result_delLinks = $dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
                $result_delTitels = $dbConnection->query('DELETE FROM `titels` WHERE `userid` = "'.$userid.'"');
                $result_delUser = $dbConnection->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
                
                if ($result_delLinks and $result_delTitels and $result_delUser) {
                  sessionAndCookieDelete();
                  echo '<h2 class="section-heading">Deleting the account did work</h2>';
                  echo '<div class="row">
                          <div class="six columns">Deleted userid: '.$userid.'</div>
                          <div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div>
                       </div>';                  
                } else { $dispErrorMsg = 34; } // deleting did work                
              } else { $dispErrorMsg = 33; } // id does exists and its neither admin nor test user
            } else { $dispErrorMsg = 32; } // select query did work
          } else { $dispErrorMsg = 31; } // have a valid userid
          break;
        default: 
          $dispErrorMsg = 1;
        } // switch
        if ($dispErrorMsg > 0) {
          printConfirmation('Error', '"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?', 'nine', 'three');
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
