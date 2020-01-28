import { FRONT_END_BASE_URL, BACK_END_BASE_URL } from '../utils/const.js';
import { sendAJAXRequest } from '../utils/ajax.js';

// Error IDs array to be reset and shown in HTML.
var allErrorIDs = [
    'wrong-username',
    'wrong-email',
    'wrong-no_hp',
    'wrong-password',
    'wrong-reconfirm_password',
    'wrong-picture_profile'
]
// Global variable for picture profile
var picture_global;

document.getElementById('registerButton').addEventListener('click', function () {
    startInformationSendSequence();
})

document.getElementById('browseButton').addEventListener('click', function () {
    document.getElementById('pic').click();
})

document.getElementById('pic').onchange = function () {
    var path = this.value;
    var filename = path.replace(/^.*\\/, "");
    // Show the value of filename in input text box
    document.getElementById('file-name').value = filename;
    encodeImageFileAsURL(this, function(image) {
        picture_global = image;
        console.log('pic: ', picture_global);
    });
}
// When register button is clicked, this function is called first.
function startInformationSendSequence () {
    resetDocumentContent();
    fetchInformation();
}

// Reset all error text in HTML to ''
function resetDocumentContent () {
    allErrorIDs.forEach(wrongID => {
        document.getElementById(wrongID).innerHTML = '';
    })
}

function fetchInformation () {
    var username = document.getElementById("username").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var password = document.getElementById("password").value;
    var reconfirmPassword = document.getElementById("reconfirmPassword").value;
    var picture = picture_global;
    if (validatePasswords(password, reconfirmPassword)) {
        sendInformationToBackEnd(username, email, phone, password, picture);
    } else {
        showPasswordErrorsHTMLContents();
    }
}

// Front-end validation of password and reconfirm password.
function validatePasswords (password, reconfirmPassword) {
    return password === reconfirmPassword;
}

// Show front-end error.
function showPasswordErrorsHTMLContents () {
    document.getElementById('wrong-password').innerHTML = 'Passwords do not match';
    document.getElementById('wrong-reconfirm_password').innerHTML = 'Passwords do not match.';
}

// This function utilizes AJAX to send to backend server.
function sendInformationToBackEnd (username, email, phone, password, picture) {
    var payload = makeRegisterJSON(username, email, phone, password, picture);
    var url = BACK_END_BASE_URL + 'user/register';
    sendAJAXRequest(payload, "POST", url, function(response) {
        handleRegisterResponse(response);
    });
}

function makeRegisterJSON (username, email, phone, password, picture) {
    return  {
        "username": username,
        "email": email,
        "password": password,
        "picture_profile": picture,
        "no_hp": phone
    }
}

function handleRegisterResponse (response) {
    // 200 means successful status code!
    if (response.status_code == '200') {
        handleSuccessResponse();
    } else {
        handleBadResponse(response);
    }
}
// Annotate all wrong attributes (username, email, password, etc.)
// errorIDs is an object: key = <error-ID such as 'wrong-username' > and value = error message
function handleBadResponse (response) {
    var errorIDs = {};
    Object.keys(response['message']).forEach(key => {
        let wrongID = 'wrong-' + key;
        errorIDs[wrongID] = response['message'][key];
    })
    changeWrongContents(errorIDs);
}

// Change error text in HTML, assign it with messages from the backend.
function changeWrongContents (errorIDs) {
    Object.keys(errorIDs).forEach(wrongID => {
        document.getElementById(wrongID).innerHTML = errorIDs[wrongID];
    })
}

// User has been created in MYSQL.
function handleSuccessResponse () {
    document.getElementById('username').style.borderColor = "green";
    document.getElementById('email').style.borderColor = "green";
    document.getElementById('phone').style.borderColor = "green";
    document.getElementById('password').style.borderColor = "green";
    document.getElementById('reconfirmPassword').style.borderColor = "green";
    document.getElementById('file-name').style.borderColor = "green";
    window.location.href = FRONT_END_BASE_URL + 'pages/login.html';
}

// This function is fetched from https://stackoverflow.com/questions/6150289/how-to-convert-image-into-base64-string-using-javascript
function encodeImageFileAsURL(element, callback) {
    var file = element.files[0];
    var reader = new FileReader();
    reader.onloadend = function () {
        callback(reader.result)
    }
    reader.readAsDataURL(file);
}