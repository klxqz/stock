$(function () {
    $('input[name="stock[restart]"]').change(function () {
        $('.restart-field-group').slideToggle('low');
    });

    $('input[name="stock[promocode_only]"]').change(function () {
        $('.promocode-field').slideToggle('low');
    });

    function uploadInit() {
        var url = $('.fileupload:first').data('action');
        var upload = $('.fileupload:first').parents('div.field');
        $('.fileupload:first').fileupload({
            url: url,
            dataType: 'json',
            start: function () {
                upload.find('.fileupload:first').hide();
                upload.find('.js-fileupload-progress').show();
            },
            done: function (e, data) {
                var response = data.jqXHR.responseJSON;
                if (response.status == 'ok') {
                    upload.find('.js-fileupload-progress').html('<i class="icon16 yes"></i>');
                    $('input[name="stock[img]"]').val(response.data.img);
                    $('.img-review').html('<img src="' + response.data.img_url + '"/>');
                    $('.delete-img').show();
                } else {
                    upload.find('.js-fileupload-progress').html('<i class="icon16 no"></i> ' + response.errors.join(','));
                }
                upload.find('.fileupload:first').show();
                setTimeout(function () {
                    upload.find('.js-fileupload-progress').empty();
                }, 3000);

            },
            fail: function (e, data) {
                upload.find('.js-fileupload-progress').html('<i class="icon16 no"></i>');
                upload.find('.fileupload:first').show();
            }
        });
    }
    uploadInit();

    $('.delete-img').click(function () {
        $.ajax({
            type: 'POST',
            url: '?plugin=stock&action=deleteStockImage',
            data: {
                img: $('input[name="stock[img]"]').val()
            },
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                if (data.status == 'ok') {
                    $('.img-review img').remove();
                    $('.delete-img').hide();
                } else {
                    alert(data.errors.join(', '));
                }
            },
            error: function (jqXHR, errorText) {
                alert(jqXHR.responseText);
            }
        });
        return false;
    });


    $('.feature-block b i').click(function () {
        $(this).toggleClass('darr').toggleClass('rarr');
        $(this).closest('.feature-block').find('.values').slideToggle('low');
    });


    $('input[name="stock[badge]"]').change(function () {
        if ($(this).val() == 'code') {
            $('.badge_code').slideDown('low');
        } else {
            $('.badge_code').slideUp('low');
        }
    });

    $('input[name="stock[name]"]').change(function () {
        if (!$('input[name="stock[page_name]"]').val()) {
            $('input[name="stock[page_name]"]').val($(this).val()).change();
        }
    });

    var locked = false;
    function  transliterate() {
        $.get('?action=transliterate&str=' + $('input[name="stock[page_name]"]').val(), function (response) {
            locked = false;
            if (response.status == 'ok') {
                $('input[name="stock[page_url]"]').val(response.data).change();
            }
        });
    }

    var is_transliterate = false;
    $('input[name="stock[page_url]"]').change(function () {
        if (!$(this).val()) {
            is_transliterate = true;
        }
        var url = frontend_url + page_url + $('input[name="stock[page_url]"]').val();
        $('.stock-frontend-url').attr('href', url);
        $('.stock-frontend-url').text(url);
    });
    $('input[name="stock[page_url]"]').change();

    $('input[name="stock[page_url]"]').keyup(function () {
        var url = frontend_url + page_url + $('input[name="stock[page_url]"]').val();
        $('.stock-frontend-url').attr('href', url);
        $('.stock-frontend-url').text(url);
    });

    $('input[name="stock[page_name]"]').keyup(function () {
        if (is_transliterate && !locked) {
            locked = true;
            transliterate();
        }
    });
    $('input[name="stock[page_name]"]').change(function () {
        if (is_transliterate) {
            transliterate();
        }
    });


    var gift_search = $('#gift-search');
    gift_search.autocomplete("destroy");
    gift_search.autocomplete({
        source: '?plugin=stock&action=skuAutocomplete',
        minLength: 3,
        delay: 300,
        select: function (event, ui) {
            $('input[name="stock[gift_sku_id]"]').val(ui.item.id);
            gift_search.val('');
            gift_search.next('a').remove();
            gift_search.after('<a target="_blank" href="?action=products#/product/' + ui.item.product_id + '/">' + ui.item.value + '</a>');
            return false;
        }
    });


    $('input[name="stock[type]"]').change(function () {
        var type = $('input[name="stock[type]"]:checked').val();
        $('.stock-type-field').hide();
        $('.stock-type-' + type).show();
    });
    $('input[name="stock[type]"]').change();

    $('input[name="stock[discount_type]"]').change(function () {
        var type = $('input[name="stock[discount_type]"]:checked').val();
        $('input[name="stock[discount_value]"]').attr('disabled', 'disabled');
        $('.discount-type-field').hide();

        $('.discount-type-' + type).find('input[name="stock[discount_value]"]').removeAttr('disabled');
        $('.discount-type-' + type).show();
    });
    $('input[name="stock[discount_type]"]').change();


    var products_search = $('#products-search');
    products_search.autocomplete("destroy");
    products_search.autocomplete({
        source: '?action=autocomplete',
        minLength: 3,
        delay: 300,
        select: function (event, ui) {
            var button = $('.add-stock-products-button[data-type="product"]');
            button.data('id', ui.item.id);
            button.data('name', ui.item.value);
            products_search.val(ui.item.value);
            return false;
        }
    });

    $('.add-stock-products-button').click(function () {
        var type_names = {"product": "Товар", "set": "Список", "category": "Категория", "type": "Тип товаров", "type": "Тип товаров", "feature": "Характеристика"};
        var icons = {"product": "folders", "set": "ss set", "category": "folder", "type": "ss pt box", "feature": "ss features-bw"};
        var urls = {"product": "?action=products#/product/", "set": "?action=products#/products/set_id=", "category": "?action=products#/products/category_id=", "type": "?action=products#/products/type_id=", "feature": "?action=settings#/features/"};
        var type = $(this).data('type');
        var id = $(this).data('id');
        var name = $(this).data('name');
        if (!id) {
            alert('Укажите товары участвующие в акции');
            return false;
        }

        if ($('#stock-products-table tr[data-type="' + type + '"][data-value="' + id + '"]').length) {
            alert(type_names[type] + ' "' + name + '" уже присутствует среди товаров акции');
        } else {
            var data = {
                type: type,
                type_name: type_names[type],
                icon: icons[type],
                url: (type != 'feature' ? urls[type] + id : urls[type]),
                name: name,
                value: id
            };
            $('#stock-product-tmpl').tmpl(data).appendTo('#stock-products-table tbody');
        }
        if ($(this).data('type') == 'product') {
            $('#products-search').val('');
            $(this).data('id', '');
            $(this).data('name', '');
        }
        return false;
    });
    $(document).on('click', '.delete-stock-products-button', function () {
        var tr = $(this).closest('tr');
        var id = tr.find('input[name="stock_products[id][]"]').val();
        if (id) {
            $.ajax({
                type: 'POST',
                url: '?plugin=stock&action=deleteStockProducts',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if (data.status == 'ok') {
                        tr.remove();
                    } else {
                        alert(data.errors.join(', '));
                    }
                },
                error: function (jqXHR, errorText) {
                    $('#stock-form-status').html('');
                    alert(jqXHR.responseText);
                }
            });
        } else {
            tr.remove();
        }


        return false;
    });

    $('input[name=hash]').change(function () {
        var hash = $(this).val();
        $('.js-hash-values').hide();
        $('.js-hash-' + hash).show();
    });
    $('input[name=hash]:first').attr('checked', 'checked').change();

    var field = $('.field.description');
    field.find('i').hide();
    field.find('.s-editor-core-wrapper').show();
    $('#stock-description-content,#stock-page-content').waEditor({
        lang: wa_lang,
        toolbarFixedBox: false
    });

    $('#ibutton-status').iButton({
        labelOn: "", labelOff: "", className: 'mini'
    });

    $('#stock-edit-form').submit(function () {
        var form = $(this);
        $('#stock-form-status').html('<i class="icon16 loading"></i>');
        $.ajax({
            type: 'POST',
            url: '?plugin=stock&action=save',
            data: form.serialize(),
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                if (data.status == 'ok') {
                    $('#stock-form-status').html('<i class="icon16 yes"></i> ' + data.data);
                    document.location.href = '#/stockList/';
                } else {
                    $('#stock-form-status').html('<i class="icon16 no"></i> ' + data.errors.join(', '));
                }
            },
            error: function (jqXHR, errorText) {
                $('#stock-form-status').html('');
                alert(jqXHR.responseText);
            }
        });
        return false;
    });
    $('.stock-delete').click(function () {
        var id = $(this).data('id');
        var loading = $(this).after('<i class="icon16 loading"></i>');
        if (id) {
            $.ajax({
                type: 'POST',
                url: '?plugin=stock&action=delete',
                data: {
                    id: $(this).data('id')
                },
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    document.location.href = '#/stockList/';
                },
                error: function (jqXHR, errorText) {
                    loading.remove();
                    alert(jqXHR.responseText);
                }
            });
        } else {
            document.location.href = '#/stockList/';
        }
        return false;
    });
    $("#stock-edit-form .timepicker").timepicker({
        controlType: 'select',
        timeFormat: 'HH:mm:ss'
    });
    $("#stock-edit-form .datetimepicker").datetimepicker({
        controlType: 'select'
    });
});