# does add a new user, login, delete the account again
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) open a new account           | id accountCreateOkSpan is present
# 2) login with this account      | page title is "Links"
# 3) goto edit                    | page title is "Einstellungen"
# 4) delete this account          | id accountDeleteOkMessageSpan is present
# 5) try to login again           | page title is not "Links"
def doNewUser(driver, testNum):
  from functions import doCreateNewAccount, checkSiteHasIdAndPrint, doLogin, checkSiteTitle, checkSiteTitleAndPrint, gotoEditPage, printOkOrNot

  modDescription = [(str(testNum)+".1"), "createNewAccount"]
  if (True): # if something is not working below while deleting the account, I still have it and don't want to create a new one  
    driver.get("https://widmedia.ch/start/index.php?do=2") # this page contains the newUser fields (page title is Startpage)  

    doCreateNewAccount(driver, username="test.email@widmedia.ch", password="correctPassword")
    if (not(checkSiteHasIdAndPrint(driver, modDescription, idToSearchFor="accountCreateOkSpan"))):
      return False
    # end if    
  # end if

  modDescription = [(str(testNum)+".2"), "loginWithCorrectPassword"]  
  driver.get("https://widmedia.ch/start/index.php") # back to the main page again
  doLogin(driver, username="test.email@widmedia.ch", password="correctPassword")
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Links"))):
    return False
  # end if

  gotoEditPage(driver, moduleTestNum=(str(testNum)+".3"))
  
  modDescription = [(str(testNum)+".4"), "deleteAccount"]
  driver.find_element_by_id("editPageDeleteLink").click()
  if (not(checkSiteHasIdAndPrint(driver, modDescription, idToSearchFor="accountDeleteOkMessageSpan"))):
    return False
  # end if
  
  modDescription = [(str(testNum)+".5"), "LoginOnNon-existingAccount"]    
  driver.get("https://widmedia.ch/start/index.php") # back to the main page again
  doLogin(driver, username="test.email@widmedia.ch", password="correctPassword") # this should fail now
  # cannot use checkSiteTitleAndPrint because the logic is the other way round
  if (checkSiteTitle(driver, "Links", outputOnFail=False)):
    printOkOrNot(ok=False, testNum=modDescription[0], text=modDescription[1])    
    return False
  # end if
  printOkOrNot(ok=True, testNum=modDescription[0], text=modDescription[1])

  return True
# end def
