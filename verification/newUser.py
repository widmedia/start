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
  from functions import doCreateNewAccount, siteHasId, doLogin, checkSiteTitle, printOkOrNot, gotoEditPage

  moduleTestNum = str(testNum)+".1"
  moduleText = "doCreateNewAccount"
  if (True): # if something is not working below while deleting the account, I still have it and don't want to create a new one  
    driver.get("https://widmedia.ch/start/index.php?do=2") # this page contains the newUser fields (page title is Startpage)  

    doCreateNewAccount(driver, username="test.email@widmedia.ch", password="correctPassword")  
    if (not(siteHasId(driver, idToSearchFor="accountCreateOkSpan"))):
      printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
      return False
    # end if
    printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText)
  # end if

  moduleTestNum = str(testNum)+".2"
  moduleText = "Login test with correct password"
  driver.get("https://widmedia.ch/start/index.php") # back to the main page again
  doLogin(driver, username="test.email@widmedia.ch", password="correctPassword")
  if (not(checkSiteTitle(driver, "Links"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText) # we are now on the links page

  moduleTestNum = str(testNum)+".3"
  gotoEditPage(driver, moduleTestNum)

  moduleTestNum = str(testNum)+".4"
  moduleText = "deleteAccount"
  driver.find_element_by_id("editPageDeleteLink").click()
  if (not(siteHasId(driver, idToSearchFor="accountDeleteOkMessageSpan"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText)

  moduleTestNum = str(testNum)+".5"
  moduleText = "Login test on non-existing account"
  driver.get("https://widmedia.ch/start/index.php") # back to the main page again
  doLogin(driver, username="test.email@widmedia.ch", password="correctPassword") # this should fail now
  if (checkSiteTitle(driver, "Links", outputOnFail=False)):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)    
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText)

  return True
# end def
