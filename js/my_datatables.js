$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
        var filterDate = parseInt((new Date($('#lastActivityDate').val()).getTime() / 1000).toFixed(0)) || 0;
        var strippedDate = data[3].replace('th', '');
        var lastActivityDate = parseInt((new Date(strippedDate).getTime() / 1000).toFixed(0)) || 0;
        if(filterDate == 0 || filterDate > lastActivityDate)
        {
            return true;
        }
        return false;
    }
);

function getLanguage() {
    var lang = document.getElementById("data-lang").textContent;
    if(lang.includes("fr")) {
        return '../blocks/trackingdashboard/lang/fr/datatables.french.json';
    }
    return '../blocks/trackingdashboard/lang/fr/datatables.english.json';
}

function getTranslatedPlaceHolder() {
    var lang = document.getElementById("data-lang").textContent;
    if(lang.includes("fr")) {
        return 'Date da la dernière activité';
    }
    return 'Last activity date';
}

$(document).ready(function() {
    var table = $('#dashboard').DataTable({
        columnDefs: [
            {
                orderable: false, targets: 0
            }
        ],
        order: [[1, 'asc']],
        dom: "<'row'<'col-sm-4'l><'col-sm-4'<'trackingDashboardCustomFilter'>><'col-sm-4'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            url: getLanguage(),
        },
        initComplete: function() {
            $("div.trackingDashboardCustomFilter")
                .html(
                    // eslint-disable-next-line max-len
                    '<input type="text" placeholder="'+getTranslatedPlaceHolder()+'" class="customFilterInput form-control form-control-sm" id="lastActivityDate" onfocus="(this.type=\'date\')" onblur="(this.type=\'text\')"/>'); // jshint ignore:line

            $('#lastActivityDate').change( function() {
                table.draw();
            } );
        }
});




});