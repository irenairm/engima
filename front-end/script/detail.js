import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var access_token = getCookie('Authorization');

getAllDetails(access_token);

function getAllDetails (access_token) {
    var url = BACK_END_BASE_URL + 'detail/fetch?id=' + getIDParams();
    console.log('url: ', url);
    sendAJAXRequest(null, "GET", url, function (response) {
        console.log('response: ', response);
        handleResponse(response);
    }, access_token);
}

// This function utilizes AJAX to send to backend server.
function handleResponse (response) {
    if (response.status_code === '200') {
        document.querySelector('.body-ticket').innerHTML = response.message;
    } else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/login.html';
    }
}

function getIDParams () {
    var url = new URL(window.location.href);
    var id = url.searchParams.get("id");
    return id;
}


function isSchedule (params) {
    if (params.slice(0,3) === 'sch') {
        return true;
    } else {
        return false;
    }
}

window.onclick = e => {
    if (isSchedule(e.target.id)) {
        window.location.href = FRONT_END_BASE_URL + "pages/bioskop.html?schedule=" + e.target.id;
    }
}