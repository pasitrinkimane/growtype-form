/*! Image Uploader - Extended v1.2.3 - 2025/09/02
 * Original Copyright (c) 2019 Christian Bayer; Licensed MIT
 * Extended for external customization and child theme extensions
 */

(function ($) {
    $.fn.imageUploader = function (options) {

        // Default settings
        let defaults = {
            preloaded: [],
            imagesInputName: 'images',
            preloadedInputName: 'preloaded',
            label: 'Drag & Drop files here or click to browse',
            extensions: ['.jpg', '.jpeg', '.png', '.gif', '.svg'],
            mimes: ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'],
            maxSize: undefined,
            maxFiles: undefined,
            capture: null,
            filters: {} // new: optional external filters (e.g., featured, private)
        };

        let plugin = this; // plugin instance
        let dataTransfer = new DataTransfer();
        let $input;

        plugin.settings = {}; // will hold final settings

        // -------------------------------
        // Plugin constructor
        // -------------------------------
        plugin.init = function () {
            plugin.settings = $.extend(plugin.settings, defaults, options);

            plugin.each(function (i, wrapper) {
                let $container = createContainer();
                $(wrapper).append($container);

                // Drag & drop bindings
                $container.on("dragover", fileDragHover.bind($container));
                $container.on("dragleave", fileDragHover.bind($container));
                $container.on("drop", fileSelectHandler.bind($container));

                // Preloaded images
                if (plugin.settings.preloaded.length) {
                    $container.addClass('has-files');
                    let $uploadedContainer = $container.find('.uploaded');
                    for (let i = 0; i < plugin.settings.preloaded.length; i++) {
                        $uploadedContainer.append(plugin.createImg(
                            plugin.settings.preloaded[i].src,
                            plugin.settings.preloaded[i].id,
                            true
                        ));
                    }
                    $uploadedContainer.sortable({
                        update: function (event, ui) {
                            updateImageOrder($container);
                        }
                    });
                    // Initial order update
                    updateImageOrder($container);
                }
            });
        };

        // -------------------------------
        // Container creation
        // -------------------------------
        let createContainer = function () {
            let $container = $('<div>', { class: 'image-uploader-inner' });

            let inputParams = {
                type: 'file',
                id: plugin.settings.imagesInputName + '-' + random(),
                name: plugin.settings.imagesInputName + '[]',
                accept: plugin.settings.extensions.join(','),
                multiple: ''
            };
            if (plugin.settings.capture) inputParams.capture = plugin.settings.capture;

            $input = $('<input>', inputParams).appendTo($container);

            // Create hidden input for image order
            let $orderInput = $('<input>', {
                type: 'hidden',
                name: plugin.settings.imagesInputName + '_order',
                class: 'image-order-input'
            }).appendTo($container);



            let $uploadedContainer = $('<div>', { class: 'uploaded' }).appendTo($container);
            let $textContainer = $('<div>', { class: 'upload-text' }).appendTo($container);
            $('<i>', { class: 'iui-cloud-upload' }).appendTo($textContainer);
            $('<span>', { text: plugin.settings.label }).appendTo($textContainer);

            $container.on('click', function (e) {
                prevent(e);
                $input.trigger('click');
            });

            $input.on("click", e => e.stopPropagation());
            $input.on('change', fileSelectHandler.bind($container));

            return $container;
        };

        // -------------------------------
        // Prevent default
        // -------------------------------
        let prevent = e => { e.preventDefault(); e.stopPropagation(); };

        // -------------------------------
        // Update image order
        // -------------------------------
        let updateImageOrder = function ($container) {
            let order = [];
            $container.find('.uploaded .uploaded-image').each(function () {
                let $img = $(this);
                if ($img.data('preloaded')) {
                    // For preloaded images, use the hidden input value (image ID)
                    let imageId = $img.find('input[type="hidden"]').val();
                    order.push({ type: 'preloaded', id: imageId });
                } else {
                    // For new uploads, include index, name, and size
                    let index = $img.data('index');
                    let fileName = $img.attr('data-file-name');
                    let fileSize = $img.attr('data-file-size');
                    order.push({
                        type: 'new',
                        index: index,
                        name: fileName,
                        size: parseInt(fileSize)
                    });
                }
            });
            $container.find('.image-order-input').val(JSON.stringify(order));
        };

        // -------------------------------
        // Exposed: createImg
        // -------------------------------
        plugin.createImg = function (src, id, preloaded, fileMetadata) {
            let $container = $('<div>', { class: 'uploaded-image' });
            $('<img>', { src: src }).appendTo($container);
            let $button = $('<button>', { class: 'delete-image' }).appendTo($container);
            $('<i>', { class: 'iui-close' }).appendTo($button);

            if (preloaded) {
                $container.attr('data-preloaded', true);
                $('<input>', {
                    type: 'hidden',
                    name: plugin.settings.preloadedInputName + '[]',
                    value: id
                }).appendTo($container);
            } else {
                $container.attr('data-index', id);
                // Store file metadata if provided
                if (fileMetadata) {
                    $container.attr('data-file-name', fileMetadata.name);
                    $container.attr('data-file-size', fileMetadata.size);
                }
            }

            $container.on("click", e => prevent(e));

            $button.on("click", function (e) {
                prevent(e);
                let $parent = $container.parent();
                if ($container.data('preloaded')) {
                    plugin.settings.preloaded = plugin.settings.preloaded.filter(p => p.id !== id);
                } else {
                    let index = parseInt($container.data('index'));
                    $parent.find('.uploaded-image[data-index]').each((i, cont) => {
                        if (i > index) $(cont).attr('data-index', i - 1);
                    });
                    dataTransfer.items.remove(index);
                    $input.prop('files', dataTransfer.files);
                }
                $container.remove();
                if (!$parent.children().length) $parent.parent().removeClass('has-files');

                // Update image order after deletion
                updateImageOrder($parent.parent());
            });

            // ----- EXTENSION POINT -----
            // Child themes or external scripts can hook here
            $(document).trigger('imageUploader:createImg', [$container, src, id, preloaded, plugin]);

            return $container;
        };

        // -------------------------------
        // File drag hover
        // -------------------------------
        let fileDragHover = function (e) {
            prevent(e);
            $(this).toggleClass('drag-over', e.type === "dragover");
        };

        // -------------------------------
        // File selection handler
        // -------------------------------
        let fileSelectHandler = function (e) {
            prevent(e);
            let $container = $(this);
            let files = Array.from(e.target.files || e.originalEvent.dataTransfer.files);
            let validFiles = [];

            $(files).each((i, file) => {
                if (plugin.settings.extensions && !validateExtension(file)) return;
                if (plugin.settings.mimes && !validateMIME(file)) return;
                if (plugin.settings.maxSize && !validateMaxSize(file)) return;
                if (plugin.settings.maxFiles && !validateMaxFiles(validFiles.length, file)) return;
                validFiles.push(file);
            });

            if (validFiles.length) {
                $container.removeClass('drag-over');
                plugin.setPreview($container, validFiles);
            } else {
                $input.prop('files', dataTransfer.files);
            }
        };

        // -------------------------------
        // Validations
        // -------------------------------
        let validateExtension = file => {
            if (plugin.settings.extensions.indexOf(file.name.replace(/^.*\./, '.')) < 0) {
                alert(`File "${file.name}" does not match accepted extensions: ${plugin.settings.extensions.join(', ')}`);
                return false;
            }
            return true;
        };

        let validateMIME = file => {
            if (plugin.settings.extensions.includes('.pdf')) plugin.settings.mimes.push('application/pdf');
            if (plugin.settings.mimes.indexOf(file.type) < 0) {
                alert(`File "${file.name}" does not match accepted mime types: ${plugin.settings.mimes.join(', ')}`);
                return false;
            }
            return true;
        };

        let validateMaxSize = file => {
            if (file.size > plugin.settings.maxSize) {
                alert(`File "${file.name}" exceeds max size of ${plugin.settings.maxSize / 1024 / 1024}Mb`);
                return false;
            }
            return true;
        };

        let validateMaxFiles = (index, file) => {
            if ((index + dataTransfer.items.length + plugin.settings.preloaded.length) >= plugin.settings.maxFiles) {
                alert(`File "${file.name}" could not be added because limit of ${plugin.settings.maxFiles} files reached`);
                return false;
            }
            return true;
        };

        // -------------------------------
        // Exposed: setPreview
        // -------------------------------
        plugin.setPreview = function ($container, files) {
            $container.addClass('has-files');
            let $uploadedContainer = $container.find('.uploaded');
            let $input = $container.find('input[type="file"]');
            let imgSrc = '';

            $(files).each((i, file) => {
                dataTransfer.items.add(file);
                imgSrc = file.type === 'application/pdf' ? window.growtype_form.public_url + 'images/pdf.png' : URL.createObjectURL(file);
                // Pass file metadata (name and size) to createImg
                $uploadedContainer.append(plugin.createImg(imgSrc, dataTransfer.items.length - 1, false, {
                    name: file.name,
                    size: file.size
                }));
            });

            $input.prop('files', dataTransfer.files);

            // Configure sortable with update callback if not already configured
            if (!$uploadedContainer.hasClass('ui-sortable')) {
                $uploadedContainer.sortable({
                    update: function (event, ui) {
                        updateImageOrder($container);
                    }
                });
            }

            // Update image order after adding new images
            updateImageOrder($container);

            // ----- EXTENSION POINT -----
            $(document).trigger('imageUploader:setPreview', [$container, files, plugin]);
        };

        // -------------------------------
        // Helpers
        // -------------------------------
        let random = () => Date.now() + Math.floor(Math.random() * 100 + 1);

        this.init();
        return this;
    };

}(jQuery));
