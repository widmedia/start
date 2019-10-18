import time
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# returns a boolean
def checkSiteTitle (driver, expectedSiteTitle):
  try:
    # we have to wait for the page to refresh, the last thing that seems to be updated is the title
    WebDriverWait(driver, 5).until(EC.title_contains(expectedSiteTitle))  # timeout in seconds    
    # print("Site title as expected: " + driver.title)
    return True
  except: # most probably the timeout exception. TODO: check on just the timeout exception
    print("Site title not as expected: " + driver.title)
    return False
  # end try/except
# end def

#returns a boolean
def siteHasId(driver, idToSearchFor):
  try:    
    element = driver.find_element_by_id(idToSearchFor)
    return True
  except: # most probably the timeout exception. TODO: check on just the timeout exception
    print("the element with this ID has not been found: " + idToSearchFor)
    time.sleep(5) # not needed, to admire the page
    return False
  # end try/except
# end def

def doLogin (driver, username, password):
  # find the elements I want to control
  emailField    = driver.find_element_by_name("email")
  passwordField = driver.find_element_by_name("password")  

  # send the login details
  emailField.send_keys(username)
  passwordField.send_keys(password)  

  # and submit
  passwordField.submit()  # could also use a command like: driver.find_element_by_name("login").click() 
# end def

def doLogout (driver):
  logoutLink = driver.find_element_by_id("footerLogoutLink")
  logoutLink.click()
# end def

# TODO: this is currently exactly the same as the doLogin function
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
  print("...exiting")
  driver.quit() # close the browser window
  sys.exit()
# end def


# element = driver.find_element_by_id("element_id")
# element.text
# from selenium.webdriver.support.ui import Select
# select = Select(driver.find_element_by_tag_name("select"))
# select.deselect_all()
# select.select_by_visible_text("Edam")
# time.sleep(2) # not needed, to admire the page
