from functions import *

# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
driver.set_window_size(500, 700) # about mobile size, portrait style

# go to the start page
driver.get("https://widmedia.ch/start/index.php?do=2") # this page contains the newUser fields
# driver.get("https://widmedia.ch/start/index.php")

print (driver.title) # initial page title (with the login fields)

# want to verify following procedure
# action                     | test against
#------------------------------------------------------------------------------
# 1) open a new account      | id accountCreateOkSpan is present
# 2) login with this account | page title is "Links"
# 3) goto edit               | ?
# 4) delete this account     | (delete ok message)
# 5) try to login again      | (login fails)

doCreateNewAccount(driver, username="test.email@widmedia.ch", password="correctPassword")
WebDriverWait(driver, 5)  # should not be required
time.sleep(1) # should not be required

## does not work. Why? No idea...

if (not(siteHasId(driver, idToSearchFor="accountCreateOkSpan"))):
# if (not(siteHasId(driver, idToSearchFor="footerAboutLink"))):
  print("ERROR. doCreateNewAccount was not successful")
  # finish(driver)
# end if
print("OK. doCreateNewAccount was successful")

time.sleep(2) # not needed, to admire the page
# finish(driver)
