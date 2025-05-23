$ = jQuery;

/**
 * Group Repeater
 */
import {repeater} from "./partials/actions/repeater";

/**
 * Conditions
 */
import {conditions} from "./partials/actions/conditions";

/**
 * General
 */
$('document').ready(function () {

    /**
     * Set repeater
     */
    repeater();

    /**
     * Set conditions
     */
    conditions();

    /**
     * Image uploader
     */
    setupImageUploader();

    /**
     * Set date
     */
    if ($(".datepicker").length > 0) {
        $(".datepicker").each(function (index, element) {
            let options = {
                dateFormat: growtype_form.date.date_format_iso
            };

            if ($(element).attr('min-date') !== undefined) {
                options.minDate = $(element).attr('min-date');
            }

            $(element).datepicker(options);
        });
    }

    /**
     * Set time
     */
    if ($(".timepicker").length > 0) {
        $('.timepicker').timepicker();
    }

    /**
     * Set date and time pickers
     */
    if ($(".datetimepicker").length > 0) {
        const date = new Date();
        let hour = date.getHours();
        let minute = date.getMinutes();

        let dateFormat = growtype_form.date.date_format_iso;

        function validateValue($this, dateText) {
            let parsedValue = Date.parse(dateText);
            try {
                if (isNaN(parsedValue)) {
                    throw null;
                }
                $.datepicker.parseDate(dateFormat, dateText);
            } catch (e) {
                $this.val('')
            }
        }

        let startDateMinDate = new Date();
        startDateMinDate.setDate(startDateMinDate.getDate() + 1);
        startDateMinDate.toLocaleDateString();

        $('.datetimepicker').datetimepicker({
            language: 'en',
            defaultDate: date,
            dateFormat: dateFormat,
            numberOfMonths: 1,
            hour: hour,
            minute: minute,
            minDate: startDateMinDate,
            onClose: function (dateText, inst) {
                if ($(this).attr('name') === '_auction_dates_from') {
                    let endDateMinDate = new Date(dateText);
                    endDateMinDate.setDate(endDateMinDate.getDate() + 1);
                    endDateMinDate.toLocaleDateString();
                    $('.datetimepicker[name="_auction_dates_to"]').datetimepicker("option", "minDate", endDateMinDate);
                }
                validateValue($(this), dateText)
            },
            onSelectDate: function (dateText, inst) {
                validateValue($(this), dateText)
            },
            onChangeDateTime: function (dateText, inst) {
                validateValue($(this), dateText)
            }
        });
    }

    /**
     * Autonumeric
     */
    if ($(".autonumeric").length > 0) {
        $('.autonumeric').autoNumeric('init', {
            unformatOnSubmit: true,
            digitGroupSeparator: growtype_form.currency.digitGroupSeparator,
            decimalCharacter: growtype_form.currency.decimalCharacter,
            currencySymbol: growtype_form.currency.currencySymbol,
            currencySymbolPlacement: growtype_form.currency.currencySymbolPlacement,
            decimalPlacesOverride: growtype_form.currency.decimalPlacesOverride,
            showWarnings: false,
            emptyInputBehavior: 'press',
            minimumValue: 0
        });
    }

    /**
     * Img remove
     */
    $('.e-wrapper .btn-img-remove').click(function () {
        let inputType = $(this).attr('data-type');
        let inputId = $(this).attr('data-id');
        let inputClass = $(this).attr('data-class');
        let inputName = $(this).attr('data-name');
        let inputAccept = $(this).attr('data-accept');
        let inputRequired = $(this).attr('data-required');
        let newInput = $('<input type="' + inputType + '" id="' + inputId + '" class="' + inputClass + '" name="' + inputName + '"  accept="' + inputAccept + '"  ' + inputRequired + '>');

        newInput.removeClass('has-value');

        $(this).closest('.input-file-wrapper').hide();
        $(this).closest('.e-wrapper')
            .append(newInput);

        newInput.filestyle({buttonBefore: true})
    });

    /**
     * General file input
     */
    $('.growtype-form-wrapper .input-file-wrapper input[type=file]').change(function (e) {
        let maxFileSize = $(this).attr('max-size');
        let maxSizeErrorMessage = $(this).attr('max-size-error-message');
        if (maxFileSize !== undefined) {
            $(e.target.files).each(function (index, element) {
                if (element.size > maxFileSize) {
                    if (maxSizeErrorMessage.length > 0) {
                        maxSizeErrorMessage = maxSizeErrorMessage.replace(':image_name', element.name).replace(':max_size', (maxFileSize / 1000000) + 'mb')
                        alert(maxSizeErrorMessage)
                    } else {
                        alert(element.name + " is too big! Max file size allowed - " + (maxFileSize / 1000000) + 'mb')
                    }
                    e.target.value = "";
                }
            });
        }

        let selectedPlaceholderSingle = $(this).attr('data-selected-placeholder-single');
        let selectedPlaceholderMultiple = $(this).attr('data-selected-placeholder-multiple');
        let filesAmount = e.target.files.length;

        $(this).closest('.input-file-wrapper')
            .find('.form-label-placeholder')
            .removeClass('is-active')
            .text($(this).attr('data-placeholder'))

        if (filesAmount > 0 && (selectedPlaceholderSingle.length > 0 || selectedPlaceholderMultiple.length > 0)) {

            let selectedPlaceholder = selectedPlaceholderSingle.replace(':nr', filesAmount)

            if (filesAmount > 1) {
                selectedPlaceholder = selectedPlaceholderMultiple.replace(':nr', filesAmount)
            }

            $(this).closest('.input-file-wrapper')
                .find('.form-label-placeholder')
                .addClass('is-active')
                .text(selectedPlaceholder)
        }
    });
});

