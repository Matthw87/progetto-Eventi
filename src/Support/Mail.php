<?php
namespace Marion\Support;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer; 
use Symfony\Component\Mailer\Transport\SendmailTransport; 
class Mail{

    private $mailer;
    private $to;
    private $from;
    private $html;
    private $text;
    private $subject;

    private array $attach_paths = [];

    function __construct()
    {
        $this->files = [];
        $transport = new SendmailTransport(); 
       
        $this->mailer = new Mailer($transport); 
       
    }

    public function setFrom($from): self{
        $this->from = $from;
        return $this;
    }
    public function setTo($to): self{
        $this->to = $to;
        return $this;
    }

    public function setSubject($subject): self{
        $this->subject = $subject;
        return $this;
    }

    public function setHtml($html): self{
        $this->html = $html;
        return $this;
    }

    public function setText($text): self{
        $this->text = $text;
        return $this;
    }

    public function attachFromPath($path): self{
        $this->attach_paths[] = $path;
        return $this;
    }

    public static function from($email): self{
        
        return (new Mail()) 
        ->setFrom($email);
    }


    public function send(): void{
        $email = (new Email())
            ->from($this->from)
            ->to($this->to);

        if( $this->subject ){
            $email->subject($this->subject);
        }
        if( $this->text ){
            $email->text($this->text);
        }
        if( $this->html ){
            $email->html($this->html);
        }
        if( okArray($this->attach_paths) ){
            foreach($this->attach_paths as $file){
                $email->attachFromPath($file);
            }
        }
        
        $this->mailer->send($email); 
    }

    
}