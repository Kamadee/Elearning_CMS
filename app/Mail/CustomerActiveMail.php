<?php
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerActiveMail extends Mailable
{
    use Queueable, SerializesModels;
    private $code;
    public function __construct($code)
    {
        $this->code = $code; 
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.customerSignUp')
        ->subject('Xác nhận email đăng ký')
        ->with('code', $this->code);
    }
}