<?php

return [

  /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

  'paths' => ['api/*'], // Các route được áp dụng CORS

  'allowed_methods' => ['*'], // Các HTTP method được phép (GET, POST, PUT,...)

  'allowed_origins' => ['http://localhost:8080'], // Domain được phép truy cập (thay '*' bằng domain cụ thể nếu cần)

  'allowed_origins_patterns' => [], // Mẫu domain cho phép (regex nếu cần)

  'allowed_headers' => ['*'], // Các header được phép

  'exposed_headers' => [], // Các header được phép hiển thị ở client

  'max_age' => 0, // Thời gian cache (giây)

  'supports_credentials' => true, // Có cho phép cookie hoặc xác thực không
];
