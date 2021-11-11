<?php

namespace Curfle\Mail\Mailer;

use Curfle\Agreements\Mail\Mailable;
use Curfle\Agreements\Mail\Mailer;
use Curfle\Support\Facades\Config;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SMTPMailer implements Mailer
{

    /**
     * The PHPMailer instance.
     *
     * @var PHPMailer
     */
    private PHPMailer $PHPMailer;

    /**
     * SMTP host.
     *
     * @var string
     */
    private string $host;

    /**
     * SMTP port.
     * @var int
     */
    private int $port;

    /**
     * SMTP encryption algorithm.
     * @var string
     */
    private string $encryption;

    /**
     * SMTP username.
     *
     * @var string
     */
    private string $username;

    /**
     * SMTP password.
     *
     * @var string
     */
    private string $password;

    public function __construct(string $host, int $port, string $encryption, string $username, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->encryption = $encryption;
        $this->username = $username;
        $this->password = $password;

        $this->createNewPHPMailerInstance();
    }

    /**
     * Creates a new PHP instance for sending mails.
     */
    private function createNewPHPMailerInstance()
    {
        $this->PHPMailer = new PHPMailer();

        $this->PHPMailer->isSMTP();
        $this->PHPMailer->CharSet = "UTF-8";
        $this->PHPMailer->Host = $this->host;
        $this->PHPMailer->SMTPAuth = true;
        $this->PHPMailer->Username = $this->username;
        $this->PHPMailer->Password = $this->password;
        $this->PHPMailer->SMTPSecure = $this->encryption;
        $this->PHPMailer->Port = $this->port;
        $this->PHPMailer->From = Config::get("mail.from.address", "");
        $this->PHPMailer->FromName = Config::get("mail.from.name", "");
    }

    /**
     * @inheritDoc
     */
    public function from(string $email, string $name): static
    {
        $this->PHPMailer->From = $email;
        $this->PHPMailer->FromName = $name;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function to(array|string $recipient): static
    {
        if(is_array($recipient))
            foreach ($recipient as $r)
                $this->PHPMailer->addAddress($r);
        else
            $this->PHPMailer->addAddress($recipient);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function cc(array|string $recipient): static
    {
        if(is_array($recipient))
            foreach ($recipient as $r)
                $this->PHPMailer->addCC($r);
        else
            $this->PHPMailer->addCC($recipient);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bcc(array|string $recipient): static
    {
        if(is_array($recipient))
            foreach ($recipient as $r)
                $this->PHPMailer->addBCC($r);
        else
            $this->PHPMailer->addBCC($recipient);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function replyTo(array|string $recipient): static
    {
        if(is_array($recipient))
            foreach ($recipient as $r)
                $this->PHPMailer->addReplyTo($r);
        else
            $this->PHPMailer->addReplyTo($recipient);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function send(Mailable $mail): bool
    {
        $content = $mail->build();

        $this->PHPMailer->isHTML(true);
        $this->PHPMailer->Subject = $content->subject();
        $this->PHPMailer->Body = $content->html();
        $this->PHPMailer->AltBody = $content->plain();
        foreach($content->attachments() as $name => $attachment){
            $name = is_string($name) ? $name : "";
            if(is_array($attachment))
                $this->PHPMailer->addStringAttachment(
                    $attachment["content"],
                    $name,
                    $attachment["encoding"],
                    $attachment["type"]
                );
            else
                $this->PHPMailer->addAttachment($attachment, $name);
        }

        return $this->PHPMailer->send();
    }

    /**
     * Returns the error informationen, when the email could not be sent.
     *
     * @return string
     */
    public function getError() : string
    {
        return $this->PHPMailer->ErrorInfo;
    }
}