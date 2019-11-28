import { FRONT_END_BASE_URL } from '../utils/const.js';

document.querySelector('.search-bar').addEventListener('click', function () {
    var value = document.getElementById('search-input').value;
    window.location.href = FRONT_END_BASE_URL + 'pages/search.html?page=1?&keyword=' + value;
})