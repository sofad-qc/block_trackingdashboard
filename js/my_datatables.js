$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
        var filterDate = parseInt((new Date($('#lastActivityDate').val()).getTime() / 1000).toFixed(0)) || 0;

        var strippedDate = data[3].replace('th', '');
        console.log(strippedDate);
        var lastActivityDate = parseInt((new Date(strippedDate).getTime() / 1000).toFixed(0)) || 0;


        console.log('---- filterDate > lastActivityDate----');
        console.log(filterDate + '>' + lastActivityDate);
        if(filterDate == 0 || filterDate > lastActivityDate)
        {

            return true;
        }
        return false;
    }
);


$(document).ready(function() {
    var table = $('#dashboard').DataTable({
        dom: "<'row'<'col-sm-4'l><'col-sm-4'<'customFilter'>><'col-sm-4'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        }
}   );

    $("div.customFilter")
        .html(
            // eslint-disable-next-line max-len
            '<input type="text" placeholder="last activity date" class="form-control form-control-sm" id="lastActivityDate" onfocus="(this.type=\'date\')" onblur="(this.type=\'text\')"/>');

    $('#lastActivityDate').change( function() {
        table.draw();
    } );
});