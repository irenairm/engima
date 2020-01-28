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

export function soap(method_request,param,paramValue) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('POST', 'http://localhost:8888/WebServiceBank?wsdl', true);

    // build SOAP request
    var sr =
    `<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:kas="http://kasatukelima.wsbank/">
    <soapenv:Header/>
    <soapenv:Body>
        <kas:` +method_request+ `>
          <` +param+ `>` + paramValue+ `</` +param+ `>
        </kas:` +method_request+ `>
    </soapenv:Body>
  </soapenv:Envelope>`;

    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4) {
            if (xmlhttp.status == 200) {
                alert(xmlhttp.responseText);
                // alert('done. use firebug/console to see network response');
            }
        }
    }
    // Send the POST request
    xmlhttp.setRequestHeader('Content-Type', 'text/xml');
    xmlhttp.send(sr);
    // send request
    // ...
}