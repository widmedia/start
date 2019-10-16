import time
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

correctPassword = "blabla"
wrongPassword = "blablabla"


# Create a new instance of the Firefox driver
driver = webdriver.Firefox()
# driver.set_window_size(800, 800)
driver.set_window_size(500, 700) # mobile, portrait style

# go to the start page
driver.get("https://widmedia.ch/start/index.php")

# initial page title (with the login fields)
print (driver.title)

# find the elements I want to control
email    = driver.find_element_by_name("email")
password = driver.find_element_by_name("password")
submit   = driver.find_element_by_name("login")

# action starts, send the login details
email.send_keys("widmer@web-organizer.ch")
password.send_keys(correctPassword)
# password.send_keys(wrongPassword)

# and submit
submit.click() # could also use a command like: password.submit()

# element = driver.find_element_by_id("element_id")
# element.text
# from selenium.webdriver.support.ui import Select
# select = Select(driver.find_element_by_tag_name("select"))
# select.deselect_all()
# select.select_by_visible_text("Edam")


try:
    # we have to wait for the page to refresh, the last thing that seems to be updated is the title
    WebDriverWait(driver, 10).until(EC.title_contains("Links"))
    
    print(driver.title)
    # time.sleep(2) # not needed, to admire the page


# working fine, get a timeout exception when the password is not correc

finally:
    driver.quit()