$(document).ready(function() {
    $('.select-date').datetimepicker({
        pickDate:true,
        pickTime:false
    });

    $('#btn-reset').on('click', function() {
        $('input[name=from_date]').val(getFirstDayOfMonth())
        $('input[name=to_date]').val(getToday())
        $('#customer_name').val("")
        $('#service_code').val("")
        $('#service_name').val("")
    })

    $('#search-receipt').on('submit', function(e) {
        var fromDate = $('#from_date').val()
        var toDate = $('#to_date').val()
        if (toDate != "" && fromDate != "" && toDate < fromDate) {
            $('#error-date').removeClass('d-none')
            e.preventDefault()
        }
    })

    function getToday() {
        var now = new Date(),
        month = now.getMonth() + 1,
        day = now.getDate(),
        year = now.getFullYear();
        if (month < 10) {
            return year + "-0" + month + "-" + day;
        } else {
            return year + "-" + month + "-" + day;
        }
    }

    function getFirstDayOfMonth() {
        var now = new Date(),
        month = now.getMonth() + 1,
        year = now.getFullYear();
        if (month < 10) {
            return year + "-0" + month + "-01";
        } else {
            return year + "-" + month + "-01";
        }
    }
});