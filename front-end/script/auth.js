import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var access_token = getCookie('Authorization');

// Add review
window.onclick = e => {
   if (e.target.id === 'submit-rev'){
    console.log("OKE");
    fetchInfo();
    window.location.href = FRONT_END_BASE_URL + 'review/submit';
    console.log("OKE");
   }
}

// Fetch information when user booked a seat and send to backend
function fetchInfo (){
    var rating = 0;
    var stars =  document.querySelectorAll(".full");
    for (var i = stars.length; i<0; i--){
        stars[i].addEventListener('click', function () {
            rating = stars[i].htmlFor;
         })
    } 
    var description = document.getElementById("review").value;
    sendInformationToBackEnd(rating,description);
}

function getIDFParams () {
    var url = new URL(window.location.href);
    var id = url.searchParams.get("id");
    return id;
}

var id_movie = getIDFParams();
// Make JSON to send to PHP
function makeBioskopJSON (rating, description, id_movie) {
    return  {
        "rating":rating,
        "description":description,
        "id_movie":id_movie
    }
}


function sendInformationToBackEnd (rating,description,id_movie) {
    var payload = makeBioskopJSON(rating,description,id_movie);
    var url = BACK_END_BASE_URL + 'review/submit';
    sendAJAXRequest(payload, "POST", url, function(response) {
        handleBioskopResponse(response);
    }, access_token);
}


function handleBioskopResponse(response){
    if (response.status_code == '200') {
        window.location.href = FRONT_END_BASE_URL + "pages/reviews.html";
    } else {
        handleBadResponse(response);
    }
}
