import sys
from selenium import webdriver
from functions import finish, printOkOrNot
from loginLogout import doLoginLogout
from newUser import doNewUser

# main file to start other tests
#------------------------------------------------------------------------------
# available tests:
# 1) loginLogout
# 2) newUser
def printUsage(allTests):
  print("Usage: run.py [testName]")
  print("run.py: runs all the available tests")
  print("run.py testName: runs a single test")
  print("  available tests are: ", end="")
  for test in allTests:
    print(test + " ", end="")
# end def


allTests = ['loginLogout', 'newUser']

testsToRun = []
if len(sys.argv) < 2:  # this means no argument has been given. Running all tests
  testsToRun = allTests  
elif len(sys.argv) == 2:
  if sys.argv[1] in allTests:  # find the argument
    testsToRun = [sys.argv[1]]
  else:
    printUsage(allTests)
  # end if 
else:
    printUsage(allTests)
# end if

if len(testsToRun) > 0:
  # Create a new instance of the Firefox driver
  driver = webdriver.Firefox()
  driver.set_window_size(500, 700) # about mobile size, portrait style

  # TODO: those two calls just differ in the function to be called. Could be merged into a for loop?
  if allTests[0] in testsToRun:
    testNum = 1 # loginLogout
    if (not(doLoginLogout(driver, testNum))):
      finish(driver)
    print("----")
  # end if 

  if allTests[1] in testsToRun:
    testNum = 2 # newUser
    if (not(doNewUser(driver, testNum))):
      finish(driver)
    print("----")
  # end if 

  printOkOrNot(ok=True, testNum="==>", text="Selected tests execution")
  finish(driver)
# end if len testsToRun