(function() {
    // Load the script
    var script = document.createElement("SCRIPT");
    script.src = '/payqr/diafanpayqr.js';
    script.type = 'text/javascript';
    document.getElementsByTagName("body")[0].appendChild(script);

    // Poll for jQuery to come into existance
    var checkReady = function(callback) {
        if (window.jQuery) {
            callback(jQuery);
        }
        else {
            window.setTimeout(function() { checkReady(callback); }, 100);
        }
    };

    // Start polling...
    checkReady(function($) {
        // Use $ here...
    });
})();