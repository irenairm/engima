import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var access_token = getCookie('Authorization');

getAllMovies(access_token);



document.getElementById('transactions').addEventListener('click', function () {
    window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
})

   

function getAllMovies(access_token) {
    var url = BACK_END_BASE_URL + 'transaction/fetch';
    console.log('url: ', url); 
    sendAJAXRequest(null, "GET", url, function (response) {
        console.log('response: ', response);
        handleResponse(response);
    }, access_token);
}
function getParamsID () {
    var id = url.searchParams.get("id");
    return id;
}
// This function utilizes AJAX to send to backend server.
function handleResponse(response) {
    if (response.status_code === '200') {
        document.querySelector('.transaction-container').innerHTML = response.message;
        document.getElementById('add').addEventListener('click', function () {
            window.location.href = FRONT_END_BASE_URL + 'pages/userreview.html?id=' + getParamsID();
        });
    } else {
        // Returns HTML
        window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
    }
}

// addEventListener('click', function () {
//     console.log("TESUTO");
//     // window.location.href = FRONT_END_BASE_URL + 'pages/userreview.html';
// })

