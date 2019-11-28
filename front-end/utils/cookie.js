// Create cookie
export function createCookie (name, value, days) {
    var expdate = new Date();
    expdate.setDate(expdate.getDate() + days);
    var c_value = value + ((days == null) ? "" : "; expires=" + expdate.toUTCString()) + "; path=/";
    document.cookie = name + "=" + c_value;
}

// Get Cookie
export function getCookie (c_name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, "");
        if (x == c_name) {
            return unescape(y);
        }
    }
}

// Erase Cookie
export function eraseCookie (name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
}