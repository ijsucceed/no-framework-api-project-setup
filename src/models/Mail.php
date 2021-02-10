<?php
/**
 * Class help to design, send, and perform related function about a Mail.
 */
class Mail
{
    public string $body;
    public $to;
    public string $subject;

    function __construct( string $to = '', string $subject = '', string $body = '') 
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }

    public function set_default_template_header() {
        $this->body .= file_get_contents( '../storage/email/head.html' );
    }

    public function set_default_template_footer() {
        $this->body .= file_get_contents( '../storage/email/footer.html' );
    }

    public function clear_body() {
        $this->body = '';
    }

    public function append_html( string $body, string $line = '' ) : void {
        if ( empty( $line ) ) {
            $this->body .= "<p style='font-size: 13px'>{$body}</p>";
        }
        else {
            $this->body .= "<span style='font-size: 13px'>{$body}</span><br>";
        }
    }

    public function send() {
        app_send_mail_with_mailgun( $this->to, $this->subject, $this->body );
    }
}