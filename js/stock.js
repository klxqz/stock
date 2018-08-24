(function ($) {
    $.products.stockListAction = function () {
        this.load('?plugin=stock', function () {
            $("#s-sidebar li.selected").removeClass('selected');
            $("#s-stocks").addClass('selected');
        });
    }
    $.products.stockAddAction = function () {
        this.load('?plugin=stock&action=stock');
    }
    $.products.stockAction = function (id) {
        this.load('?plugin=stock&action=stock&id=' + id);
    }

    function showDialog() {
        $('#stock-dialog').waDialog({
            disableButtonsOnSubmit: false,
            onLoad: function () {

            },
            onSubmit: function (d) {
                var post_data = '';
                $('table#product-list tr.product td input[type=checkbox]:checked').each(function () {
                    post_data = post_data + '&product_id[]=' + $(this).closest('tr.product').data('product-id');
                });

                var self = $(this);
                self.find('i.loading').show();
                $.ajax({
                    type: 'POST',
                    url: self.attr('action'),
                    data: self.serialize() + post_data,
                    dataType: 'json',
                    success: function (data, textStatus, jqXHR) {
                        self.find('i.loading').hide();
                        if (data.status == 'ok') {
                            $('#stock-dialog').trigger('close');
                        } else if (data.status == 'fail') {
                            $('.stock-response').text(data.errors);
                            $('.stock-response').css('color', 'red');
                            $('.stock-response').show();
                        }
                    }
                });
                return false;
            }
        });
    }

    $(document).on('click', '.add-stock-products', function () {
        $.ajax({
            type: 'GET',
            url: '?plugin=stock&action=dialog',
            success: function (data, textStatus, jqXHR) {
                $('#stock-dialog').html(data);
                showDialog();
            }
        });

        return false;
    });

    $(document).on('click', '.stock-update', function () {
        var self = $(this);
        var id = self.data('id');
        var loading = $('<i class="icon16 loading"></i>');
        self.after(loading);
        $.ajax({
            type: 'POST',
            url: '?plugin=stock&action=update',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                loading.remove();
                if (data.status == 'ok') {
                    self.after('<i class="icon16 yes"></i>');
                    window.location.reload();
                } else {
                    self.html('<i class="icon16 no"></i> ' + data.errors.join(', '));
                }
            },
            error: function (jqXHR, errorText) {
                loading.remove();
                alert(jqXHR.responseText);
            }
        });
        return false;
    });
})(jQuery);