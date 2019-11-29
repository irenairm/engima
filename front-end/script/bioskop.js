import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var access_token = getCookie('Authorization');

getStatus(access_token); // Get seats before loading

function getStatus (access_token) {
    var url = BACK_END_BASE_URL + 'bioskop/fetch?schedule=' + getIDParams() +'&id=' + getIDFParams();
    console.log('url: ', url); 
    sendAJAXRequest(null, "GET", url, function (response) { 
        console.log('response: ', response.message);
        handleResponse(response);
    }, access_token);
}

// This function utilizes AJAX to send to backend server.
function handleResponse (response) {
    if (response.status_code === '200') {
        document.querySelector('.container-ticket').innerHTML = response.message;
    } 
    else {
        // Returns HTML
         window.location.href = FRONT_END_BASE_URL + 'pages/pelarian.html';
        // alert(response.message);
    }
}

function getIDParams () {
    var url = new URL(window.location.href);
    var id = url.searchParams.get("schedule");
    return id;
}

function getIDFParams () {
    var url = new URL(window.location.href);
    var id = url.searchParams.get("id");
    return id;
}

window.onclick = e => {
    if (isSchedule(e.target.id)) {
        window.location.href = FRONT_END_BASE_URL + "pages/bioskop.html?schedule=" + e.target.id;
    }
}

// document.getElementById("button-buy").onclick = function(){
//     document.getElementById("modal").style.display = "block";
//     document.getElementById("modal-button").onclick = function() {
//         window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
//     }
// }