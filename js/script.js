/* eslint-env es6 */

window.$(document).ready(function() {
    window.$.fn.dataTable.ext.search.push(
        function(settings, data) {
            const lastActivityRangePicker = window.$('#lastActivityRange');
            if(lastActivityRangePicker !== null) {
                const dateRangePickerValues = lastActivityRangePicker.val();
                if(dateRangePickerValues !== undefined && dateRangePickerValues !== getTranslatedPlaceHolder()) {
                    let minDate = Date.parse(dateRangePickerValues.substring(0, dateRangePickerValues.indexOf('-') - 1));
                    let maxDate = Date.parse(dateRangePickerValues.substring(
                        dateRangePickerValues.indexOf('-') + 2,
                        dateRangePickerValues.length));
                    // eslint-disable-next-line no-undef
                    let strippedDate = Date.parse(data[3]
                        .replace('th', '').replace('nd', '').replace('st', '').replace('rd', ''))
                        || 0;
                    if(strippedDate >= minDate && strippedDate <= maxDate) {
                        return true;
                    }
                    return false;
                }
            }
            return true;
        }
    );
    let table = window.$('#dashboard').DataTable({
        columnDefs: [
            {
                orderable: false, targets: 0
            }
        ],
        order: [[1, 'asc']],
        dom: "<'row filters'<'col-sm-4 filter'l><'col-sm-4 filter'<'trackingDashboardCustomFilter'>><'col-sm-4 filter'f>>",
        language: {
            url: getLanguageFileUrl(),
        },
        initComplete: function() {
            window.$("div.trackingDashboardCustomFilter")
                .html(
                     '<input ' +
                    'type="text" ' +
                    'name="datefilter" ' +
                    'class="customFilterInput form-control form-control-sm" ' +
                    'id="lastActivityRange" ' +
                    'value="' + getTranslatedPlaceHolder() + '" />');
            window.$(function() {
                window.$('input[name="datefilter"]').daterangepicker({
                    autoUpdateInput: false,
                    opens: 'center',
                    showDropdowns: true,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });
                window.$('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
                    window.$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    table.draw();
                });

                window.$('input[name="datefilter"]').on('cancel.daterangepicker', function() {
                    window.$(this).val(getTranslatedPlaceHolder());
                    table.draw();
                });
            });
        }
});

/**
 * Return the current language file url.
 * @returns {string} The sum of the two numbers.
 */
function getLanguageFileUrl() {
    const lang = document.getElementById("data-lang").textContent;
    if(lang.includes("fr")) {
        return '../blocks/trackingdashboard/lang/fr/datatables.french.json';
    }
    return '../blocks/trackingdashboard/lang/en/datatables.english.json';
}

/**
 * Return translated placeholder for datepicker.
 * @returns {string} The translated placeholder.
 */
function getTranslatedPlaceHolder() {
    const lang = document.getElementById("data-lang").textContent;
    if(lang.includes("fr")) {
        return 'Plage de date pour dernière activité';
    }
    return 'Date range for last activity';
}


});