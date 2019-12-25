import { FRONT_END_BASE_URL } from '../utils/const.js';


// Get the input field
var input = document.getElementById("search-input");

// Execute a function when the user releases a key on the keyboard
input.addEventListener("keyup", function(event) {
  // Number 13 is the "Enter" key on the keyboard
  if (event.keyCode === 13) {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    
    var value = document.getElementById('search-input').value;
    window.location.href = FRONT_END_BASE_URL + 'pages/search.html?page=1?&keyword=' + value;
    
  }
});

document.querySelector('.search-bar').addEventListener('click', function () {
    var value = document.getElementById('search-input').value;
    window.location.href = FRONT_END_BASE_URL + 'pages/search.html?page=1?&keyword=' + value;
})
