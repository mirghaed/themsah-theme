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

        // Subtabs (custom CSS)
        $(document).on('click', '.themsah-subtab-nav li', function(){
            var sub = $(this).data('subtab');
            $(this).closest('.themsah-subtabs').find('.themsah-subtab-nav li').removeClass('active');
            $(this).addClass('active');
            $(this).closest('.themsah-subtabs').find('.themsah-subtab-content').removeClass('active');
            var target = $('#' + sub).addClass('active');
            // Refresh CodeMirror inside the newly opened pane
            target.find('.CodeMirror').each(function(){
                try { this.CodeMirror && this.CodeMirror.refresh(); } catch(e){}
            });
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
            var multiple = false;
            var insideFonts = btn.closest('#tab-fonts').length > 0 || btn.closest('.themsah-font-family').length > 0;
            if ( btn.data('target') === 'header_logo_image' ) {
                title = 'انتخاب تصویر لوگو';
                libTypes = ['image'];
            } else if ( btn.data('target') === 'woff' || btn.data('target') === 'woff2' || insideFonts ) {
                title = 'انتخاب فایل فونت';
                libTypes = ['application/font-woff','application/font-woff2','application/octet-stream'];
            } else if ( btn.data('multiple') ) {
                multiple = true; title = 'انتخاب تصاویر'; libTypes = ['image'];
            } else if ( btn.data('library') === 'video' ) {
                libTypes = ['video']; title = 'انتخاب ویدئو';
            }
            mediaFrame = wp.media({
                title: title,
                multiple: multiple,
                library: libTypes ? { type: libTypes } : undefined
            });
            mediaFrame.on('select', function(){
                var target = btn.data('target');
                var input = target ? $('#'+target) : btn.closest('td').find('input.themsah-media-url');
                if ( multiple ) {
                    var urls = [];
                    mediaFrame.state().get('selection').each(function(a){ urls.push(a.toJSON().url); });
                    if ( input && input.length ) input.val(urls.join(','));
                } else {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    if ( input && input.length ) input.val(attachment.url);
                }
            });
            mediaFrame.open();
        });

        // Families: add new family
        $('#themsah-font-family-add').on('click', function(e){
            e.preventDefault();
            var fi = $('#themsah-fonts-families .themsah-font-family').length;
            var html = '<div class="themsah-font-family" data-index="'+fi+'">\
                <div class="family-head">\
                    <strong>خانواده فونت</strong>\
                    <button class="button button-link-delete themsah-font-family-remove" type="button">&times;</button>\
                </div>\
                <table class="form-table">\
                    <tr>\
                        <th>نام خانواده</th>\
                        <td><input type="text" class="regular-text" name="themsah_theme_options[custom_fonts_list]['+fi+'][family]" placeholder="مثال: IRANSansX" /></td>\
                    </tr>\
                    <tr>\
                        <th>نوع فونت</th>\
                        <td>\
                            <label><input type="radio" name="themsah_theme_options[custom_fonts_list]['+fi+'][type]" value="static" class="family-type-radio" checked> ساده</label>\
                            &nbsp; &nbsp;\
                            <label><input type="radio" name="themsah_theme_options[custom_fonts_list]['+fi+'][type]" value="variable" class="family-type-radio"> Variable</label>\
                        </td>\
                    </tr>\
                </table>\
                <div class="family-static-fields" style="display:block">\
                    <table class="form-table themsah-fonts-repeater" data-family-index="'+fi+'">\
                        <thead>\
                            <tr>\
                                <th>وزن</th>\
                                <th>WOFF2</th>\
                                <th>WOFF</th>\
                                <th></th>\
                            </tr>\
                        </thead>\
                        <tbody></tbody>\
                    </table>\
                    <p><button class="button themsah-font-add-weight" type="button" data-family-index="'+fi+'">+ افزودن وزن فونت</button></p>\
                </div>\
                <div class="family-variable-fields" style="display:none">\
                    <table class="form-table">\
                        <tr>\
                            <th>بازه وزن</th>\
                            <td>\
                                <input type="number" min="1" max="1000" step="1" name="themsah_theme_options[custom_fonts_list]['+fi+'][min]" value="100" style="width:100px"> -\
                                <input type="number" min="1" max="1000" step="1" name="themsah_theme_options[custom_fonts_list]['+fi+'][max]" value="900" style="width:100px">\
                                <p class="description">معمولاً 100 تا 900</p>\
                            </td>\
                        </tr>\
                        <tr>\
                            <th>فایل WOFF2</th>\
                            <td>\
                                <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list]['+fi+'][woff2]" />\
                                <button class="button themsah-media-upload" type="button">انتخاب</button>\
                            </td>\
                        </tr>\
                        <tr>\
                            <th>فایل WOFF (اختیاری)</th>\
                            <td>\
                                <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list]['+fi+'][woff]" />\
                                <button class="button themsah-media-upload" type="button">انتخاب</button>\
                            </td>\
                        </tr>\
                    </table>\
                </div>\
            </div>';
            $('#themsah-fonts-families').append(html);
        });

        $(document).on('click', '.themsah-font-family-remove', function(e){
            e.preventDefault();
            $(this).closest('.themsah-font-family').remove();
        });

        // Toggle type fields
        $(document).on('change', '.family-type-radio', function(){
            var wrap = $(this).closest('.themsah-font-family');
            var type = $(this).val();
            if ( type === 'variable' ) {
                wrap.find('.family-static-fields').hide();
                wrap.find('.family-variable-fields').show();
            } else {
                wrap.find('.family-variable-fields').hide();
                wrap.find('.family-static-fields').show();
            }
        });

        // Add weight row per family
        $(document).on('click', '.themsah-font-add-weight', function(e){
            e.preventDefault();
            var fi = $(this).data('family-index');
            var tbody = $('.themsah-fonts-repeater[data-family-index="'+fi+'"]').find('tbody');
            var index = tbody.find('tr').length;
            var row = $('<tr>\
                <td>\
                    <select name="themsah_theme_options[custom_fonts_list]['+fi+'][weights]['+index+'][weight]">\
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
                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list]['+fi+'][weights]['+index+'][woff2]" />\
                    <button class="button themsah-media-upload" type="button">انتخاب</button>\
                </td>\
                <td>\
                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list]['+fi+'][weights]['+index+'][woff]" />\
                    <button class="button themsah-media-upload" type="button">انتخاب</button>\
                </td>\
                <td><button class="button button-link-delete themsah-font-remove" type="button">&times;</button></td>\
            </tr>');
            tbody.append(row);
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

        // Initialize CodeMirror editors if available
        function resizeCodeMirror(cm){
            try {
                var lc = cm.lineCount();
                var lh = parseFloat(window.getComputedStyle(cm.getWrapperElement()).lineHeight) || 18;
                var height = Math.max(360, (lc + 1) * lh + 24);
                cm.setSize('100%', height + 'px');
                cm.refresh();
            } catch(e){}
        }
        function refreshLater(cm){
            [0, 60, 180].forEach(function(t){ setTimeout(function(){ try{ resizeCodeMirror(cm); }catch(e){} }, t); });
        }
        function ensureRendered(cm){
            var attempts = 0;
            var timer = setInterval(function(){
                attempts++;
                try {
                    var el = cm.getWrapperElement();
                    if ( el && el.offsetWidth > 0 && el.offsetHeight > 0 ) {
                        resizeCodeMirror(cm);
                        clearInterval(timer);
                    }
                } catch(e){}
                if ( attempts > 20 ) clearInterval(timer);
            }, 80);
        }

        if ( window.wp && wp.codeEditor ) {
            $('.codemirror-css, .codemirror-js').each(function(){
                var settings = wp.codeEditor.defaultSettings ? JSON.parse(JSON.stringify(wp.codeEditor.defaultSettings)) : { codemirror:{} };
                if (!settings.codemirror) settings.codemirror = {};
                if ( $(this).hasClass('codemirror-css') ) settings.codemirror.mode = 'css';
                if ( $(this).hasClass('codemirror-js') ) settings.codemirror.mode = 'javascript';
                settings.codemirror.lineNumbers = true;
                settings.codemirror.direction = 'ltr';
                settings.codemirror.viewportMargin = Infinity; // auto-height
                settings.codemirror.rtlMoveVisually = true;
                settings.codemirror.gutters = ["CodeMirror-linenumbers"];
                settings.codemirror.lineWrapping = false;
                var cm = wp.codeEditor.initialize( this, settings );
                // Force refresh to fix single-line overlay bug
                setTimeout(function(){ try { if ( cm && cm.codemirror ) { resizeCodeMirror(cm.codemirror); } } catch(e){} }, 0);
                setTimeout(function(){ try { if ( cm && cm.codemirror ) { resizeCodeMirror(cm.codemirror); } } catch(e){} }, 120);
                try {
                    if ( cm && cm.codemirror ) {
                        $(this).data('themsah-cm', cm.codemirror);
                        cm.codemirror.on('changes', function(inst){ resizeCodeMirror(inst); });
                        $(window).on('resize', function(){ resizeCodeMirror(cm.codemirror); });
                        // Extra ensure on fonts/css ready
                        refreshLater(cm.codemirror);
                        ensureRendered(cm.codemirror);
                    }
                } catch(e){}
            });
        }

        // Final pass after page fully loaded to be sure editors show contents
        $(window).on('load', function(){
            $('.CodeMirror').each(function(){ try{ this.CodeMirror && resizeCodeMirror(this.CodeMirror); }catch(e){} });
        });

        // When switching main tabs (sidebar tabs), refresh editors in the active panel
        $(document).on('click', '.themsah-tab-nav li', function(){
            setTimeout(function(){
                $('.themsah-tab-content.active .CodeMirror').each(function(){ try{ this.CodeMirror && resizeCodeMirror(this.CodeMirror); }catch(e){} });
            }, 10);
        });
    });
})(jQuery);


