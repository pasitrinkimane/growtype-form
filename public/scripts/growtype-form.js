!function(){var e,n={153:function(){function e(t){t.click((function(t){t.preventDefault();var i=$('.repeater-fields[data-form-nr="1"]'),a=$(this).closest(".repeater-fields"),o=i.clone(),f=a.attr("data-form-nr"),d=parseInt(f)+1;$(this).closest(".btn-wrapper").hide(),o.hide(),o.insertAfter(a),o.find(".btn-remove").show(),o.find(".btn-wrapper").show(),o.find(".e-counter").text(d),o.attr("data-form-nr",d),o.find(".chosen-container").remove(),o.find("label.error").remove(),o.find(".error").removeClass("error"),o.find("input").val(""),o.find("select").val(""),n(i,o,d),o.find("select").chosen(window.selectArgs),o.fadeIn(),e(o.find("a.btn-add")),r(o.find(".btn-remove"))}))}function n(e,n,r){e.find("div").map((function(e,t){var i=$(t).attr("data-name");if(void 0!==i&&i.length>0){var a=i+"_"+r,o=$(n.find("div")[e]);o.attr("data-name",a),o.find("label").attr("for",a),o.find(".form-control").attr("name",a).attr("id",a),o.find("select").attr("name",a).attr("id",a)}}))}function r(e){e.click((function(){var e=$(this).closest(".repeater-fields");e.find(".btn-wrapper:visible").length>0?e.fadeOut().promise().done((function(){e.prev(".repeater-fields").find(".btn-wrapper").fadeIn(),$(this).remove()})):e.fadeOut().promise().done((function(){$(this).remove(),$(".repeater-fields").not('[data-form-nr="1"]').map((function(e,r){var t=e+2;$(r).attr("data-form-nr",t).find(".e-counter").hide().text(t).fadeIn(),n($('.repeater-fields[data-form-nr="1"]'),$(r),t)}))}))}))}$("document").ready((function(){e($(".repeater-fields a.btn-add:visible")),r($(".btn-remove"))}))},549:function(){}},r={};function t(e){var i=r[e];if(void 0!==i)return i.exports;var a=r[e]={exports:{}};return n[e](a,a.exports,t),a.exports}t.m=n,e=[],t.O=function(n,r,i,a){if(!r){var o=1/0;for(s=0;s<e.length;s++){r=e[s][0],i=e[s][1],a=e[s][2];for(var f=!0,d=0;d<r.length;d++)(!1&a||o>=a)&&Object.keys(t.O).every((function(e){return t.O[e](r[d])}))?r.splice(d--,1):(f=!1,a<o&&(o=a));if(f){e.splice(s--,1);var c=i();void 0!==c&&(n=c)}}return n}a=a||0;for(var s=e.length;s>0&&e[s-1][2]>a;s--)e[s]=e[s-1];e[s]=[r,i,a]},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},function(){var e={736:0,347:0};t.O.j=function(n){return 0===e[n]};var n=function(n,r){var i,a,o=r[0],f=r[1],d=r[2],c=0;if(o.some((function(n){return 0!==e[n]}))){for(i in f)t.o(f,i)&&(t.m[i]=f[i]);if(d)var s=d(t)}for(n&&n(r);c<o.length;c++)a=o[c],t.o(e,a)&&e[a]&&e[a][0](),e[o[c]]=0;return t.O(s)},r=self.webpackChunksage=self.webpackChunksage||[];r.forEach(n.bind(null,0)),r.push=n.bind(null,r.push.bind(r))}(),t.O(void 0,[347],(function(){return t(153)}));var i=t.O(void 0,[347],(function(){return t(549)}));i=t.O(i)}();
//# sourceMappingURL=growtype-form.js.map