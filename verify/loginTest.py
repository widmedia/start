# import time
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# returns a boolean
def checkSiteTitle (driver, expectedSiteTitle):
  try:
    # we have to wait for the page to refresh, the last thing that seems to be updated is the title
    WebDriverWait(driver, 5).until(EC.title_contains(expectedSiteTitle))  # timeout in seconds    
    print("Site title as expected: " + driver.title)
    return True
  except: # most probably the timeout exception. TODO: check on just the timeout exception
    print("Site title not as expected: " + driver.title)
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


# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
driver.set_window_size(500, 700) # mobile, portrait style

# go to the start page
driver.get("https://widmedia.ch/start")

# initial page title (with the login fields)
print (driver.title)

# want to verify following procedure
# 1) login with correct name/pw  -> page title is Links
# 2) logout                      -> page title is Startpage again
# 3) login with faulty name/pw   -> page title is still Startpage


doLogin(driver, username="widmer@web-organizer.ch", password="blabla")

if (not(checkSiteTitle(driver, "Links"))):
  print("ERROR. Login test not successful")
  driver.quit()
  sys.exit()
# end if
  
print("OK. Login test successful") # we are now on the links page






driver.quit() # close the browser window



# element = driver.find_element_by_id("element_id")
# element.text
# from selenium.webdriver.support.ui import Select
# select = Select(driver.find_element_by_tag_name("select"))
# select.deselect_all()
# select.select_by_visible_text("Edam")
# time.sleep(2) # not needed, to admire the page
