/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

/*!
 * Generated using the Bootstrap Customizer (http://getbootstrap.com/customize/?id=c992c52bc3a08c635528a423a2df6f3a)
 * Config saved to config.json and https://gist.github.com/c992c52bc3a08c635528a423a2df6f3a
 */
if("undefined"==typeof jQuery)throw new Error("Bootstrap's JavaScript requires jQuery");+function(t){"use strict";var r=t.fn.jquery.split(" ")[0].split(".");if(r[0]<2&&r[1]<9||1==r[0]&&9==r[1]&&r[2]<1||r[0]>3)throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher, but lower than version 4")}(jQuery),+function(t){"use strict";function r(r){return this.each(function(){var e=t(this),n=e.data("bs.alert");n||e.data("bs.alert",n=new a(this)),"string"==typeof r&&n[r].call(e)})}var e='[data-dismiss="alert"]',a=function(r){t(r).on("click",e,this.close)};a.VERSION="3.3.7",a.TRANSITION_DURATION=150,a.prototype.close=function(r){function e(){o.detach().trigger("closed.bs.alert").remove()}var n=t(this),s=n.attr("data-target");s||(s=n.attr("href"),s=s&&s.replace(/.*(?=#[^\s]*$)/,""));var o=t("#"===s?[]:s);r&&r.preventDefault(),o.length||(o=n.closest(".alert")),o.trigger(r=t.Event("close.bs.alert")),r.isDefaultPrevented()||(o.removeClass("in"),t.support.transition&&o.hasClass("fade")?o.one("bsTransitionEnd",e).emulateTransitionEnd(a.TRANSITION_DURATION):e())};var n=t.fn.alert;t.fn.alert=r,t.fn.alert.Constructor=a,t.fn.alert.noConflict=function(){return t.fn.alert=n,this},t(document).on("click.bs.alert.data-api",e,a.prototype.close)}(jQuery);