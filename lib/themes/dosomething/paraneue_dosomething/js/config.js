//
//
// RequireJS Configuration
// We load and shim external components here.
//
//

(function() {
  "use strict";

  require.config({
    baseUrl: "/profiles/dosomething/themes/dosomething/paraneue_dosomething/js/",
    optimize: "none",
    include: "main",
    paths: {
      "jquery": "../bower_components/jquery/dist/jquery",
      "neue": "../bower_components/neue/neue",
      "mailcheck": "../bower_components/mailcheck/src/mailcheck"
    },
    shim: {
      "neue": { deps: ["jquery"], exports: "NEUE" },
      "mailcheck": { exports: "Kicksend.mailcheck" }
    }
  });
})();
