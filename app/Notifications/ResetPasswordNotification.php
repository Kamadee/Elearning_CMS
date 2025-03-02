<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
  public function __construct(public string $token) {}

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    $resetUrl = url('/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email));
    return (new MailMessage)
      ->subject('Yêu cầu đặt lại mật khẩu')
      ->greeting('Xin chào!')
      ->line('Bạn đã yêu cầu đặt lại mật khẩu. Nhấn vào nút bên dưới để tiếp tục:')
      ->action('Đặt lại mật khẩu', $resetUrl)
      ->line('Nếu bạn không yêu cầu, hãy bỏ qua email này.');
  }
}
