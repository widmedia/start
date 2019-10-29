# does the login and logout
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) login with correct name/pw   | page title is Links
# 2) logout                       | page title is Startpage again
# 3) login with faulty name/pw    | page title is still Startpage (but with different site content)
def doLoginLogout(driver, testNum):
  from functions import doLogin, doLogout, checkSiteTitleAndPrint
 
  driver.get("https://widmedia.ch/start") # go to the start page
  
  modDescription = [(str(testNum)+".1"), "loginWithCorrectPassword"]  
  doLogin(driver, username="widmer@web-organizer.ch", password="blabla") # this is the correct password
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Links"))):
    return False
  # end if

  modDescription = [(str(testNum)+".2"), "logout"]  
  doLogout(driver)
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Startpage"))):
    return False
  # end if
  
  modDescription = [(str(testNum)+".3"), "loginWithWrongPassword"]  
  doLogin(driver, username="widmer@web-organizer.ch", password="wrongPassword")
  # we are still on the start page (but only with error messages)
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Startpage"))):
    return False
  # end if
  
  return True
# end def