from selenium import webdriver
from functions import finish, printOkOrNot
from loginLogout import doLoginLogout
from newUser import doNewUser

# main file to start other tests
#------------------------------------------------------------------------------
# available tests:
# 1) loginLogout
# 2) newUser

# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
driver.set_window_size(500, 700) # about mobile size, portrait style


# default mode: starting all tests, one after the other
testNum = 1 # loginLogout
if (not(doLoginLogout(driver, testNum))):
  finish(driver)

testNum = 2 # newUser
if (not(doNewUser(driver, testNum))):
  finish(driver)

printOkOrNot(ok=True, testNum="0.0    ", text="All tests have been executed successfully")
finish(driver)
