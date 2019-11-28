export function sendAJAXRequest (payload, method_request, url, callback, auth) {
    var xhr = new XMLHttpRequest();
    console.log(payload);
    console.log(auth);
    // Call a function when the state changes.
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            // Backend returns JSON, handled by following function:
            callback(response);
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
