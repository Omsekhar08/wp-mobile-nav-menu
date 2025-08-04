(function($) {
    'use strict';

    $(document).ready(function() {
        initAdminSettings();
    });

    function initAdminSettings() {
        initColorPickers();
        initMenuItemsManagement();
        initStylePresets();
        initLivePreview();
        initImportExport();
        initFormHandling();
        initSliders();
    }

    function initColorPickers() {
        $('.wp-color-picker').wpColorPicker({
            change: function() {
                updateLivePreview();
            }
        });
    }

    function initMenuItemsManagement() {
        let menuItemIndex = $('.menu-item-row').length;

        // Add menu item
        $('#add-menu-item').on('click', function() {
            const template = $('#menu-item-template').html();
            const html = template.replace(/\{\{index\}\}/g, menuItemIndex);
            $('#menu-items-list').append(html);
            menuItemIndex++;
            updateLivePreview();
        });

        // Remove menu item
        $(document).on('click', '.remove-menu-item', function() {
            $(this).closest('.menu-item-row').fadeOut(300, function() {
                $(this).remove();
                updateLivePreview();
            });
        });

        // Make menu items sortable
        $('#menu-items-list').sortable({
            handle: '.menu-item-handle',
            placeholder: 'menu-item-placeholder',
            update: function() {
                updateMenuItemIndices();
                updateLivePreview();
            }
        });

        // Menu item type change
        $(document).on('change', '.menu-item-type', function() {
            const $row = $(this).closest('.menu-item-row');
            const type = $(this).val();
            const $urlField = $row.find('input[type="url"]');
            
            if (type.startsWith('woocommerce_')) {
                $urlField.hide();
            } else {
                $urlField.show();
            }
        });
    }

    function updateMenuItemIndices() {
        $('.menu-item-row').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    function initStylePresets() {
        $('.style-preset input[type="radio"]').on('change', function() {
            $('.style-preset').removeClass('selected');
            $(this).closest('.style-preset').addClass('selected');
            updateLivePreview();
        });

        // Handle pro style clicks
        $('.style-preset.pro-style').on('click', function(e) {
            e.preventDefault();
            showProNotice('Advanced styling options (Styles 8-12) are available in the Pro version.');
        });
    }

    function initLivePreview() {
        // Update preview on any form change
        $('form input, form select, form textarea').on('change keyup', function() {
            clearTimeout(window.previewTimeout);
            window.previewTimeout = setTimeout(updateLivePreview, 300);
        });

        // Initial preview load
        updateLivePreview();
    }

    function updateLivePreview() {
        const settings = getFormSettings();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_mnb_get_preview',
                settings: settings,
                nonce: wp_mnb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#wp-mnb-preview').html(response.data.preview);
                }
            }
        });
    }

    function getFormSettings() {
        const settings = {};
        
        // Get all form fields
        $('form input, form select, form textarea').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            const type = $(this).attr('type');
            
            if (name && name.startsWith('wp_mnb_settings')) {
                const key = name.replace('wp_mnb_settings[', '').replace(']', '');
                
                if (type === 'checkbox') {
                    settings[key] = $(this).is(':checked');
                } else if (type === 'radio') {
                    if ($(this).is(':checked')) {
                        settings[key] = value;
                    }
                } else {
                    settings[key] = value;
                }
            }
        });

        // Get menu items
        settings.menu_items = [];
        $('.menu-item-row').each(function() {
            const item = {};
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                const type = $(this).attr('type');
                
                if (name) {
                    const key = name.split('[').pop().replace(']', '');
                    if (type === 'checkbox') {
                        item[key] = $(this).is(':checked');
                    } else {
                        item[key] = value;
                    }
                }
            });
            settings.menu_items.push(item);
        });

        return settings;
    }

    function initImportExport() {
        // Export settings
        $('#export-settings').on('click', function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_mnb_export_settings',
                    nonce: wp_mnb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        downloadJSON(response.data.settings, response.data.filename);
                        showMessage('Settings exported successfully!', 'success');
                    }
                }
            });
        });

        // Import settings
        $('#import-settings').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    importSettings(settings);
                } catch (error) {
                    showMessage('Invalid settings file!', 'error');
                }
            };
            reader.readAsText(file);
        });

        // Demo imports
        $('[id^="import-demo-"]').on('click', function() {
            const demoId = $(this).attr('id').replace('import-demo-', '');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_mnb_import_demo',
                    demo_id: demoId,
                    nonce: wp_mnb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        showMessage('Failed to import demo!', 'error');
                    }
                }
            });
        });
    }

    function downloadJSON(data, filename) {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function importSettings(settings) {
        // Populate form with imported settings
        Object.keys(settings).forEach(key => {
            const $field = $('[name="wp_mnb_settings[' + key + ']"]');
            const value = settings[key];
            
            if ($field.length) {
                if ($field.attr('type') === 'checkbox') {
                    $field.prop('checked', !!value);
                } else if ($field.attr('type') === 'radio') {
                    $field.filter('[value="' + value + '"]').prop('checked', true);
                } else {
                    $field.val(value);
                }
            }
        });

        // Handle menu items import
        if (settings.menu_items) {
            $('#menu-items-list').empty();
            settings.menu_items.forEach((item, index) => {
                addMenuItemRow(item, index);
            });
        }

        // Update color pickers
        $('.wp-color-picker').wpColorPicker('color', settings.bg_color || '#ffffff');
        
        updateLivePreview();
        showMessage('Settings imported successfully!', 'success');
    }

    function addMenuItemRow(item, index) {
        const template = $('#menu-item-template').html();
        const html = template.replace(/\{\{index\}\}/g, index);
        const $row = $(html);
        
        // Populate fields
        $row.find('[name$="[label]"]').val(item.label || '');
        $row.find('[name$="[icon]"]').val(item.icon || '');
        $row.find('[name$="[type]"]').val(item.type || 'custom');
        $row.find('[name$="[url]"]').val(item.url || '');
        $row.find('[name$="[enabled]"]').prop('checked', item.enabled !== false);
        
        $('#menu-items-list').append($row);
    }

    function initFormHandling() {
        // Save settings
        $('#wp-mnb-save-settings').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.val();
            
            $button.val('Saving...').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $('#wp-mnb-settings-form').serialize() + '&action=wp_mnb_save_settings',
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage('Failed to save settings!', 'error');
                    }
                },
                complete: function() {
                    $button.val(originalText).prop('disabled', false);
                }
            });
        });

        // Reset settings
        $('#wp-mnb-reset-settings').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to reset all settings to default?')) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_mnb_reset_settings',
                    nonce: wp_mnb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        showMessage('Failed to reset settings!', 'error');
                    }
                }
            });
        });
    }

    function initSliders() {
        $('.slider').on('input', function() {
            const value = $(this).val();
            $(this).siblings('.slider-value').text(value + 'px');
            updateLivePreview();
        });
    }

    function showMessage(message, type) {
        const $message = $('<div class="wp-mnb-message ' + type + '">' + message + '</div>');
        $('.wrap h1').after($message);
        
        setTimeout(() => {
            $message.fadeOut(() => $message.remove());
        }, 5000);
    }

    function showProNotice(message) {
        const $overlay = $('<div class="wp-mnb-pro-overlay">');
        const $notice = $('<div class="wp-mnb-pro-notice">');
        
        $notice.html(`
            <h4>🚀 Pro Feature</h4>
            <p>${message}</p>
            <a href="#" class="button button-primary">Upgrade to Pro - $29</a>
            <button type="button" class="button" id="close-pro-notice">Close</button>
        `);
        
        $overlay.append($notice);
        $('body').append($overlay);
        
        // Close handlers
        $overlay.on('click', function(e) {
            if (e.target === this) {
                $overlay.remove();
            }
        });
        
        $('#close-pro-notice').on('click', function() {
            $overlay.remove();
        });
    }

    // Icon upload handling
    function initIconUpload() {
        const $uploadArea = $('.icon-upload-area');
        const $fileInput = $('#custom-icon-upload');
        
        $uploadArea.on('click', () => $fileInput.click());
        
        $uploadArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        $uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        $uploadArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                uploadIcon(files[0]);
            }
        });
        
        $fileInput.on('change', function() {
            if (this.files.length) {
                uploadIcon(this.files[0]);
            }
        });
    }
    
    function uploadIcon(file) {
        if (!file.type.startsWith('image/')) {
            showMessage('Please select an image file!', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('icon', file);
        formData.append('action', 'wp_mnb_upload_icon');
        formData.append('nonce', wp_mnb_ajax.nonce);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    addUploadedIcon(response.data.url);
                    showMessage(response.data.message, 'success');
                } else {
                    showMessage('Upload failed!', 'error');
                }
            }
        });
    }
    
    function addUploadedIcon(url) {
        const $icon = $(`
            <div class="uploaded-icon" data-url="${url}">
                <img src="${url}" alt="Custom Icon">
                <button type="button" class="remove-icon">×</button>
            </div>
        `);
        
        $('.uploaded-icons').append($icon);
        
        $icon.on('click', function() {
            $('.uploaded-icon').removeClass('selected');
            $(this).addClass('selected');
            // Update current menu item icon field
            const iconUrl = $(this).data('url');
            $('.menu-item-row.editing').find('[name$="[icon]"]').val(iconUrl);
        });
        
        $icon.find('.remove-icon').on('click', function(e) {
            e.stopPropagation();
            $(this).closest('.uploaded-icon').remove();
        });
    }

    // Initialize icon upload if on pro version
    if (window.wpMnbProActive) {
        initIconUpload();
    }

})(jQuery);