/**
 * Growtype quiz check if image uploader has files
 */
document.addEventListener('growtypeQuizValidateQuestion', function (event) {
    $(event.detail.currentQuestion).find('.growtype-form-fields .b-wrapper').each(function (index, element) {
        if ($(element).find('div[data-required]') && $(element).find('div[data-required]').attr('data-required') === 'true') {
            if (!$(element).find('.image-uploader-inner').hasClass('has-files')) {
                $(element).addClass('anim-wrong-selection');

                setTimeout(function () {
                    $(element).removeClass('anim-wrong-selection');
                }, 500);

                window.growtype_quiz_global.is_valid = false;
            }
        }
    });
})

/**
 * Image uploaded setup
 */
function setupImageUploader() {
    /**
     * Image uploader setup
     */
    let defaultGallerySupported = true;

    try {
        let dataTransfer = new DataTransfer();
    } catch (err) {
        defaultGallerySupported = false;
    }

    let imageUploaderInitial = $('.image-uploader-init');

    if (imageUploaderInitial.length === 0) {
        return;
    }

    imageUploaderInitial.each(function (index, element) {
        let formInput = $(element);
        let imageUploaderInitialName = formInput.attr('data-name');
        imageUploaderInitialName = imageUploaderInitialName ? imageUploaderInitialName : formInput.closest('.b-wrapper').attr('data-name');
        imageUploaderInitialName = imageUploaderInitialName.replace('[]', '');
        let imageUploaderInitialExtension = formInput.attr('data-extensions') !== undefined ? formInput.attr('data-extensions').split(",") : '';
        let imageUploaderInitialMaxSize = formInput.attr('data-max-size');
        let imageUploaderInitialMaxFiles = formInput.attr('data-max-files');
        let imageUploaderPreload = formInput.attr('data-preload');
        let imageUploaderLabel = formInput.attr('data-label') !== undefined ? formInput.attr('data-label') : 'Drag & Drop files here or click to browse';
        let imageCapture = formInput.attr('data-capture') !== undefined ? formInput.attr('data-capture') : '';
        let mimes = formInput.attr('data-mimes') !== undefined ? formInput.attr('data-mimes') : '';

        if (defaultGallerySupported) {
            if (typeof $.fn.imageUploader !== 'undefined') {
                formInput.addClass('image-uploader');

                let imageUploaders = typeof (window.growtype_form_image_uploaders) !== 'undefined' && imageUploaderPreload ? window.growtype_form_image_uploaders : [];
                let preloaded = [];

                let uploaderSettings = {
                    imagesInputName: imageUploaderInitialName,
                    extensions: imageUploaderInitialExtension,
                    maxSize: imageUploaderInitialMaxSize,
                    maxFiles: imageUploaderInitialMaxFiles ? imageUploaderInitialMaxFiles : 1,
                    label: imageUploaderLabel
                };

                if (Object.entries(imageUploaders).length > 0) {
                    let imageUploaderContent = JSON.parse(imageUploaders['content']);

                    if (imageUploaderContent[imageUploaderInitialName] && Object.entries(imageUploaderContent[imageUploaderInitialName]).length > 0) {
                        preloaded = imageUploaderContent[imageUploaderInitialName];
                    }

                    uploaderSettings['preloaded'] = preloaded;
                    uploaderSettings['preloadedInputName'] = imageUploaders['old_images_prefix'] + '_' + imageUploaderInitialName;
                }

                if (imageCapture) {
                    uploaderSettings.capture = imageCapture;
                }

                if (mimes) {
                    uploaderSettings.mimes = mimes;
                }

                formInput.imageUploader(uploaderSettings);

                /**
                 * Format label
                 */
                formInput.find('.upload-text span:first').html(formInput.find('.upload-text span:first').text())
            }
        } else {
            $('<input multiple type="file" class="upload-multifile with-preview" className="multi" name="' + imageUploaderInitialName + '[]"/>').insertAfter(formInput);

            formInput.MultiFile({
                max: 10,
                accept: imageUploaderInitialExtension.join(','),
                max_size: imageUploaderInitialMaxSize,
            });
        }
    });
}
