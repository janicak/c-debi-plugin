/*!
 * 
 * cDebi
 * 
 * @author 
 * @version 0.1.0
 * @link UNLICENSED
 * @license UNLICENSED
 * 
 * Copyright (c) 2020 
 * 
 * This software is released under the UNLICENSED License
 * https://opensource.org/licenses/UNLICENSED
 * 
 * Compiled with the help of https://wpack.io
 * A zero setup Webpack Bundler Script for WordPress
 */
(window.wpackiocDebiappJsonp=window.wpackiocDebiappJsonp||[]).push([[5],{216:function(n,e,t){t(29),n.exports=t(217)},217:function(n,e){var t,o,i,c,a,r,u;t=jQuery,o=document,i=window.c_debi_plugin,c=i.ajax_url,a=i.action,r=i.nonce,u=i.route,t(o).ready((function(){t("#one-time").on("submit",(function(n){n.preventDefault();var e=t(this).find(".status .message").eq(0);e.html("");var o=[];return t(this).find('input[type="checkbox"]:checked').each((function(n,e){o.push(JSON.parse(e.value))})),o.length&&(function(n,e){var o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;t.ajax({url:c,type:"POST",data:{action:a,nonce:r,route:u,reqs:n},error:function(n,e,t){console.log(t)},success:function(n){o&&window.clearInterval(o),e.html("<pre>".concat(JSON.stringify(n),"</pre>"))}})}(o,e),e.html("Awaiting response...")),!1}))}))},29:function(n,e,t){"use strict";var o="cDebidist".replace(/[^a-zA-Z0-9_-]/g,"");t.p=window["__wpackIo".concat(o)]}},[[216,0]]]);
//# sourceMappingURL=c-debi_one_time-be890986.js.map