tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');
tinymce.PluginManager.add('plenigo', function (editor, url) {
    // Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo', {
        tooltip: 'Product Checkout Button',
        text: '',
        icon: 'pl-checkout',
        onclick: function () {
            // Open setup window
            editor.windowManager.open({
                title: 'Plenigo Checkout Button',
                body: [
                    {type: 'textbox', name: 'prodId', label: 'Product ID*'},
                    {type: 'textbox', name: 'title', label: 'Button Title'},
                    {type: 'textbox', name: 'cssClass', label: 'Button CSS class'},
                    {type: 'combobox', name: 'register', label: 'Show Register form',
                        values: [
                            {text: 'Yes', value: '1'},
                            {text: 'No', value: '0'}
                        ]
                    },
                    {type: 'textbox', name: 'source', label: 'Source URL'},
                    {type: 'textbox', name: 'target', label: 'Target URL'},
                    {type: 'textbox', name: 'affiliate', label: 'Affiliate ID'}
                ],
                onsubmit: function (e) {
                    var selected_text = editor.selection.getContent();
                    var return_text = '';
                    var prod_text = '';
                    var title_text = '';
                    var register_text = '';
                    var source_text = '';
                    var target_text = '';
                    var affiliate_text = '';
                    var class_text = '';
                    if (e.data.prodId.trim() === '' || e.data.prodId.length < 5) {
                        editor.windowManager.alert('Invalid Product ID!');
                    } else {
                        prod_text = ' prod_id="' + e.data.prodId.trim() + '" ';
                        if (e.data.title.trim() !== '') {
                            title_text = ' title="' + e.data.title.trim() + '" ';
                        }
                        if (e.data.cssClass.trim() !== '') {
                            class_text = ' class="' + e.data.cssClass.trim() + '" ';
                        }
                        if (e.data.register.trim() !== '') {
                            register_text = ' register="' + e.data.register.trim() + '" ';
                        }
                        if (e.data.source.trim() !== '') {
                            source_text = ' source="' + e.data.source.trim() + '" ';
                        }
                        if (e.data.target.trim() !== '') {
                            target_text = ' target="' + e.data.target.trim() + '" ';
                        }
                        if (e.data.affiliate.trim() !== '') {
                            affiliate_text = ' affiliate="' + e.data.affiliate.trim() + '" ';
                        }
                        return_text = '[pl_checkout '
                                + prod_text
                                + title_text
                                + class_text
                                + register_text
                                + source_text
                                + target_text
                                + affiliate_text
                                + ' ]'
                                + selected_text
                                + '[/pl_checkout]';
                        editor.execCommand('mceInsertContent', false, return_text);
                    }
                }
            });
        }
    });
});