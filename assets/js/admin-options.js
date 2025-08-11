(function($){
    $(document).ready(function(){
        if ( typeof $.fn.wpColorPicker === 'function' ) {
            $('.themsah-color-field').wpColorPicker({
                palettes: ['#2663ff','#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#111827']
            });
        }

        // Tabs
        $('.themsah-tab-nav li').on('click', function(){
            var tab = $(this).data('tab');
            $('.themsah-tab-nav li').removeClass('active');
            $(this).addClass('active');
            $('.themsah-tab-content').removeClass('active');
            $('#' + tab).addClass('active');
        });

        // Media uploader for repeater rows
        var mediaFrame;
        $(document).on('click', '.themsah-media-upload', function(e){
            e.preventDefault();
            var btn = $(this);
            if ( mediaFrame ) {
                mediaFrame.open();
                mediaFrame.off('select');
            }
            var title = 'انتخاب فایل';
            var libTypes = null; // null shows all media library items
            if ( btn.data('target') === 'woff' || btn.data('target') === 'woff2' || btn.data('target') === 'ttf' ) {
                title = 'انتخاب فایل فونت';
                libTypes = ['application/font-woff','application/font-woff2','font/ttf','application/octet-stream'];
            } else if ( btn.data('target') === 'header_logo_image' ) {
                title = 'انتخاب تصویر لوگو';
                libTypes = ['image'];
            }
            mediaFrame = wp.media({
                title: title,
                multiple: false,
                library: libTypes ? { type: libTypes } : undefined
            });
            mediaFrame.on('select', function(){
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                var target = btn.data('target');
                var input;
                if ( target ) {
                    input = $('#'+target);
                } else {
                    input = btn.closest('td').find('input.themsah-media-url');
                }
                if ( input && input.length ) input.val(attachment.url);
            });
            mediaFrame.open();
        });

        // Repeater add/remove (new schema: custom_fonts[family], custom_fonts[weights][index][...])
        $('#themsah-font-add').on('click', function(e){
            e.preventDefault();
            var index = $('#themsah-fonts-repeater tbody tr').length;
            var row = $('<tr>\
                <td>\
                    <select name="themsah_theme_options[custom_fonts][weights]['+index+'][weight]">\
                        <option value="100">100</option>\
                        <option value="200">200</option>\
                        <option value="300">300</option>\
                        <option value="400" selected>400</option>\
                        <option value="500">500</option>\
                        <option value="600">600</option>\
                        <option value="700">700</option>\
                        <option value="800">800</option>\
                        <option value="900">900</option>\
                    </select>\
                </td>\
                <td>\
                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts][weights]['+index+'][woff2]" />\
                    <button class="button themsah-media-upload" data-target="woff2">انتخاب</button>\
                </td>\
                <td>\
                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts][weights]['+index+'][woff]" />\
                    <button class="button themsah-media-upload" data-target="woff">انتخاب</button>\
                </td>\
                <td>\
                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts][weights]['+index+'][ttf]" />\
                    <button class="button themsah-media-upload" data-target="ttf">انتخاب</button>\
                </td>\
                <td><button class="button button-link-delete themsah-font-remove">&times;</button></td>\
            </tr>');
            $('#themsah-fonts-repeater tbody').append(row);
        });

        $(document).on('click', '.themsah-font-remove', function(e){
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        // AJAX save
        $(document).on('submit', '#themsah-options-form', function(e){
            e.preventDefault();
            var $btn = $(this).find('input[type="submit"], button[type="submit"]');
            var original = $btn.val();
            $btn.prop('disabled', true).val((window.THEMSAH_OPTIONS && THEMSAH_OPTIONS.i18n ? THEMSAH_OPTIONS.i18n.saving : 'Saving...'));
            $.post(THEMSAH_OPTIONS.ajax_url, {
                action: 'themsah_save_options',
                nonce: THEMSAH_OPTIONS.nonce,
                serialized: $(this).serialize()
            }).done(function(resp){
                var ok = resp && resp.success;
                var i18n = (window.THEMSAH_OPTIONS && THEMSAH_OPTIONS.i18n) ? THEMSAH_OPTIONS.i18n : {saved:'Saved', error:'Error'};
                var msg = ok ? i18n.saved : (resp && resp.data && resp.data.message ? resp.data.message : i18n.error);
                if (!$('#themsah-save-toast').length) {
                    $('body').append('<div id="themsah-save-toast" class="themsah-toast"></div>');
                }
                $('#themsah-save-toast').text(msg).addClass(ok ? 'ok':'err').fadeIn(150).delay(1200).fadeOut(300);
            }).fail(function(){
                if (!$('#themsah-save-toast').length) {
                    $('body').append('<div id="themsah-save-toast" class="themsah-toast"></div>');
                }
                var i18n = (window.THEMSAH_OPTIONS && THEMSAH_OPTIONS.i18n) ? THEMSAH_OPTIONS.i18n : {error:'Error'};
                $('#themsah-save-toast').text(i18n.error).addClass('err').fadeIn(150).delay(1200).fadeOut(300);
            }).always(function(){
                $btn.prop('disabled', false).val(original);
            });
        });
    });
})(jQuery);


