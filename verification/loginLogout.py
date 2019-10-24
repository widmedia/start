# does the login and logout
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) login with correct name/pw   | page title is Links
# 2) logout                       | page title is Startpage again
# 3) login with faulty name/pw    | page title is still Startpage (but with different site content)
def doLoginLogout(driver, testNum):
  from functions import printOkOrNot, doLogin, doLogout, checkSiteTitle
 
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
  moduleText = "Logout test"
  doLogout(driver)
  if (not(checkSiteTitle(driver, "Startpage"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText) # we are now on the start page again  

  moduleTestNum = str(testNum)+".3"
  moduleText = "Login test with wrong password"
  doLogin(driver, username="widmer@web-organizer.ch", password="wrongPassword")
  if (not(checkSiteTitle(driver, "Startpage"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText) # we are still on the start page (but only with error messages)  
  
  return True
# end def