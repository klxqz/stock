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
            this.form = this.product_id ? this.container.find('form') : $('#s-services-save');

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

        },


        save: function() {

            var form = $.product_stock.form;
            $.product.refresh('submit');

            return $.shop.jsonPost(
                form.attr('action'),
                form.serialize(),
                function(r) {
                    var that = $.product_stock;
                    var sidebar = that.container.find('.s-inner-sidebar');
                    var li = sidebar.find('li[data-service-id='+that.service_id+']');
                    var status = parseInt(r.data.status, 10);
                    if (!status && !li.hasClass('gray')) {
                        li.addClass('gray');
                    } else if (status && li.hasClass('gray')) {
                        li.removeClass('gray');
                    }
                    that.options.count = r.data.count;
                    that.counter.text(r.data.count);

                    $.product.refresh();
                    $('#s-product-save-button').removeClass('yellow green').addClass('green');

                    that.form_serialized_data = form.serialize();

                    $.products.dispatch();
                }
            );
        },
        
    };
})(jQuery);