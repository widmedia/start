import time

from selenium import webdriver
# from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
  
driver = webdriver.Firefox()
driver.get("https://widmedia.ch/start/index.php")
  
email = driver.find_element_by_name("email")
password = driver.find_element_by_name("password")
submit = driver.find_element_by_name("login")

# this account is a 'normal' account (not admin and not testUser)
email.send_keys("widmer@web-organizer.ch")
password.send_keys("blabla")

submit.click()
  
wait = WebDriverWait(driver, 5)

time.sleep(3) # the script itself

# TODO implement the checks (e.g. need to be on the links page, no login error, etc...)
# print("site title is: " + driver.getTitle())

# at the very end
driver.quit()
