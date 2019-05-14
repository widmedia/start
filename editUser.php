<?php
  require_once('functions.php');
  $dbConnection = initialize();

  
  function printErrorNoDo() { // TODO: whole function
    echo '<h2 class="section-heading">Error</h2><div class="row">';          
    echo '<div class="row"><div class="six columns">No valid action given</div><div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div></div></div> <!-- /container -->';
    printFooter();
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
      // TODO: the account management functionality
      
      // possible actions: 
      // 1=> add a new user
      // 2=> edit an existing user
      
      // Form processing
      $doSafe       = makeSafeInt($_GET['do'], 1);             // this is an integer (range 1 to 2)
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens

      // sanity checking. Check first if I have a valid 'do'
      if ($doSafe == 0) { // error, there is no valid thing to 'do'. Might occur because one can access this site directly
        printErrorNoDo();
        die(); // exit the php part
      } elseif ($doSafe > 0) {
        
        switch ($doSafe) {
        case 1: // add a new user but only if number of users is less than 10 (TODO: developer limitation for now)
          // TODO: some selection first? email? With or without pw? ... stuff like that
          // TODO: the real data base action
          printConfirmation('Add a new user', 'TODO: confirmation or error', 'six', 'six');          
          // $sqlInsert = 'INSERT INTO `links` (`id`, `userid`, `category`, `sort`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$userid.'", "'.$categorySafe.'", "'.$maxPlus1.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'", "0")';
          break;  
        case 2: // edit an existing user
          if ($userid) { // have a valid userid
            if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = '.$userid)) {
              // returns `id` and `name`
              $row = $result->fetch_assoc(); // guaranteed to get only one row              
              printConfirmation('Userid: '.$row['id'], 'username: '.$row['name'], 'nine', 'three');
            } else { $dispErrorMsg = 22; } // select query did work
          } else { $dispErrorMsg = 21; } // have a valid userid
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
