tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');

function isInvalidNumber(number) {
    return number !== '' && (isNaN(parseFloat(number)) || !isFinite(number));
}

function getAttributeIfNotEmpty(value, attributeName) {
    var trimmedValue = value.trim();
    if (trimmedValue === '') {
        return trimmedValue;
    }
    return attributeName + '="' + trimmedValue + '" ';
}

function isInteger(number) {
    return !isNaN(number) && number % 1 === 0 && /^\d+$/.test(number);
}

tinymce.PluginManager.add('plenigo', function (editor, url) {
    // Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo', {
        tooltip: 'Product Checkout Button',
        text: '',
        icon: 'pl-checkout',
        onclick: function () {
            // Open setup window
            editor.windowManager.open({
                title: 'plenigo Checkout Button',
                body: [
                    {type: 'textbox', name: 'prodId', label: 'Product ID*'},
                    {type: 'textbox', name: 'price', label: 'Price(Currency is the one configured in the product)'},
                    {type: 'textbox', name: 'quantityTitle', label: 'Quantity Title'},
                    {type: 'textbox', name: 'quantityCssClass', label: 'Quantity CSS class'},
                    {type: 'textbox', name: 'quantityLabelCssClass', label: 'Quantity Label CSS class'},
                    {type: 'textbox', name: 'maxQuantity', label: 'Max Quantity', value: '1'},
                    {type: 'textbox', name: 'title', label: 'Button Title'},
                    {type: 'textbox', name: 'cssClass', label: 'Button CSS class'},
                    {
                        type: 'combobox', name: 'register', label: 'Show Register form',
                        values: [
                            {text: 'Yes', value: '1'},
                            {text: 'No', value: '0'}
                        ]
                    },
                    {type: 'textbox', name: 'source', label: 'Source URL'},
                    {type: 'textbox', name: 'target', label: 'Target URL'},
                    {type: 'textbox', name: 'affiliate', label: 'Affiliate ID'},
                    {
                        type: 'combobox', name: 'hideWhenBought', label: 'Hide Button when bought',
                        values: [
                            {text: 'Yes', value: '1'},
                            {text: 'No', value: '0'}
                        ],
                        value: '1'
                    },
                    {
                        type: 'combobox', name: 'usePostTitle', label: 'Use Post Title',
                        values: [
                            {text: 'Yes', value: '1'},
                            {text: 'No', value: '0'}
                        ],
                        value: '0'
                    }
                ],
                onsubmit: function (e) {
                    var isInvalid = false;
                    var price = e.data.price.trim();
                    var max_quantity = e.data.maxQuantity.trim();
                    var trimmedProdId = e.data.prodId.trim();
                    if (trimmedProdId === '' || e.data.prodId.length < 5) {
                        editor.windowManager.alert('Invalid Product ID!');
                        isInvalid = true;
                    } else if (isInvalidNumber(price)) {
                        editor.windowManager.alert('Invalid Price!');
                        isInvalid = true;
                    } else if (!isInteger(max_quantity) || parseInt(max_quantity) <= 0) {
                        editor.windowManager.alert('Invalid Quantity!');
                        isInvalid = true;
                    } else {
                        var selected_text = editor.selection.getContent();
                        var prod_text = ' prod_id="' + trimmedProdId + '" ';
                        var title_text = getAttributeIfNotEmpty(e.data.title, 'title');
                        var class_text = getAttributeIfNotEmpty(e.data.cssClass, 'class');
                        var register_text = getAttributeIfNotEmpty(e.data.register, 'register');
                        var source_text = getAttributeIfNotEmpty(e.data.source, 'source');
                        var target_text = getAttributeIfNotEmpty(e.data.target, 'target');
                        var affiliate_text = getAttributeIfNotEmpty(e.data.affiliate, 'affiliate');
                        var price_text = '';
                        if (price !== '') {
                            price_text = ' price="' + price + '" ';
                        }
                        var quantity_class_text = getAttributeIfNotEmpty(e.data.quantityCssClass, 'quantity_class');
                        var quantity_label_class_text = getAttributeIfNotEmpty(e.data.quantityLabelCssClass, 'quantity_label_class');
                        max_quantity = getAttributeIfNotEmpty(e.data.maxQuantity, 'max_quantity');
                        var quantity_title_text = getAttributeIfNotEmpty(e.data.quantityTitle, 'quantity_title');
                        var hide_when_bought = getAttributeIfNotEmpty(e.data.hideWhenBought, 'hide_when_bought');
                        var use_post_title_text = getAttributeIfNotEmpty(e.data.usePostTitle, 'use_post_title');
                        var return_text = '[pl_checkout '
                            + prod_text
                            + title_text
                            + class_text
                            + register_text
                            + source_text
                            + target_text
                            + affiliate_text
                            + price_text
                            + quantity_class_text
                            + quantity_label_class_text
                            + max_quantity
                            + quantity_title_text
                            + hide_when_bought
                            + use_post_title_text
                            + ' ]'
                            + selected_text
                            + '[/pl_checkout]';
                        editor.execCommand('mceInsertContent', false, return_text);
                    }
                    if (isInvalid) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
});