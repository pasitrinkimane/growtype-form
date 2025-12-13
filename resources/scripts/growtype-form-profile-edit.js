jQuery(document).ready(function ($) {
    $(".growtype-form input[type='file']").on("change", function () {
        var formData = new FormData();

        formData.append("action", growtypeProfileUpload.action);
        formData.append("_wpnonce", growtypeProfileUpload.nonce);

        var input = this;
        var fieldName = $(this).attr("name") || "files"; // fallback

        if (input.files && input.files.length > 0) {

            $.each(input.files, function (i, file) {
                if (fieldName.endsWith("[]")) {
                    formData.append(fieldName, file);
                } else {
                    formData.append(fieldName + "[]", file);
                }

            });
        }

        $.ajax({
            url: growtypeProfileUpload.ajaxUrl,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response['data']['message']);
                }
            },
            error: function () {
                alert("Error uploading files. Please try again.");
            }
        });

    });

    // Handle photo removal
    $(".growtype-form-remove-profile-image").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (confirm("Are you sure you want to remove your profile photo?")) {
            $.ajax({
                url: growtypeProfileUpload.ajaxUrl,
                type: "POST",
                data: {
                    action: "growtype_form_remove_profile_picture",
                    _wpnonce: growtypeProfileUpload.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert("Error removing photo. Please try again.");
                    }
                },
                error: function () {
                    alert("Error removing photo. Please try again.");
                }
            });
        }
    });

    /**
     * Input type tel
     */
    var phoneInput = document.querySelector('.growtype-form input[name="phone"]');
    if (!phoneInput) return;

    var iti = window.intlTelInput(phoneInput, {
        initialCountry: growtypeProfileUpload.initialCountry,
        separateDialCode: true,
    });

    $(phoneInput).on('countrychange input', function () {
        var countryData = iti.getSelectedCountryData();

        let iso2 = countryData.iso2;
        let dialCode = countryData.dialCode;

        if (iso2) {
            $(this).closest('.growtype-form').find('input[name="phone_country_iso"]').val(iso2);
            $(this).closest('.growtype-form').find('input[name="phone_country_code"]').val(dialCode);

            $.ajax({
                url: growtypeProfileUpload.ajaxUrl,
                type: "POST",
                data: {
                    action: "growtype_form_profile_edit_form_data",
                    phone_country_code: dialCode,  // e.g. "1"
                    phone_country_iso: iso2,      // e.g. "us"
                    _wpnonce: growtypeProfileUpload.nonce
                },
                success: function (response) {
                    if (!response.success) {
                        console.warn("Phone save failed:", response);
                    }
                }
            });
        }
    });
});
