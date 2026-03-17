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
            extensions: ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.mp4', '.webm', '.mov', '.mp3', '.wav'],
            mimes: ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime', 'audio/mpeg', 'audio/wav'],
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
                            true,
                            plugin.settings.preloaded[i]
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

            let isVideo = false;
            if (fileMetadata && fileMetadata.type && fileMetadata.type.startsWith('video/')) {
                isVideo = true;
            } else if (src && (typeof src === 'string' && (src.match(/\.(mp4|webm|mov)(\?.*)?$/i) || src.startsWith('blob:')))) {
                // For blobls we rely on metadata, but if we have a blob and metadata says video, it's video.
                // If it's a preloaded src with video extension, it's video.
                if (src.startsWith('blob:') && fileMetadata && fileMetadata.type && fileMetadata.type.startsWith('video/')) {
                    isVideo = true;
                } else if (!src.startsWith('blob:') && src.match(/\.(mp4|webm|mov)(\?.*)?$/i)) {
                    isVideo = true;
                }
            }

            let isAudio = false;
            if (fileMetadata && fileMetadata.type && fileMetadata.type.startsWith('audio/')) {
                isAudio = true;
            } else if (src && typeof src === 'string' && src.match(/\.(mp3|ogg|wav|m4a)(\?.*)?$/i)) {
                isAudio = true;
            }

            if (isAudio) {
                $('<audio>', { controls: true, class: 'uploaded-audio-preview' })
                    .append($('<source>', { src: src, type: (fileMetadata && fileMetadata.type) || 'audio/mpeg' }))
                    .appendTo($container);
                $container.addClass('is-audio');
            } else if (isVideo || (fileMetadata && fileMetadata.is_video)) {
                $('<video>', { src: src, muted: true, playsinline: true }).appendTo($container);
                $container.addClass('is-video');
            } else {
                $('<img>', { src: src }).appendTo($container);
            }
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
                if (!validateDuplicate(file)) return;

                // Use standard Array.prototype.some for better compatibility check or find
                // Check if already in current validFiles batch
                var isDuplicateBatch = false;
                for (var j = 0; j < validFiles.length; j++) {
                    if (validFiles[j].name === file.name && validFiles[j].size === file.size && validFiles[j].lastModified === file.lastModified) {
                        isDuplicateBatch = true;
                        break;
                    }
                }
                if (isDuplicateBatch) return;

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

        let validateDuplicate = file => {
            // Check pending uploads
            for (let i = 0; i < dataTransfer.files.length; i++) {
                if (dataTransfer.files[i].name === file.name && dataTransfer.files[i].size === file.size && dataTransfer.files[i].lastModified === file.lastModified) {
                    alert(`File "${file.name}" has already been selected.`);
                    return false;
                }
            }

            // Normalization helpers
            const normalize = name => name.toLowerCase().replace(/[-_]\d+(\.[a-z0-9]+)$/i, '$1');
            const simplify = name => name.toLowerCase().replace(/[^a-z0-9]/g, '');

            // Check preloaded images
            if (plugin.settings.preloaded) {
                for (let i = 0; i < plugin.settings.preloaded.length; i++) {
                    let preload = plugin.settings.preloaded[i];

                    let pName = preload.name || '';
                    let fName = file.name || '';

                    // Name check: exact, normalized (clean suffix), or simplified (alphanumeric only)
                    let isNameMatch = (pName === fName) ||
                        (normalize(pName) === normalize(fName)) ||
                        (simplify(pName) === simplify(fName));

                    // Size check: exact byte match
                    let isSizeMatch = (preload.size && parseInt(preload.size) === file.size);

                    console.log('--- Checking Duplicate ---');
                    console.log('Uploading:', fName, file.size);
                    console.log('Preloaded:', pName, preload.size);
                    console.log('Is Name Match:', isNameMatch);
                    console.log('Is Size Match:', isSizeMatch);

                    if (isNameMatch || isSizeMatch) {
                        alert(`File "${file.name}" has already been uploaded.`);
                        return false;
                    }
                }
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
                // Pass file metadata (name, size, type) to createImg
                $uploadedContainer.append(plugin.createImg(imgSrc, dataTransfer.items.length - 1, false, {
                    name: file.name,
                    size: file.size,
                    type: file.type
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
