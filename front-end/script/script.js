import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';


var seat = document.querySelectorAll("button.seat");


//Change seat number when clicked button
// Get id of seat
window.onclick = e => {
    // current_seat = e.target.id;
    if (e.target.id >=1 && e.target.id<=30){
        document.getElementById("book-msg").innerHTML = "";
        document.getElementById("booked").style.display = "block";
        document.getElementById("movie-selected-seat").innerHTML = "Seat #" + e.target.id;
        var current_seat = e.target.id;
        // When button "Buy Ticket" is clicked, send info to backend
        document.getElementById("button-buy").onclick = function(){
            
            fetchInfo(current_seat);
            document.getElementById("modal").style.display = "block";
            document.getElementById("modal-button").onclick = function() {
                window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
            }
        }
    }
}

// Fetch information when user booked a seat and send to backend
function fetchInfo (current_seat){
    var id_schedule = getIDSchedule();
    var id_movie = getIDMovie();
    var seat_booked = current_seat;
    var access_token = getAccessToken();
    sendInformationToBackEnd(id_schedule, id_movie, seat_booked, access_token);
}

// Make JSON to send to PHP
function makeBioskopJSON (id_schedule, id_movie, id_seat) {
    return  {
        "id_schedule":id_schedule,
        "id_movie":id_movie,
        "id_seat":id_seat
    }
}

function getAccessToken () {
    return getCookie('Authorization');
}

function sendInformationToBackEnd (id_schedule, id_movie, id_seat, access_token) {
    var payload = makeBioskopJSON(id_schedule, id_movie, id_seat);
    var url = BACK_END_BASE_URL + 'bioskop/submit';
    sendAJAXRequest(payload, "POST", url, function(response) {
        handleBioskopResponse(response);
    }, access_token);
}


function handleBioskopResponse(response){
    if (response.status_code == '200') {
        handleSuccessResponse();
    } else {
        handleBadResponse(response);
    }
}

function getIDMovie () {
    var url = new URL(window.location.href);
    var keyword = url.searchParams.get("id");
    return keyword;
}

function getIDSchedule () {
    var url = new URL(window.location.href);
    var keyword = url.searchParams.get("schedule");
    return keyword;
}