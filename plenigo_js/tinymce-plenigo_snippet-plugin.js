tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');
tinymce.PluginManager.add('plenigo_snippet', function (editor, url) {
// Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo_renew', {
        tooltip: 'Insert plenigo snippet',
        text: '',
        icon: 'pl-snippet',
        onclick: function () {
            // Open setup window
            editor.windowManager.open({
                title: 'Plenigo Snippet Selection',
                body: [
                    {type: 'combobox', name: 'snippet', label: 'Snippet ID',
                        values: [
                            {text: 'All Snippets', value: 'all'},
                            {text: 'Personal Data',         value: 'plenigo.Snippet.PERSONAL_DATA'},
                            {text: 'Orders Status',         value: 'plenigo.Snippet.ORDER'},
                            {text: 'Subscriptions',         value: 'plenigo.Snippet.SUBSCRIPTION'},
                            {text: 'Payment Methods',       value: 'plenigo.Snippet.PAYMENT_METHODS'},
                            {text: 'Address Information',   value: 'plenigo.Snippet.ADDRESS_DATA'},
                            {text: 'Billing Address Only Information',   value: 'plenigo.Snippet.BILLING_ADDRESS_DATA'},
                            {text: 'Delivery Address Only Information',   value: 'plenigo.Snippet.DELIVERY_ADDRESS_DATA'},
                            {text: 'Bank Account Only Information',   value: 'plenigo.Snippet.BANK_ACCOUNT'},
                            {text: 'Credit Card Only Information',   value: 'plenigo.Snippet.CREDIT_CARD'},
                            {text: 'Personal Data Settings Only Information',   value: 'plenigo.Snippet.PERSONAL_DATA_SETTINGS'},
                            {text: 'Personal Data Address Only Information',   value: 'plenigo.Snippet.PERSONAL_DATA_ADDRESS'},
                            {text: 'Personal Data Protection Only Information',   value: 'plenigo.Snippet.PERSONAL_DATA_PROTECTION'},
                            {text: 'Personal Data Social Media Only Information',   value: 'plenigo.Snippet.PERSONAL_DATA_SOCIAL_MEDIA'},
                            {text: 'Personal Data Password Only Information',   value: 'plenigo.Snippet.PERSONAL_DATA_PASSWORD'}
                        ]
                    }
                ],
                onsubmit: function (e) {
                    var selected_text = editor.selection.getContent();
                    var return_text = '';
                    var name_text = '';
                    if (e.data.snippet.trim() === '' || e.data.snippet.length < 3) {
                        editor.windowManager.alert('Invalid selection!');
                    } else {
                        name_text = ' name="' + e.data.snippet.trim() + '"';
                        return_text = selected_text
                                + '[pl_snippet'
                                + name_text
                                + ']';
                        editor.execCommand('mceInsertContent', false, return_text);
                    }
                }
            });
        }
    });
});