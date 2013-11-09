$.product.editTabLoadContent = function(path, params) {
    var self = $.product;
    path = path || $.product.path;

    if (path.tab == 'stock') {
        var url = '?plugin=stock&id=' + path.id;
    } else {
        var url = '?module=product&action=' + path.tab + '&id=' + path.id;
    }

    if (path.tail && (typeof (path.tail) != 'undefined')) {
        url += '&param[]=' + path.tail.split('/').join('&param[]=');
    }
    var r = Math.random();
    $.product.ajax.random = r;
    var $tab = $('#s-product-edit-forms .s-product-form.' + path.tab);
    if ($tab.length) {
        $tab.remove();
    }
    $('#s-product-edit-forms > form').append(tmpl('template-productprofile-tab', {
        id: path.tab
    }));
    $tab = $('#s-product-edit-forms .s-product-form.' + path.tab);
    $.product.ajax.target = $tab;
    $.product.ajax.link = $('#s-product-edit-menu li.' + path.tab);
    $.shop.trace('$.product.editTabLoadContent', [path, url, params]);
    $.ajax({
        'url': url,
        'type': params ? 'POST' : 'GET',
        'data': params || {},
        'success': function(data, textStatus, jqXHR) {
            $.shop.trace('$.product.loadTab status=' + textStatus);
            if (self.ajax.random != r) {
                // too late: user clicked something else.
                return;
            }
            $tab.empty().append(data);
            self.ajax.target = null;
            self.ajax.link = null;
            var hash = '#/product/' + path.id + '/edit/';
            if (path.tab) {
                hash += path.tab + '/';
            }
            if (path.tail) {
                hash += path.tail + '/';
            }
            window.location.hash = hash;
            self.dispatch(path);
        }
    });

}