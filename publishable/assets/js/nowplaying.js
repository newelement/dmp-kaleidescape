!function(t){var e={};function n(o){if(e[o])return e[o].exports;var a=e[o]={i:o,l:!1,exports:{}};return t[o].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var a in t)n.d(o,a,function(e){return t[e]}.bind(null,a));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="/",n(n.s=0)}([function(t,e,n){n(1),t.exports=n(2)},function(t,e){var n={},o=!1;function a(){axios.get("/api/dmp-kscape-now-playing").then((function(t){if(console.log(t),t.data.playing&&!o){var e=t.data.playing;!function(t){o=!0,axios.post("/api/now-playing",t).then((function(){})).catch((function(){}))}({mediaSource:"dmp-kscape",mediaType:"movie",poster:n.kscape_use_poster?e.kscpe_poster:e.image,audienceRating:e.audience_rating,contentRating:e.mpaa_rating,duration:e.runtime})}})).catch((function(t){console.log(t)}))}function i(){axios.get("/api/dmp-kscape-status").then((function(t){console.log(t),"playing"!==t.data.status||o?"stopped"===t.data.status&&o&&(o=!1,axios.post("/api/stopped",{mediaSource:"dmp-kscape"}).then((function(){})).catch((function(){}))):a()})).catch((function(t){console.log(t)}))}document.addEventListener("DOMContentLoaded",(function(){setTimeout((function(){axios.get("/api/dmp-kscape-settings").then((function(t){n=t.data,setInterval((function(){i()}),3e3)})).catch((function(t){console.log(t),console.log("COULD NOT GET Kaleidescape SETTINGS")}))}),3e3)}))},function(t,e){}]);