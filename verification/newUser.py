# does add a new user, login, delete the account again
# returns true if test is passing, false otherwise

# action
#-------------------------------
# 1) open a new account
# 2) login with this account
# 3) goto edit
# 4) delete this account
# 5) try to login again
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
