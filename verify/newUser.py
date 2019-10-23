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
# 5) try to login again      | page title is not "Links"

if (True): # if something is not working below while deleting the account, I still have it and don't want to create a new one  
  driver.get("https://widmedia.ch/start/index.php?do=2") # this page contains the newUser fields (page title is Startpage)  

  doCreateNewAccount(driver, username="test.email@widmedia.ch", password="correctPassword")  
  if (not(siteHasId(driver, idToSearchFor="accountCreateOkSpan"))):
    print("ERROR. doCreateNewAccount was not successful")
    finish(driver)
  # end if
  print(".OK. doCreateNewAccount was successful")
# end if

driver.get("https://widmedia.ch/start/index.php") # back to the main page again
doLogin(driver, username="test.email@widmedia.ch", password="correctPassword")
if (not(checkSiteTitle(driver, "Links"))):
  print("ERROR. Login test with correct password not successful")
  finish(driver)
# end if
print("..OK. Login test with correct password successful") # we are now on the links page

driver.find_element_by_id("footerEditLink").click()
if (not(checkSiteTitle(driver, "Einstellungen"))):
  print("ERROR. Going to edit page was not successful")
  finish(driver)
# end if
print("...OK. Going to edit page test successful") # we are now on the edit page

driver.find_element_by_id("editPageDeleteLink").click()
if (not(siteHasId(driver, idToSearchFor="accountDeleteOkMessageSpan"))):
  print("ERROR. deleteAccount was not successful")
  finish(driver)
# end if
print("....OK. deleteAccount was successful")

driver.get("https://widmedia.ch/start/index.php") # back to the main page again
doLogin(driver, username="test.email@widmedia.ch", password="correctPassword") # this should fail now
if (checkSiteTitle(driver, "Links", outputOnFail=False)):
  print("ERROR. Login test on non-existing account not successful")
  finish(driver)
# end if
print(".....OK. Login test on non-existing account successful") 



time.sleep(2) # not required, to admire the page
finish(driver)
