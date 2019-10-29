from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# returns a boolean
def checkSiteTitle (driver, expectedSiteTitle, outputOnFail=True):
  try:
    # we have to wait for the page to refresh, the last thing that seems to be updated is the title
    WebDriverWait(driver, 3).until(EC.title_contains(expectedSiteTitle))  # timeout in seconds
    return True
  except: # most probably the timeout exception. TODO: check on just the timeout exception
    if (outputOnFail):
      print("Site title not as expected: " + driver.title)
    return False
  # end try/except
# end def

def checkSiteTitleAndPrint (driver, modDescription, expectedSiteTitle):  
  if (not(checkSiteTitle(driver, expectedSiteTitle))):
    printOkOrNot(ok=False, testNum=modDescription[0], text=modDescription[1])
    return False
  # end if
  printOkOrNot(ok=True, testNum=modDescription[0], text=modDescription[1]) # we are now on the links page
  return True
# end def

#returns a boolean
def siteHasId(driver, idToSearchFor):
  try:    
    element = driver.find_element_by_id(idToSearchFor)
    return True
  except: # most probably the timeout exception. TODO: check on just the timeout exception
    import time
    print("the element with this ID has not been found: " + idToSearchFor)
    time.sleep(5) # not needed, to admire the page
    return False
  # end try/except
# end def

def checkSiteHasIdAndPrint(driver, modDescription, idToSearchFor):
  if (not(siteHasId(driver, idToSearchFor))):
    printOkOrNot(ok=False, testNum=modDescription[0], text=modDescription[1])
    return False
  # end if
  printOkOrNot(ok=True, testNum=modDescription[0], text=modDescription[1])
  return True
# end def

def gotoEditPage(driver, moduleTestNum):
  moduleText = "Going to edit page"
  driver.find_element_by_id("footerEditLink").click()
  if (not(checkSiteTitle(driver, "Einstellungen"))):
    printOkOrNot(ok=False, testNum=moduleTestNum, text=moduleText)
    return False
  # end if
  printOkOrNot(ok=True, testNum=moduleTestNum, text=moduleText) # we are now on the edit page
# end def

def doLogin (driver, username, password):
  # find the elements I want to control
  emailField    = driver.find_element_by_name("email")
  passwordField = driver.find_element_by_name("password")  

  # send the login details
  emailField.send_keys(username)
  passwordField.send_keys(password)  

  # and submit
  passwordField.submit() # could also use a command like: driver.find_element_by_name("login").click() 
# end def

def doLogout (driver):
  logoutLink = driver.find_element_by_id("footerLogoutLink")
  logoutLink.click()
# end def

# TODO: this is currently exactly the same as the doLogin function (just on another page)
def doCreateNewAccount(driver, username, password): 
  # find the elements I want to control
  emailField    = driver.find_element_by_name("email")
  passwordField = driver.find_element_by_name("password")  

  # send the login details
  emailField.send_keys(username)
  passwordField.send_keys(password)  

  # and submit
  passwordField.submit()  # could also use a command like: driver.find_element_by_name("login").click() 
# end def

def finish (driver):
  import sys  
  driver.quit() # close the browser window
  sys.exit()
# end def

def printOkOrNot(ok, testNum, text):
  successPre = "ERROR"
  successPost = " was not successful."  
  if(ok):
    successPre = "OK"
    successPost = " was successful."
  print(testNum + " " + successPre + " " + text + successPost)
# end def

# element = driver.find_element_by_id("element_id")
# element.text
# from selenium.webdriver.support.ui import Select
# select = Select(driver.find_element_by_tag_name("select"))
# select.deselect_all()
# select.select_by_visible_text("Edam")
