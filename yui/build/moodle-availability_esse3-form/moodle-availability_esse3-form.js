YUI.add('moodle-availability_esse3-form', function (Y, NAME) {

/**
 * JavaScript for form editing esse3 enrolments condition.
 *
 * @module moodle-availability_esse3-form
 */
M.availability_esse3 = M.availability_esse3 || {};

// Class M.availability_esse3.form @extends M.core_availability.plugin.
M.availability_esse3.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * Nothing to be initialized,
 *
 * @method initInner
 */
M.availability_esse3.form.initInner = function() {
};

M.availability_esse3.form.getNode = function(json) {
    // Create HTML structure.
    var idnumberlist = '';
    if (json.creating === undefined) {
        if (json.idnumbers !== undefined) {
            idnumberlist = json.idnumbers;
        }
    }
    var tit = M.util.get_string('title', 'availability_esse3');
    var html = '<label class="form-group"><span class="p-r-1">' + tit + '</span>';
    html += '<span class="availability-esse3">';
    if (idnumberlist == "") {
        html += '<input class="esse3-file" name="esse3file" type="file" />';
    } 
    html += '<input class="esse3-list-field" name="idnumbers" type="hidden" value="' + idnumberlist + '" />';
    html += '<span class="esse3-list">' + idnumberlist.split(",").join("<br />") + '</span>';
    html += '</span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    var errorstr = M.util.get_string('missing', 'availability_esse3');

    // Add event handlers (first time only).
    if (!M.availability_esse3.form.addedEvents) {
        M.availability_esse3.form.addedEvents = true;
        var root = Y.one('.availability-field');

        root.delegate('change', function() {
            if (this._node.files.length > 0) {
                var file = this._node.files[0];

                var esse3filechooser = this;
                var esse3list = this.next('.esse3-list-field');
                var esse3displaylist = this.next('.esse3-list');
         
                if (file) {
                    var reader = new FileReader();
                        
                    reader.onload = function (e) {
                        var data = e.target.result;
                        var workbook = XLSX.read(data, { type: 'binary' });
                        var idnumbers = [];
    
                        workbook.SheetNames.forEach(function(sheetName) {
                            var XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
                            var idColumn = "matricola".toLowerCase();
                        
                            if (XL_row_object[0].hasOwnProperty("GRUPPO_GIUD_COD")) {
                                XL_row_object.forEach(function(row) {
                                    // Student enrolment ordinal number.
                                    var ordNumber = parseInt(row.GRUPPO_GIUD_COD);
                                    if (row.hasOwnProperty("SUBSET") && ordNumber >= 0) {
                                        // Skip column header and get idcolumn values.
                                        if (row.SUBSET.toLowerCase() != idColumn) {
                                            idnumbers.push(row.SUBSET);
                                        }
                                    }
                                });
                            }
                        });
                        if (idnumbers.length > 0) {
                            esse3filechooser.remove();
                            esse3list.set('value', idnumbers.join(','));
                            esse3displaylist.set('innerHTML', idnumbers.join('<br />'));

                            // Just update the form fields.
                            M.core_availability.form.update();
                        } else {
                           esse3displaylist.set('innerHTML', '<div class="invalid-feedback" style="display:block;">'
                                    + errorstr + "</div>");
                        }
                    };

                    reader.readAsBinaryString(file);
                }
            }

        }, '.availability_esse3 input.esse3-file');
    }

    return node;
};

M.availability_esse3.form.focusAfterAdd = function(node) {
    var target = node.one('input:not([disabled])');
    target.focus();
};

M.availability_esse3.form.fillValue = function(value, node) {
    var selected = node.one('input[name=idnumbers]').get('value');
    if (selected === '') {
        value.idnumbers = '';
    } else {
        value.idnumbers = selected;
    }
};

M.availability_esse3.form.fillErrors = function(errors, node) {
    var selected = node.one('input[name=idnumbers]').get('value');
    if (selected === '') {
        errors.push('availability_esse3:missing');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "node-event-simulate", "moodle-core_availability-form"]});
