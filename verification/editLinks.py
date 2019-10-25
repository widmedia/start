# does login, add a link, check for it, delete it again logout
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) login with correct name/pw   | page title is Links
# 2) goto edit page               | 
# 3) TODO 

def doEditLinks(driver, testNum):
  from functions import printOkOrNot, doLogin, doLogout, checkSiteTitle, gotoEditPage
 
  driver.get("https://widmedia.ch/start") # go to the start page
  
  moduleTestNum = str(testNum)+".1"
  moduleText = "Login test with correct password"
  doLogin(driver, username="widmer@web-organizer.ch", password="blabla") # this is the correct password
  if (not(checkSiteTitle(driver, "Links"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText) # we are now on the links page

  moduleTestNum = str(testNum)+".2"
  gotoEditPage(driver, moduleTestNum)
  
  print('TODO: not yet finished')
  
  return True
# end def