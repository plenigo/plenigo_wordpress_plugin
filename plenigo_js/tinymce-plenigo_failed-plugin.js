tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');
tinymce.PluginManager.add('plenigo_failed', function (editor, url) {
    // Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo_failed', {
        tooltip: 'Failed Payments Button',
        text: '',
        icon: 'pl-failed',
        onclick: function () {
            // Open setup window
            editor.windowManager.open({
                title: 'Plenigo Failed Payments Button',
                body: [
                    {type: 'textbox', name: 'title', label: 'Button Title'},
                    {type: 'textbox', name: 'cssClass', label: 'Button CSS class'}
                ],
                onsubmit: function (e) {
                    var selected_text = editor.selection.getContent();
                    var return_text = '';
                    var title_text = '';
                    var class_text = '';
                    if (e.data.title.trim() !== '') {
                        title_text = ' title="' + e.data.title.trim() + '" ';
                    }
                    if (e.data.cssClass.trim() !== '') {
                        class_text = ' class="' + e.data.cssClass.trim() + '" ';
                    }
                    return_text = '[pl_failed '
                            + title_text
                            + class_text
                            + ' ]'
                            + selected_text
                            + '[/pl_failed]';
                    editor.execCommand('mceInsertContent', false, return_text);
                }
            });
        }
    });
});