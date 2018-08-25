 <?php
  require_once("php/dbConnection.php"); // this will return the $dbConnection variable

  // Check connection
  if ($dbConnection->connect_error) {
      die("Connection failed: " . $dbConnection->connect_error);
  }

  $errorString = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>Errorpage</title></head>
  <body>Something related to the data base went wrong... well, that doesn't help that much, does it?  But you might still want to inform me (sali@widmedia.ch) or just try again later</body></html>";

  // id as post / get?
  $idFromGet = htmlspecialchars($_GET["id"]);
  $idFiltered = 0;
  if (filter_var($idFromGet, FILTER_VALIDATE_INT)) {
    $idFiltered = $idFromGet;

    // important to verify the userid as well
    $sqlSelectString = "SELECT link FROM `links` WHERE userid = 1 AND id = ".$idFiltered." LIMIT 1";
    $sqlUpdateString = "UPDATE `links` SET cntTot = cntTot + 1 WHERE userid = 1 AND id = ".$idFiltered;

    if ($result = $dbConnection->query($sqlSelectString)) {
      $row = $result->fetch_assoc();

      if ($dbConnection->query($sqlUpdateString)) {
        // everything went as expected, no errors
        $result->close(); // free result set
        header("Location: ".$row["link"]);
        exit();
      } 
    } 
  } 
   
  // should never reach this code...
  echo $errorString; 	
?>