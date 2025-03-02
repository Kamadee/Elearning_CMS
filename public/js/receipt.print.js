$(document).ready(function () {
    $(document).on('click', '.submitPrint', function () {
        $(".loading_spinner").css("display", "block");
        $.ajax({
            url: '/receipt/save_receipt',
            type: 'POST',
            data: {
                'receiptData': $('#print_data').val(),
                "_token": $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.status) {
                    $('#saveOrPrint').removeClass('submitPrint');
                    $('#saveOrPrint').addClass('printReceipt');
                    $('#saveOrPrint').html('印刷');
                    $(".loading_spinner").css("display", "none");
                    alert('領収書は保存できました。');
                } else {
                    $('.alert-error').addClass('show');
                    $(".loading_spinner").css("display", "none");
                    alert('この領収書印は保存できません。もう一度試してみてください。');
                }
            },
            error: function (response) {
                $('.alert-error').addClass('show');
                $(".loading_spinner").css("display", "none");
                alert('この領収書印は保存できません。もう一度試してみてください。');
            },
        });
    });

    $(document).on('click', '.printReceipt', function () {
        printJS({
            printable: "printPage",
            type: "html",
            css: [
                "/css/bootstrap/bootstrap.min.css",
                "/css/receipt.print.css",
            ],
            scanStyles: true
        });
    });

    $(document).on('click', '.buttonCancel', function () {
        if ($("#saveOrPrint").hasClass("printReceipt")) {
            return confirm('この領収書の印刷を取り消してもよろしいですか。');
        } else {
            return confirm('この領収書はまだ保存されていません。保存を取り消してもよろしいですか。');
        }
    });
});
