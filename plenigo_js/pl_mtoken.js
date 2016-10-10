var pl_mtoken_loaded = false;
var pl_mtoken_remove_msg = pl_mtoken_remove_msg || "Are you sure you want to remove this App ID?";
jQuery(document).ready(function () {
    pl_mtoken_loaded = true;
});

function plenigo_create_mtoken(product, customer) {
    if (pl_mtoken_loaded) {
        var address = plenigo_mtoken_address(product, customer);
        var elemText = document.getElementById("plenigo_" + product + "_desc");
        if (elemText) {
            var deviceText = elemText.value;
            if (deviceText && deviceText !== "") {
                address += "&mobileDEV=" + encodeURIComponent(deviceText);
                window.location.href = address;
            } else {
                elemText.style.backgroundColor = "lightyellow";
                elemText.focus();
            }
        }
    }
}

function plenigo_remove_mtoken(product, customer, appID) {
    if (pl_mtoken_loaded) {
        var address = plenigo_mtoken_address(product, customer);
        if (confirm(pl_mtoken_remove_msg)) {
            address += "&removeAID=" + appID;
            window.location.href = address;
        }
    }
}

function plenigo_mtoken_address(product, customer) {
    var parser = document.createElement('a');
    parser.href = window.location.href;
    if (!parser.origin) {
        parser.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
    }
    var res = parser.origin + parser.pathname + parser.search;

    res = removeParam("mobileDEV", res);
    res = removeParam("mobilePID", res);
    res = removeParam("mobileCID", res);
    res = removeParam("removeAID", res);

    if (parser.search && parser.search !== "") {
        res += "&";
    } else {
        res += "?";
    }

    res += "mobilePID=" + product + "&mobileCID=" + customer;

    return res;
}


function removeParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}