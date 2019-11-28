export function sendAJAXRequest (payload, method_request, url, callback, auth) {
    var xhr = new XMLHttpRequest();
    console.log('payload: ', payload); 
    console.log('auth: ', auth); 
    // Call a function when the state changes.
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log('this response Text: ')
            console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            // Backend returns JSON, handled by following function:
            console.log('response message:', response.message);
            callback(response);
            console.log('passed callback!')
        }
    }
    xhr.open(method_request, url);
    xhr.setRequestHeader("Content-Type", "application/json");
    if (auth) {
        xhr.setRequestHeader("Authorization", auth);
    }
    if (payload) {
        xhr.send(JSON.stringify(payload));
    } else {
        xhr.send(null);
    }
}
