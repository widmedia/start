@echo off
setlocal
:: batch script, somewhat outdated and superseded by the python / selenium scripts

"C:/Program Files/Mozilla Firefox/firefox.exe" -P newProfile -headless --screenshot index800_800.png https://widmedia.ch/start --window-size=800,800
:: teaser image has not yet been fully faded into view

:: logging in does not yet work:
::"C:/Program Files/Mozilla Firefox/firefox.exe" -P newProfile -headless --screenshot links800_800.png https://widmedia.ch/start/links.php --window-size=800,800

endlocal
@echo on
