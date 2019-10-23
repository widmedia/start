from functions import *

# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
driver.set_window_size(500, 700) # about mobile size, portrait style

# want to verify following procedure
# action                     | test against
#------------------------------------------------------------------------------
# 1) open a new account      | id accountCreateOkSpan is present
# 2) login with this account | page title is "Links"
# 3) goto edit               | page title is "Einstellungen"
# 4) delete this account     | id accountDeleteOkMessageSpan is present
# TODO: 5) try to login again      | (login fails)

if (True):
  # go to the new user page (page title is Startpage)
  driver.get("https://widmedia.ch/start/index.php?do=2") # this page contains the newUser fields
  print (driver.title)

  doCreateNewAccount(driver, username="test.email@widmedia.ch", password="correctPassword")
  ## TODO: don't know whether those 2 waits below are really necessary
  WebDriverWait(driver, 5) 
  time.sleep(1) 

  if (not(siteHasId(driver, idToSearchFor="accountCreateOkSpan"))):
    print("ERROR. doCreateNewAccount was not successful")
    finish(driver)
  # end if
  print("OK. doCreateNewAccount was successful")

  time.sleep(2) # not needed, to admire the page
# end if

driver.get("https://widmedia.ch/start/index.php") # 
doLogin(driver, username="test.email@widmedia.ch", password="correctPassword")
if (not(checkSiteTitle(driver, "Links"))):
  print("ERROR. Login test with correct password not successful")
  finish(driver)
# end if
print("OK. Login test with correct password successful") # we are now on the links page

footerEditLink = driver.find_element_by_id("footerEditLink")
footerEditLink.click()
if (not(checkSiteTitle(driver, "Einstellungen"))):
  print("ERROR. Going to edit page was not successful")
  finish(driver)
# end if
print("OK. Going to edit page test successful") # we are now on the edit page
# print (driver.title)


editPageDeleteLink = driver.find_element_by_id("editPageDeleteLink")
editPageDeleteLink.click()

## TODO: don't know whether those 2 waits below are really necessary
WebDriverWait(driver, 5) 
time.sleep(1) 
if (not(siteHasId(driver, idToSearchFor="accountDeleteOkMessageSpan"))):
  print("ERROR. deleteAccount was not successful")
  finish(driver)
# end if
print("OK. deleteAccount was successful")



time.sleep(5) 
finish(driver)
