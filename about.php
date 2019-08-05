<?php
  require_once('functions.php');
  $dbConn = initialize();
  
  printStartOfHtml($dbConn);  
  echo '
  <h3 class="section-heading">'.getLanguage($dbConn,1).'</h3>
  <div class="row">
    <div class="four columns u-max-full-width"><img src="images/myself.jpg" alt="'.getLanguage($dbConn,2).' Daniel Widmer" class="imgBorder" style="width:100%;"></div>
    <div class="eight columns textBox">
      <h4>'.getLanguage($dbConn,3).'</h4>
      <p>widmedia.ch/start '.getLanguage($dbConn,4).' <a href="https://github.com/widmedia/start">github</a> (open source)</p>
      <p>'.getLanguage($dbConn,5).': <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>
    </div>        
  </div>
  <div class="row"><div class="twelve columns"><hr /></div></div>
  <div class="row">
    <div class="twelve columns textBox">
      <h4>'.getLanguage($dbConn,6).'</h4>
      <p>'.getLanguage($dbConn,7).'</p>
      <p>'.getLanguage($dbConn,8).' <a href="https://github.com/widmedia/start">github '.getLanguage($dbConn,9).'.</a></p>
      <p>'.getLanguage($dbConn,10).'</p>
      <p>'.getLanguage($dbConn,11).' <br> 
      '.getLanguage($dbConn,12).' widmedia.ch '.getLanguage($dbConn,13).'</p>
      <p>widmedia '.getLanguage($dbConn,14).'</p>
    </div>        
  </div>
  <div class="row"><div class="twelve columns"><hr /></div></div>
  <div class="row">
    <div class="twelve columns textBox">
      <h4>'.getLanguage($dbConn,15).'</h4>
      <p>'.getLanguage($dbConn,16).'</p>          
    </div>        
  </div>
  <div class="row"><div class="twelve columns"><hr /></div></div>
  <div class="row">
    <div class="twelve columns textBox">
      <h4>'.getLanguage($dbConn,17).'</h4>
      <p>'.getLanguage($dbConn,18).' <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>          
    </div>        
  </div>';    
  printFooter($dbConn); 
?>
