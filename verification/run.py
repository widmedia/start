from selenium import webdriver
from functions import finish
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
# 1) loginLogout
if (not(doLoginLogout(driver))):
  finish(driver)

# 2) newUser
if (not(doNewUser(driver))):
  finish(driver)


print("OK. All tests have been executed successfully")
finish(driver)
