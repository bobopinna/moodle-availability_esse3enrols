/**
 * JavaScript for form editing esse3 enrolments condition.
 *
 * @module moodle-availability_esse3enrols-form
 */
M.availability_esse3enrols = M.availability_esse3enrols || {};

// Class M.availability_esse3enrols.form @extends M.core_availability.plugin.
M.availability_esse3enrols.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * Nothing to be initialized,
 *
 * @method initInner
 */
M.availability_esse3enrols.form.initInner = function() {
};

M.availability_esse3enrols.form.getNode = function(json) {
    // Create HTML structure.
    var idnumberlist = '';
    if (json.creating === undefined) {
        if (json.idnumbers !== undefined) {
            idnumberlist = json.idnumbers;
        }
    }
    var tit = M.util.get_string('title', 'availability_esse3enrols');
    var html = '<label class="form-group"><span class="p-r-1">' + tit + '</span>';
    html += '<span class="availability-esse3enrols">';
    if (idnumberlist == "") {
        html += '<input class="esse3enrols-file" name="esse3enrolsfile" type="file" />';
    }
    html += '<input class="esse3enrols-list-field" name="idnumbers" type="hidden" value="' + idnumberlist + '" />';
    html += '<span class="esse3enrols-list">' + idnumberlist.split(",").join("<br />") + '</span>';
    html += '</span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    var errorstr = M.util.get_string('missing', 'availability_esse3enrols');

    // Add event handlers (first time only).
    if (!M.availability_esse3enrols.form.addedEvents) {
        M.availability_esse3enrols.form.addedEvents = true;
        var root = Y.one('.availability-field');

        root.delegate('change', function() {
            if (this._node.files.length > 0) {
                var file = this._node.files[0];

                var esse3enrolsfilechooser = this;
                var esse3enrolslist = this.next('.esse3enrols-list-field');
                var esse3enrolsdisplaylist = this.next('.esse3enrols-list');

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
                            } else {
                                var columnName = null;
                                for (var prop in XL_row_object[0]) {
                                    if (prop.toLowerCase() == idColumn) {
                                        columnName = prop;
                                    }
                                }
                                if (columnName != null) {
                                    XL_row_object.forEach(function(row) {
                                        if (row.hasOwnProperty(columnName) && (row[columnName] != '')) {
                                            idnumbers.push(row[columnName]);
                                        }
                                    });
                                }
                            }
                        });
                        if (idnumbers.length > 0) {
                            esse3enrolsfilechooser.remove();
                            esse3enrolslist.set('value', idnumbers.join(','));
                            esse3enrolsdisplaylist.set('innerHTML', idnumbers.join('<br />'));

                            // Just update the form fields.
                            M.core_availability.form.update();
                        } else {
                           esse3enrolsdisplaylist.set('innerHTML', '<div class="invalid-feedback" style="display:block;">'
                                    + errorstr + "</div>");
                        }
                    };

                    reader.readAsBinaryString(file);
                }
            }

        }, '.availability_esse3enrols input.esse3enrols-file');
    }

    return node;
};

M.availability_esse3enrols.form.focusAfterAdd = function(node) {
    var target = node.one('input:not([disabled])');
    target.focus();
};

M.availability_esse3enrols.form.fillValue = function(value, node) {
    var selected = node.one('input[name=idnumbers]').get('value');
    if (selected === '') {
        value.idnumbers = '';
    } else {
        value.idnumbers = selected;
    }
};

M.availability_esse3enrols.form.fillErrors = function(errors, node) {
    var selected = node.one('input[name=idnumbers]').get('value');
    if (selected === '') {
        errors.push('availability_esse3enrols:missing');
    }
};
