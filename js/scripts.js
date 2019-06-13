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

// displays a message and does then the fade out of this
function msgShow() {
  document.getElementById("overlay").style.display = "block";
  fade(document.getElementById("overlay"));
}

// fades out a message and does a display: none when it's fully faded out
function fade(element) {
var op = 1;  // initial opacity
var timer = setInterval(function () {
    if (op <= 0.1){
        clearInterval(timer);
        element.style.display = 'none';
    }
    element.style.opacity = op;
    element.style.filter = 'alpha(opacity=' + op * 100 + ")";
    op -= op * 0.1;
  }, 300);
}
