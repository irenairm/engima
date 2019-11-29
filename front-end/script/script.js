import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

// var button = document.getElementById("button-buy");
// var modal = document.getElementById("modal");
var seat = document.querySelectorAll("button.seat");
// var not_booked = document.getElementById("booking-message").children;
var go_transaction = document.getElementById('modal-button');
// var child = document.getElementById("booked").children;
// var current_seat = null;

//Change seat number when clicked button
// Get id of seat
window.onclick = e => {
    // current_seat = e.target.id;
    if (e.target.id >=1 && e.target.id<=30){
        document.getElementById("book-msg").innerHTML = "";
        document.getElementById("booked").style.display = "block";
        document.getElementById("movie-selected-seat").innerHTML = "Seat#" + e.target.id;
        document.getElementById("button-buy").onclick = function(){
            document.getElementById("modal").style.display = "block";
            document.getElementById("modal-button").onclick = function() {
                window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
            }
        }
    }
}

document.getElementById("button-buy").onclick = function() {
    window.location.href = FRONT_END_BASE_URL + 'pages/transaction.html';
}

button.onclick = function(){
//    modal.style.display= "block"; //show block
    alert('a');
    document.getElementById("modal").style.display = "block";
    document.getElementById("modal-content").style.display = "block";
    document.getElementById("modal-text").style.display = "block";

    document.getElementById(e.target.id).disabled = true;
    console.log('tesuto');
}

// Show movie details when clicked on seat
var i;

for (i=0; i<seat.length; i++){
    seat[i].addEventListener('click', function() {
        not_booked[1].style.display = "none";
        not_booked[2].style.display = "block";
    })
}

go_transaction.onclick = function(){
    startSendingInfo();
}

function startSendingInfo(){
    fetchInfo();
}

// Fetch information when user booked a seat and send to backend
function fetchInfo (){
    var id_schedule = 1;
    var id_movie = 1;
    var seat_booked = current_seat;
    var harga = 45000;
    var access_token = getAccessToken();
    sendInformationToBackEnd(id_schedule, id_movie, seat_booked, harga, access_token);
}

// Make JSON to send to PHP
function makeBioskopJSON (id_schedule, id_movie, id_seat, harga) {
    return  {
        "id_schedule":id_schedule,
        "id_movie":id_movie,
        "id_seat":id_seat,
        "harga": harga
    }
}

function getAccessToken () {
    return getCookie('Authorization');
}

function sendInformationToBackEnd (id_schedule, id_movie, id_seat, harga, access_token) {
    var payload = makeBioskopJSON(id_schedule, id_movie, id_seat, harga);
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

function getIdMovie () {
    var url = new URL(window.location.href);
    var keyword = url.searchParams.get("id_movie");
    return keyword;
}

function getIdSchedule () {
    var url = new URL(window.location.href);
    var keyword = url.searchParams.get("id_schedule");
    return keyword;
}