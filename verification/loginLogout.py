# does the login and logout
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) login with correct name/pw   | page title is Links
# 2) logout                       | page title is Startpage again
# 3) login with faulty name/pw    | page title is still Startpage (but with different site content)
def doLoginLogout(driver):
  from functions import doLogin, doLogout, checkSiteTitle
 
  driver.get("https://widmedia.ch/start") # go to the start page

  doLogin(driver, username="widmer@web-organizer.ch", password="blabla") # this is the correct password
  if (not(checkSiteTitle(driver, "Links"))):
    print("ERROR. Login test with correct password not successful")
    return False
  # end if
  print("OK. Login test with correct password successful") # we are now on the links page

  doLogout(driver)
  if (not(checkSiteTitle(driver, "Startpage"))):
    print("ERROR. Logout test not successful")
    return False
  # end if
  print("OK. Logout test successful") # we are now on the start page again

  doLogin(driver, username="widmer@web-organizer.ch", password="wrongPassword")
  if (not(checkSiteTitle(driver, "Startpage"))):
    print("ERROR. Login test with wrong password not successful")
    return False
  # end if
  print("OK. Login test with wrong password successful") # we are still on the start page (but only with error messages)
  
  return True
# end def