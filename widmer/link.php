 <?php
  // this file does the redirection and increases the link counter by one
  require_once("php/dbConnection.php"); // this will return the $dbConnection variable

  // Check connection
  if ($dbConnection->connect_error) {
      die("Connection failed: " . $dbConnection->connect_error);
  }

  // id as 'get' parameter
  $idFromGet = htmlspecialchars($_GET["id"]);
  $idFiltered = 0;
  if (filter_var($idFromGet, FILTER_VALIDATE_INT)) {
    $idFiltered = $idFromGet;

    // TODO: add another sqlspecialchars or similar. Just to make sure
    
    // important to verify the userid as well
    $sqlSelectString = "SELECT link FROM `links` WHERE userid = 1 AND id = ".$idFiltered." LIMIT 2"; // should always return just one
    $sqlUpdateString = "UPDATE `links` SET cntTot = cntTot + 1 WHERE userid = 1 AND id = ".$idFiltered;

    if ($result = $dbConnection->query($sqlSelectString)) {
      if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if ($dbConnection->query($sqlUpdateString)) {
          // everything went as expected, no errors
          $result->close(); // free result set
          header("Location: ".$row["link"]);
          exit();
        } 
      }
    } 
  } 
   
  // should never reach this code...
  echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>Errorpage</title><link rel=\"stylesheet\" href=\"css/skeleton.css\" type=\"text/css\"></head>
  <body><br />Something related to the data base went wrong (in file link.php)... well, that doesn't help that much, does it?<br />But you might still want to inform me (sali@widmedia.ch) or just try again later</body></html>";

?>