!function(){function e(r){r.click((function(r){r.preventDefault();var i=$('.repeater-fields[data-form-nr="1"]'),n=$(this).closest(".repeater-fields"),d=i.clone(),o=n.attr("data-form-nr"),s=parseInt(o)+1;if(n.closest(".b-wrapper").hasClass("repeater-fields-folded")){var p=n.closest(".b-wrapper").attr("data-group");return $('.b-wrapper[data-group="'+p+'"]').find('.form-check-wrapper[aria-required="true"]').attr("aria-required","false").find("input").prop("checked",!1),n.closest(".b-wrapper").removeClass("repeater-fields-folded"),n.find(".btn-remove").show(),n.find(".btn-add").hide(),!1}$(this).closest(".btn-wrapper").hide(),d.hide(),d.insertAfter(n),d.find(".btn-remove").show(),d.find(".btn-wrapper").show(),d.find(".e-counter").text(s),d.attr("data-form-nr",s),d.find(".chosen-container").remove(),d.find("label.error").remove(),d.find(".error").removeClass("error"),d.find("input").val(""),d.find("select").val(""),t(i,d,s),d.find("select").chosen(window.selectArgs),d.fadeIn(),e(d.find("a.btn-add")),a(d.find(".btn-remove"))}))}function t(e,t,a){e.find(".e-wrapper").map((function(e,r){var i=$(r).attr("data-name");if(void 0!==i&&i.length>0){var n="";n=null!==i.match(/\[/g)?(i=i.split("["))[0]+"_"+a+"["+i[1]:i+"_"+a;var d=$(t.find("div")[e+1]);d.attr("data-name",n),d.find("label").attr("for",n),d.find(".form-control").attr("name",n).attr("id",n),d.find("select").attr("name",n).attr("id",n)}}))}function a(e){e.click((function(){var e=$(this).closest(".repeater-fields");if("1"===e.attr("data-form-nr")){var a=e.closest(".b-wrapper").attr("data-group");return $('.b-wrapper[data-group="'+a+'"]').find('.form-check-wrapper[aria-required="false"]').attr("aria-required","true"),e.closest(".b-wrapper").addClass("repeater-fields-folded"),e.find(".btn-add").fadeIn(),!1}e.find(".btn-wrapper:visible").length>0?e.fadeOut().promise().done((function(){e.prev(".repeater-fields").find(".btn-wrapper").fadeIn(),$(this).remove()})):e.fadeOut().promise().done((function(){$(this).remove(),$(".repeater-fields").not('[data-form-nr="1"]').map((function(e,a){var r=e+2;$(a).attr("data-form-nr",r).find(".e-counter").hide().text(r).fadeIn(),t($('.repeater-fields[data-form-nr="1"]'),$(a),r)}))}))}))}$=jQuery,$("document").ready((function(){e($(".repeater-fields a.btn-add")),a($(".btn-remove"));var t=!0;try{new DataTransfer}catch(e){t=!1}var r=$(".image-uploader-init"),i=r.attr("data-name"),n=void 0!==r.attr("data-extensions")?r.attr("data-extensions").split(","):"",d=r.attr("data-max-size");if(t?void 0!==$.fn.imageUploader&&(r.addClass("image-uploader"),$(".image-uploader").each((function(){var e="undefined"!=typeof growtype_form_image_upload_data?growtype_form_image_upload_data:[],t=[];if(Object.entries(e).length>0){var a=JSON.parse(e.preloaded);Object.entries(a).length>0&&(t=a)}$(this).imageUploader({preloaded:t,imagesInputName:i,extensions:n,maxSize:d})}))):($('<input multiple type="file" class="upload-multifile with-preview" className="multi" name="'+i+'[]"/>').insertAfter(".image-uploader-init"),$(".upload-multifile").MultiFile({max:10,accept:n.join([separator=","]),max_size:d})),$(".datepicker").length>0&&$(".datepicker").datepicker(),$(".timepicker").length>0&&$(".timepicker").timepicker(),$(".datetimepicker").length>0){var o=function(e,t){var a=Date.parse(t);try{if(isNaN(a))throw null;$.datepicker.parseDate(l,t)}catch(t){e.val("")}},s=new Date,p=s.getHours(),c=s.getMinutes(),l=growtype_form_date_time_data.date_format,f=new Date;f.setDate(f.getDate()+1),f.toLocaleDateString(),$(".datetimepicker").datetimepicker({language:"en",defaultDate:s,dateFormat:l,numberOfMonths:1,hour:p,minute:c,minDate:f,onClose:function(e,t){if("_auction_dates_from"===$(this).attr("name")){var a=new Date(e);a.setDate(a.getDate()+1),a.toLocaleDateString(),$('.datetimepicker[name="_auction_dates_to"]').datetimepicker("option","minDate",a)}o($(this),e)},onSelectDate:function(e,t){o($(this),e)},onChangeDateTime:function(e,t){o($(this),e)}})}$(".autonumeric").length>0&&$(".autonumeric").autoNumeric("init",{unformatOnSubmit:!0,digitGroupSeparator:autoNumericdata.digitGroupSeparator,decimalCharacter:autoNumericdata.decimalCharacter,currencySymbol:autoNumericdata.currencySymbol,currencySymbolPlacement:autoNumericdata.currencySymbolPlacement,decimalPlacesOverride:autoNumericdata.decimalPlacesOverride,showWarnings:!1,emptyInputBehavior:"press",minimumValue:0}),$(".e-wrapper .btn-img-remove").click((function(){var e=$(this).attr("data-type"),t=$(this).attr("data-id"),a=$(this).attr("data-class"),r=$(this).attr("data-name"),i=$(this).attr("data-accept"),n=$(this).attr("data-required"),d=$('<input type="'+e+'" id="'+t+'" class="'+a+'" name="'+r+'"  accept="'+i+'"  '+n+">");d.removeClass("has-value"),$(this).closest(".input-file-wrapper").hide(),$(this).closest(".e-wrapper").append(d),d.filestyle({buttonBefore:!0})}))}))}();
//# sourceMappingURL=growtype-form-render.js.map