 <?php
  // This file contains functions to be included in other blocks and rely heavily on the context around them
  
  // function to output several links in a formatted way
  // creating a div for every link and div-rows for every $module-th entry
  function printLinks($modulo, $divClass, $sqlString, $dbConnection) {
    if ($result = $dbConnection->query($sqlString)) {
      $counter = 0;
         
      while ($row = $result->fetch_assoc()) {
        echo $divClass."<a href=\"link.php?id=".$row["id"]."\" target=\"_blank\" class=\"button button-primary\">".
             $row["text"]."</a><span class=\"counter\">".$row["cntTot"]."</span></div>\n";
        $counter++;

        if (($counter % $modulo) == 0) {
          echo "</div><div class=\"row\">";
        }
      } // while    
      $result->close(); // free result set
    } // if  
  } // function 

  // function returns the text of the category. If something does not work as expected, NULL is returned
  function getCategory($userid, $category, $dbConnection) {
    // Data base is organised as follows:
    // SELECT * FROM `titels`
    // id	userid	category	text
    // 1 	1 	    1 	      News
    // 2 	1 	    2 	      Work
    // 3 	1 	    3 	      Div
    $sqlString = "SELECT * FROM `titels` WHERE userid = ".$userid." AND category = ".$category." LIMIT 1";
    if ($result = $dbConnection->query($sqlString)) {
      $row = $result->fetch_assoc();
      $result->close(); // free result set
      
      return ($row["text"]);      
    } // if  
  } // function
  
  // function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input
  // TODO: the about page is not yet existing...
  function printFooter($currentSite) {
    $firstLink = "href=\"editLinks.php\">&nbsp;&nbsp;&nbsp;edit&nbsp;&nbsp;&nbsp;"; // default value
    if ($currentSite == "editLinks") {
        $firstLink = "href=\"index.php\">&nbsp;&nbsp;&nbsp;home&nbsp;&nbsp;&nbsp;";
    }
    echo "      
    <div class=\"section noBottom\">
      <div class=\"container\">
        <div class=\"row\">
          <div class=\"twelve columns\"><hr /></div>
        </div>
        <div class=\"row\">
          <div class=\"six columns\"><a class=\"button differentColor\" ".$firstLink."</a></div>
          <div class=\"six columns\"><a href=\"about.html\">about</a></div>
        </div>
      </div>
    </div>
    "; 
  } // function
  
  
  
?>                
