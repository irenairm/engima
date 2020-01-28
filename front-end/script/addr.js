import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var access_token = getCookie('Authorization');

// getAllReviews(access_token);
getStatus(access_token);
// Show page to add review
function getStatus (access_token) {
    var url = BACK_END_BASE_URL + 'review/fetch?id=' + getIDFParams();
    console.log('url: ', url); 
    sendAJAXRequest(null, "GET", url, function (response) { 
        console.log('response: ', response.message);
        handleResponse(response);
    }, access_token);
}
function handleResponse (response) {
    if (response.status_code === '200') {
        document.querySelector('.container-ticket').innerHTML = response.message;
    } else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/';
    }
}

function getIDFParams () {
    var url = new URL(window.location.href);
    var id = url.searchParams.get("id");
    return id;
}

var id_movie = getIDFParams();

