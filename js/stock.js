(function($) {
    $.product_stock = {

        /**
         * {Number}
         */
        service_id: 0,

        /**
         * {Number}
         */
        product_id: 0,

        /**
         * {Jquery object}
         */
        form: null,

        /**
         * Keep track changing of form
         * {String}
         */
        form_serialized_data: '',

        /**
         * {Jquery object}
         */
        container: null,

        button_color: null,

        /**
         * {Object}
         */
        options: {},

        init: function(options) {
            this.options = options;
            if (options.container) {
                if (typeof options.container === 'object') {
                    this.container = options.container;
                } else {
                    this.container = $(options.container);
                }
            }
            if (options.counter) {
                if (typeof options.counter === 'object') {
                    this.counter = options.counter;
                } else {
                    this.counter = $(options.counter);
                }
            }

            this.service_id = parseInt(this.options.service_id, 10) || 0;
            this.product_id = parseInt(this.options.product_id, 10) || 0;
            this.form = $('#s-product-save');

            if (this.product_id) {

                // maintain intearaction with $.product object



                $.product.editTabStockBlur = function() {
                    var that = $.product_stock;

                    if (that.form_serialized_data != that.form.serialize()) {
                        $.product_stock.save();
                    }
                };

                $.product.editTabStockSave = function() {
                    $.product_stock.save();
                };

                var that = this;
                var button = $('#s-product-save-button');

                // some extra initializing
                that.container.addClass('ajax');
                that.form_serialized_data = that.form.serialize();
                that.counter.text(that.options.count);

            }
            this.discountTypeInit();
            this.typeInit();
                
        },
        
        discountTypeInit: function() {
            var val = $('input[name="shop_stock[discount_type]"]').val();
            $('.discount_type_option').hide();
            $('#option_' + val).show();
            
            $('input[name="shop_stock[discount_type]"]').change(function(){
                var val = $(this).val();
                $('.discount_type_option').hide();
                $('#option_' + val).show();
            });
        },
        
        typeInit: function() {
            var val = $('select[name="shop_stock[type]"]').val();
            $('.stock_type').hide();
            $('#type_' + val).show();
            
            $('select[name="shop_stock[type]"]').change(function(){
                var val = $(this).val();
                $('.stock_type').hide();
                $('#type_' + val).show();
            });
        },


        save: function() {

            var form = $.product_stock.form;
            var that = this;
            $.product.refresh('submit');
            
            var url = '?plugin=stock&action=save';
            return $.shop.jsonPost(
                url,
                form.serialize(),
                function(r) {
                    if(r.data.id) {
                        $('input[name="shop_stock[id]"]').val(r.data.id);
                    }                    
                    $.product.refresh();
                    $('#s-product-save-button').removeClass('yellow green').addClass('green');

                    that.form_serialized_data = form.serialize();

                    $.products.dispatch();
                }
            );
        },
        
    };
})(jQuery);