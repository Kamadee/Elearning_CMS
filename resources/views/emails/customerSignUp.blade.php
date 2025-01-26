<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <p>Xin chào Quý khách,</p>
        <p>Bạn vừa đăng ký thành công tài khoản trên {{config('app.name')}}. Vui lòng click vào đường dẫn sau để kích hoạt tài khoản!</p>
        <p>{{ config('app.landing_url') . '/customer/verify?code=' . $code }}</p>
        <p>Trân trọng.</p>
        <p><i>*Đây là email tự động. Quý khách vui lòng không trả lời email này*</i></p>
    </body>
</html>
