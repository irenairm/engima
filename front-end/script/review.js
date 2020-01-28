import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

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
        document.getElementById('transaction-container').innerHTML = response.message;
    } else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/pelarian.html';
    }
}

// document.getElementById('edit').addEventListener('click', function () {
//     console.log("oK");
//     window.location.href = FRONT_END_BASE_URL + 'pages/userreview.html';
// })

document.getElementById('delete').addEventListener('click', function () {
    var url = BACK_END_BASE_URL + 'review/delete';
    sendAJAXRequest(null, "DELETE", url, function (response) { 
        handleDeleteResponse(response);
    }, access_token);
})

function handleDeleteResponse (response) {
    if (response.status_code === '200') {
        window.location.href = FRONT_END_BASE_URL + 'pages/reviews.html';
    } else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/pelarian.html';
    }
}