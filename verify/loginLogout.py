from functions import *

# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
driver.set_window_size(500, 700) # about mobile size, portrait style

# go to the start page
driver.get("https://widmedia.ch/start")

# initial page title (with the login fields)
print (driver.title)

# want to verify following procedure
# 1) login with correct name/pw -> page title is Links
# 2) logout                     -> page title is Startpage again
# 3) login with faulty name/pw  -> page title is still Startpage (but with different site content)


doLogin(driver, username="widmer@web-organizer.ch", password="blabla")
if (not(checkSiteTitle(driver, "Links"))):
  print("ERROR. Login test with correct password not successful")
  finish(driver)
# end if
print("OK. Login test with correct password successful") # we are now on the links page

doLogout(driver)
if (not(checkSiteTitle(driver, "Startpage"))):
  print("ERROR. Logout test not successful")
  finish(driver)
# end if
print("OK. Logout test successful") # we are now on the start page again

doLogin(driver, username="widmer@web-organizer.ch", password="wrongPassword")
if (not(checkSiteTitle(driver, "Startpage"))):
  print("ERROR. Login test with wrong password not successful")
  finish(driver)
# end if
print("OK. Login test with wrong password successful") # we are still on the start page (but only with error messages)

# time.sleep(2) # not needed, to admire the page
finish(driver)


# element = driver.find_element_by_id("element_id")
# element.text
# from selenium.webdriver.support.ui import Select
# select = Select(driver.find_element_by_tag_name("select"))
# select.deselect_all()
# select.select_by_visible_text("Edam")
# time.sleep(2) # not needed, to admire the page
