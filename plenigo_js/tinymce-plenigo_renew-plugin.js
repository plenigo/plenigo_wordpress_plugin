tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');
tinymce.PluginManager.add('plenigo_renew', function (editor, url) {
    // Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo_renew', {
        text: 'Renew Subscription',
        icon: '',
        onclick: function () {
            // Open setup window
            editor.windowManager.open({
                title: 'Plenigo Renew Button',
                body: [
                    {type: 'textbox', name: 'prodId', label: 'Product ID*'},
                    {type: 'textbox', name: 'title', label: 'Button Title'},
                    {type: 'textbox', name: 'cssClass', label: 'Button CSS class'}
                ],
                onsubmit: function (e) {
                    var selected_text = editor.selection.getContent();
                    var return_text = '';
                    var prod_text = '';
                    var title_text = '';
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
                        return_text = '[pl_renew '
                                + prod_text
                                + title_text
                                + class_text
                                + ' ]'
                                + selected_text
                                + '[/pl_renew]';
                        editor.execCommand('mceInsertContent', false, return_text);
                    }
                }
            });
        }
    });
});