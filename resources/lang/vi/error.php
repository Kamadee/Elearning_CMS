<?php

use App\Helpers\ResponseCode;

return [
  ResponseCode::$BAD_REQUEST => 'Bad Request.',
  ResponseCode::$UNAUTHORIZED => 'Not Authenticated.',
  ResponseCode::$ACCESS_DENIED => 'You do not have access.',
  ResponseCode::$NOT_FOUND => 'Not found',
  ResponseCode::$CONFLICT => "Conflict",
  ResponseCode::$INTERNAL_ERROR => "Internal error has occurred. Please try again.",
  ResponseCode::$TOKEN_INVALID => "Token invalid.",
  ResponseCode::$TOKEN_ABSENT => "Token absent.",
  ResponseCode::$TOKEN_EXPIRED => "Token has expired.",
  ResponseCode::$USER_NOT_FOUND => "User not found.",
  ResponseCode::$INCORRECT_PASSWORD => "Incorrect password.",
  ResponseCode::$CANCEL_BOOKING_NOT_ACCEPT => "Can't cancel at this time.",
  ResponseCode::$MQTT_PUBLISH_FAILED => "Mqtt publish failed",
];
