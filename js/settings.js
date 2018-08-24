(function ($) {
    $.stock_settings = {
        options: {},
        init: function (options) {
            this.options = options;
            this.initButtons();
            this.initRouteSelector();
            this.initScroll();
            return this;
        },
        initScroll: function () {
            $(window).scroll(function () {
                var item = $('.field-group.submit');
                var form_bottom_position = $('#plugins-settings-form').offset().top + $('#plugins-settings-form').height();
                var scroll_bottom = $(this).scrollTop() + $(window).height();
                if (form_bottom_position - scroll_bottom > 120 && !item.hasClass("fixed")) {
                    item.hide();
                    item.addClass("fixed").slideToggle(200);
                } else if (form_bottom_position - scroll_bottom < 100 && item.hasClass("fixed")) {
                    item.removeClass("fixed");
                }
            }).scroll();
        },
        initButtons: function () {
            $('#ibutton-status').iButton({
                labelOn: "Вкл", labelOff: "Выкл"
            }).change(function () {
                var self = $(this);
                var enabled = self.is(':checked');
                if (enabled) {
                    self.closest('.field-group').siblings().show(200);
                } else {
                    self.closest('.field-group').siblings().hide(200);
                }
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });
            $(document).on('click', '.helper-link', function () {
                $(this).closest('.field-group').find('.help-content').slideToggle('slow');
                $(this).find('i.icon10').toggleClass('darr-tiny').toggleClass('uarr-tiny');
                return false;
            });
            $(document).keydown(function (e) {
                // ctrl + s
                if (e.ctrlKey && e.keyCode == 83) {
                    $('#plugins-settings-form').submit();
                    return false;
                }
            });


        },
        initRouteSelector: function () {
            var templates = this.options.templates;
            $('#route-selector').change(function () {
                var self = $(this);
                var loading = $('<i class="icon16 loading"></i>');
                $(this).attr('disabled', true);
                $(this).after(loading);
                $('.route-container').find('input,select,textarea').attr('disabled', true);
                $('.route-container').slideUp('slow');
                $.get('?plugin=stock&module=settings&action=route&route_hash=' + $(this).val(), function (response) {
                    $('.route-container').html(response);
                    loading.remove();
                    self.removeAttr('disabled');
                    $('.route-container').slideDown('slow');

                    $('.route-container .ibutton').iButton({
                        labelOn: "Вкл",
                        labelOff: "Выкл",
                        className: 'mini'
                    });

                    for (var i = 0; i < templates.length; i++) {
                        CodeMirror.fromTextArea(document.getElementById(templates[i].id), {
                            mode: "text/" + templates[i].mode,
                            tabMode: "indent",
                            height: "dynamic",
                            lineWrapping: true,
                            onChange: function (c) {
                                c.save();
                            }
                        });
                    }

                    $('.template-block').hide();
                    $('.edit-template').click(function () {
                        $(this).closest('.field').find('.template-block').slideToggle('slow');
                        return false;
                    });
                    $('.templates-block').hide();
                    $('.edit-templates').click(function () {
                        $(this).closest('.field-group').find('.templates-block').slideToggle('slow');
                        return false;
                    });

                    $('input[name="shop_stock[countdown_style]"]').change(function () {
                        if ($('input[name="shop_stock[countdown_style]"]:checked').val() == 'boring') {
                            $('.only-boring').show();
                        } else {
                            $('.only-boring').hide();
                        }
                    });
                    $('input[name="shop_stock[countdown_style]"]').change();

                    $('.color').each(function () {
                        var input = $(this);
                        var replacer = $('<span class="color-replacer">' +
                                '<i class="icon16 color" style="background: #' + input.val().substr(1) + '"></i>' +
                                '</span>').insertAfter(input);
                        var picker = $('<div style="display:none;" class="color-picker"></div>').
                                insertAfter(replacer);
                        var farbtastic = $.farbtastic(picker, function (color) {
                            replacer.find('i').css('background', color);
                            input.val(color);
                        });
                        farbtastic.setColor('#' + input.val());
                        replacer.click(function () {
                            picker.slideToggle(200);
                            return false;
                        });
                        var timer_id;
                        input.unbind('keydown').bind('keydown', function () {
                            if (timer_id) {
                                clearTimeout(timer_id);
                            }
                            timer_id = setTimeout(function () {
                                farbtastic.setColor(input.val());
                            }, 250);
                        });
                    });


                    $('#ibutton-stock-page').iButton({
                        labelOn: "", labelOff: "", className: 'mini'
                    }).change(function () {
                        var self = $(this);
                        var enabled = self.is(':checked');
                        if (enabled) {
                            $('.stock-page-settings').show(200);
                        } else {
                            $('.stock-page-settings').hide(200);
                        }
                    });

                });
                return false;
            }).change();
        }
    };
})(jQuery);
