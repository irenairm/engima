import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

// Add review
document.getElementById('add').addEventListener('click', function () {
    window.location.href = FRONT_END_BASE_URL + 'review/submit';
})

// Show review
var access_token = getCookie('Authorization');

getAllReviews(access_token);

function getAllReviews (access_token) {
    var url = BACK_END_BASE_URL + 'review/fetch';
    sendAJAXRequest(null, "GET", url, function (response) { 
        handleResponse(response);
    }, access_token);
}

// This function utilizes AJAX to send to backend server.
function handleResponse (response) {
    if (response.status_code === '200') {
        document.getElementById('review').innerHTML = response.message;
    } else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/login.html';
    }
}