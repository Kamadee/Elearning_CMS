$.extend(true, $.fn.dataTable.defaults, {
    serverSide: true,
    fixedHeader: true,
    searchDelay: 800,
    processing: false
})

$.validator.setDefaults({
    lang: 'vi',
    onfocusout: function(element) {
        $(element).valid()
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback')
        element.closest('.form-group').append(error)
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid')
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid')
    }
});

$(document).ajaxStart(function(){
    $(".overlay-spinner").removeClass('hidden');
})
$(document).ajaxComplete(function(){
    $(".overlay-spinner").addClass('hidden');
})

const DAY_OF_WEEKS = [
    'CN',
    'T2',
    'T3',
    'T4',
    'T5',
    'T6',
    'T7'
]

const MONTHS = [
    'Tháng Một',
    'Tháng Hai',
    'Tháng Ba',
    'Tháng Tư',
    'Tháng Năm',
    'Tháng Sáu',
    'Tháng Bảy',
    'Tháng Tám',
    'Tháng Chín',
    'Tháng Mười',
    'Tháng Mười Một',
    'Tháng Mười Hai',
]