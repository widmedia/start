# does login, add a link, check for it, delete it again logout
# returns true if test is passing, false otherwise

# action
#-------------------------------
# 1) login with correct name/pw
# 2) goto edit page
# 3) click on category 2
# 4) add some link -> are on links page
# 5) go back to edit page
# 6) click on category 2
# 7) delete the link again
# 8) logout

def doEditLinks(driver, testNum):
  from functions import doLogin, doLogout, checkSiteTitleAndPrint, gotoEditPage, checkSiteHasIdAndPrint 
 
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
  
  # back to edit page
  modDescription = [(str(testNum)+".5"), ""]  
  gotoEditPage(driver, moduleTestNum=modDescription[0])
  
  modDescription = [(str(testNum)+".6"), "clickCategory2Edit"]
  driver.find_element_by_id("editPageCategory_2_submit").click()
  if (not(checkSiteHasIdAndPrint(driver, modDescription, idToSearchFor="editPageHrAfterAddNewLink"))):
    return False
  # end if
  
  ## now delete the link again
  # need to find the first link which looks like this: <a href="edit.php?id=8&amp;do=4"><img "bla"> löschen</a>
  driver.find_element_by_link_text("löschen").click()
  modDescription = [(str(testNum)+".7"), "linkDeletedAgain"]
  # will be redirected to links page after successfully adding a link
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Links"))):
    return False
  # end if
  
  modDescription = [(str(testNum)+".8"), "logout"]  
  doLogout(driver)
  if (not(checkSiteTitleAndPrint(driver, modDescription, expectedSiteTitle="Startpage"))):
    return False
  # end if
  
  return True
# end def