<?php
  require_once('../functions.php');
  $dbConnection = initialize();
  
  
  // prints all the users (limit 100) in the database, sorted by id
  function printUserTable ($dbConnection) {   
    echo '<table><tr><th>id</th><th>email</th><th>lastLogin</th><th>hasPw</th><th>verified</th><th>verDate</th></tr>';
    if ($result = $dbConnection->query('SELECT `id`, `email`, `lastLogin`, `hasPw`, `verified`, `verDate` FROM `user` WHERE 1 ORDER BY `id` ASC LIMIT 100')) {
      while ($row = $result->fetch_assoc()) {        
        echo '<tr><td>'.$row['id'].'</td><td>'.$row['email'].'</td><td>'.$row['lastLogin'].'</td><td>'.$row['hasPw'].'</td><td>'.$row['verified'].'</td><td>'.$row['verDate'].'</td></tr>';
      } // while
    } // query ok
    echo '</table>';
  } // function
  
  
  
    
  echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin page</title>
  <meta name="author" content="Daniel Widmer">
  <meta name="robots" content="noindex, nofollow">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- CSS -->
  <link rel="stylesheet" href="../css/font.css" type="text/css">
  <link rel="stylesheet" href="../css/normalize.css" type="text/css">
  <link rel="stylesheet" href="../css/skeleton.css" type="text/css">

  <!-- Favicon -->  
  <link rel="icon" type="image/png" sizes="96x96" href="../images/favicon-96x96.png">
  </head>
  <body>';

  
  $userid = getUserid();
  if ($userid != 1) { // admin has the userid 1...
    die('sorry, only the admin may visit this site</body></html>');    
  }
    
  echo '<div class="section categories noBottom"><div class="container">';
  
  echo '<h3 class="section-heading">Accounts</h3>';
  echo '<div class="row">';
  printUserTable($dbConnection);
  echo '</div>
  </div> <!-- /container -->';
?>
  <div class="section noBottom">
    <div class="container">
      <div class="row twelve columns"><hr /></div>
      <div class="row twelve columns"><a class="button differentColor" href="../main.php"><img src="../images/home_green.png" class="logoImg"> back to main</a></div>
    </div>
  </div>                
  </div> <!-- /section categories -->
</body>
</html>
