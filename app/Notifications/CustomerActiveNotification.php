<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerActiveNotification extends Notification
{
  protected $confirmation_code;

  public function __construct($confirmation_code)
  {
    $this->confirmation_code = $confirmation_code;
  }
  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    return (new MailMessage)
      ->subject('Xác nhận tài khoản')
      ->line('Mã xác thực của bạn là: ' . $this->confirmation_code)
      ->line('Vui lòng nhập mã này để kích hoạt tài khoản.');
  }
}
