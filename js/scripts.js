// changes the 'display' property of the pw-text field (actually the whole row) and some warning message
function pwToggle() {
  if (document.getElementById("pwCheckBox").checked == 1) {
    document.getElementById("pwRow").style.display = "initial";
    document.getElementById("noPwWarning").style.display = "none";
  } else {
    document.getElementById("pwRow").style.display = "none";
    document.getElementById("noPwWarning").style.display = "block";
  }
}

// fades out a message and does a display: none when it's fully faded out
function overlayMsgFade() {
  element = document.getElementById("overlay");
  var op = 0.8;  // initial opacity
  var timer = setInterval(function () {
    if (op <= 0.3){
        clearInterval(timer);
        element.style.display = 'none';
    }
    element.style.opacity = op;
    element.style.filter = 'alpha(opacity=' + op * 100 + ")";
    op -= op * 0.05;
  }, 200);
}