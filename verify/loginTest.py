import time
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait # available since 2.4.0
from selenium.webdriver.support import expected_conditions as EC # available since 2.26.0

# Create a new instance of the Firefox driver
driver = webdriver.Firefox()

driver.get("https://widmedia.ch/start/index.php")

# initial page title (with the login fields)
print (driver.title)

# find the elements I want to control
email = driver.find_element_by_name("email")
password = driver.find_element_by_name("password")
submit = driver.find_element_by_name("login")

email.send_keys("widmer@web-organizer.ch")
password.send_keys("blabla")

submit.click()

try:
    # we have to wait for the page to refresh, the last thing that seems to be updated is the title
    WebDriverWait(driver, 10).until(EC.title_contains("Links"))
    
    print(driver.title)
    time.sleep(2) # not needed, to admire the page

finally:
    driver.quit()