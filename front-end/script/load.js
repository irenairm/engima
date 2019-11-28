import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';
import { getCookie } from '../utils/cookie.js';

var curr_page = 1;
var max_page = 1;
// Pre load event: LOAD BEFORE DOM IS LOADED:
var access_token = getCookie('Authorization');
var keyword = getKeywordParams();

getAllMoviesByKeyword(access_token, 1, keyword);

// On click event:
window.onclick = e => {
    if (e.target.id >= 1 && e.target.id <= max_page) {
        curr_page = e.target.id;
        getAllMoviesByKeyword(access_token, curr_page, keyword);
    } else if (e.target.id === 'next') {
        if (curr_page < max_page) {
            curr_page++;
            getAllMoviesByKeyword(access_token, curr_page, keyword);
        }
    } else if (e.target.id === 'back') {
        if (curr_page > 1) {
            curr_page--;
            getAllMoviesByKeyword(access_token, curr_page, keyword);
        }
    } else if (isMovieDetail(e.target.id)) {
        window.location.href = FRONT_END_BASE_URL + "pages/movie_detail.html?id=" + e.target.id;
    }
}

function getAllMoviesByKeyword (access_token, page, keyword) {
    var url = BACK_END_BASE_URL + 'home/fetch?page=' + page  + '&keyword=' + keyword;
    sendAJAXRequest(null, "GET", url, function (response) {
        handleResponse(response, page);
    }, access_token);
}

// This function utilizes AJAX to send to backend server.
function handleResponse (response, page) {
    if (response.status_code === '200') {
        // Change max_page global variable.
        max_page = Math.ceil(response.count / 5);
        showPaginationButtons(max_page, page);
        document.getElementById('page').innerHTML = response.message;
    } else {
        // Returns HTML
        window.location.href = FRONT_END_BASE_URL + 'pages/login.html';
    }
}

function getKeywordParams () {
    var url = new URL(window.location.href);
    var keyword = url.searchParams.get("keyword");
    return keyword;
}

function showPaginationButtons (page_count, page) {
    var html_element = '';
    for (let id = 1; id <= page_count; id++) {
        if (id != page) {
            html_element += '<button id=' + id + ' class="seat" type="submit"> ' + id + ' </button>';
        } else {
            html_element += '<button id=' + id + ' class="seat seat-colored" type="submit"> ' + id + ' </button>';
        }
    }
    document.getElementById('buttons').innerHTML = html_element;
}

function isMovieDetail (movieID) {
    if (movieID.slice(0,3) === 'mov') {
        return true;
    } else {
        return false;
    }
}