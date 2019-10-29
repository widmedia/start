# does login, add a link, check for it, delete it again logout
# returns true if test is passing, false otherwise

# action                          | test against
#------------------------------------------------------------------------------
# 1) login with correct name/pw   | page title is Links
# 2) goto edit page               | 
# 3) TODO 

def doEditLinks(driver, testNum):
  from functions import printOkOrNot, doLogin, doLogout, checkSiteTitleAndPrint, gotoEditPage, checkSiteHasIdAndPrint
  import time
 
  driver.get("https://widmedia.ch/start") # go to the start page
  
  modDescription = [(str(testNum)+".1"), "loginWithCorrectPassword"]
  doLogin(driver, username="widmer@web-organizer.ch", password="blabla") # this is the correct password  
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Links"))):
    return False
  # end if

  modDescription = [(str(testNum)+".2"), ""]  
  gotoEditPage(driver, moduleTestNum=modDescription[0])
  
  modDescription = [(str(testNum)+".3"), "clickCategory2Edit"]
  driver.find_element_by_id("editPageCategory_2_submit").click()
  if (not(checkSiteHasIdAndPrint(driver, modDescription, idToSearchFor="editPageHrAfterAddNewLink"))):
    return False
  # end if
  
  urlField  = driver.find_element_by_id("editPageAddNewLinkUrlInput")
  textField = driver.find_element_by_id("editPageAddNewLinkTextInput")
  submitField = driver.find_element_by_id("editPageAddNewLinkSubmit")  
  urlField.send_keys("https://someNonExistingPage.ch/widmedia/start/")
  textField.send_keys("myNewLink")  
  submitField.click()  
  
  modDescription = [(str(testNum)+".4"), "newLinkAdded"]
  # will be redirected to links page after successfully adding a link
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Links"))):
    return False
  # end if
  
  
  
  print('TODO: not yet finished')
  
  return True
# end def