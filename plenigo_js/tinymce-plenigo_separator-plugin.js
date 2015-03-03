tinymce.PluginManager.requireLangPack('plenigo', 'en_US,es_ES');
tinymce.PluginManager.add('plenigo_separator', function (editor, url) {

    var separatorTag = "<!-- {{PLENIGO_SEPARATOR}} -->";
    var separatorImg = '<img class="pl-separator mceItem mceNonEditable" alt="" data-mce-resize="false" data-mce-placeholder="1" />';


    // Add a button that opens a setup window and configures the checkout button
    editor.addButton('plenigo_separator', {
        text: '-P---',
        icon: '',
        onclick: function () {
            // show alert
            editor.windowManager.alert('This will split the teaser from the paywalled content!');
            editor.execCommand('mceInsertContent', false, separatorTag);
        }
    });

    editor.on('BeforeSetContent', function (event) {
        event.content = event.content.replace(separatorTag, separatorImg);
    });

    editor.on('PostProcess', function (event) {
        if (event.get) {
            var regExp = /<img class="pl-separator.+".+\/>/ig;
            event.content = event.content.replace(regExp, separatorTag);
        }
    });
});