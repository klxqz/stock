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
})(jQuery);