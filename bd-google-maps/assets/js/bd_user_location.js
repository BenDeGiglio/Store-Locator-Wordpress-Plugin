jQuery(document).ready(function( $ ) {
  bdCheckCookie('bd_lat');
}); 

function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        bdSetCookie('bd_lat', position.coords.latitude, 10);
        bdSetCookie('bd_lng', position.coords.longitude, 10);
        changeLocation();
      });
    } else {
        alert('Geolocation is not supported by this browser.')
    }
}

function bdSetCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function bdGetCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function bdCheckCookie() {
    var lat = bdGetCookie('bd_lat');
    if (lat == "") {
        getLocation();
    }
}


