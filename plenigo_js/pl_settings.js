var plenigoSettings = plenigoSettings || {
        jqHidden: [],
        jqEditor: [],
        jqTags: [],
        jqProds: [],
        jqAddButton: [],
        jqModButton: [],
        jqDelButton: [],
        init: function (prefix) {
            // Components
            this.jqHidden[prefix] = jQuery("#" + prefix + "_db"); // Hidden DB
            this.jqEditor[prefix] = jQuery("#" + prefix + "_editor"); // List Editor
            this.jqTags[prefix] = jQuery("#" + prefix + "_tags"); // Tags dropdown
            this.jqProds[prefix] = jQuery("#" + prefix + "_prods"); // Products dropdown
            this.jqAddButton[prefix] = jQuery("#" + prefix + "_add_btn"); // The Add Button
            this.jqModButton[prefix] = jQuery("#" + prefix + "_mod_btn"); // The Modify Button
            this.jqDelButton[prefix] = jQuery("#" + prefix + "_del_btn"); // The Delete Button

            // Events
            this.jqAddButton[prefix].click(prefix, this.addValue);
            this.jqModButton[prefix].click(prefix, this.modValue);
            this.jqDelButton[prefix].click(prefix, this.delValue);

            this.update(prefix);
        },
        addValue: function (event) {
            // calling statically since "this" is actually the button
            var that = plenigoSettings;
            var prefix = event.data;

            var tagsVal = that.jqTags[prefix].find(":selected").val();
            var prodsVal = that.jqProds[prefix].find(":selected").val();
            var setString = tagsVal+"->"+prodsVal;
            var strSettingValue = that.jqHidden[prefix].val(); // DB value

            //TODO Continue here


            // Set the combos to blank
            that.jqTags[prefix].val('');
            that.jqProds[prefix].val('');

            that.update(prefix);
        },
        modValue: function (event) {
            // calling statically since "this" is actually the button
            var that = plenigoSettings;
            var prefix = event.data;



            that.update(prefix);
        },
        delValue: function (event) {
            // calling statically since "this" is actually the button
            var that = plenigoSettings;
            var prefix = event.data;



            that.update(prefix);
        },
        update: function (prefix) {
            var strSettingValue = this.jqHidden[prefix].val(); // DB value
            var that = this;

            // Empty editor
            this.jqEditor[prefix].empty();
            // split lines
            var arrSettings = strSettingValue.split("\n");
            if (arrSettings.length > 0) {
                jQuery.each(arrSettings, function (i, line) {
                    var m = plenigoParseLine(line);

                    if (m !== null) {
                        that.jqEditor[prefix].append(
                            jQuery('<option></option>').val(line).html(m[2] + " -> " + m[3])
                        );
                    }
                });
            }
        }
    };

/**
 * Parses a line of "Tag{tagslug}->producId" to separate the three values.
 *
 * @param {string} line the line with the format to parse
 * @returns {Array} NULL or an array with all the line on index 0 and then the three captured values.
 */
function plenigoParseLine(line) {
    var res = [];
    // [CAPTURE 1]{[CAPTURE 2]}->[CAPTURE 3]
    var re = /(.*)\{(.*)\}->(.*)/;
    var m = [];
    if ((m = re.exec(line)) !== null) {
        if (m.index === re.lastIndex) {
            re.lastIndex++;
        }
        res = m;
    } else {
        res = null;
    }
    return res;
}