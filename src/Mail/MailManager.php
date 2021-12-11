<?php

namespace Curfle\Mail;

use Curfle\Agreements\Mail\Mailer;
use Curfle\Mail\Mailer\SMTPMailer;
use Curfle\Support\Exceptions\Mail\MailerNotFoundException;
use Curfle\Support\Str;

class MailManager
{
    /**
     * @throws MailerNotFoundException
     */
    public function mailer(string $mailer = null) : Mailer
    {
        if($mailer === null)
            $mailer = config("mail.default");

        return match (Str::lower($mailer)) {
            "smtp" => new SMTPMailer(
                config("mail.mailers.smtp.host"),
                (int)config("mail.mailers.smtp.port"),
                config("mail.mailers.smtp.encryption"),
                config("mail.mailers.smtp.username"),
                config("mail.mailers.smtp.password"),
            ),
            default => throw new MailerNotFoundException("The mailer [$mailer] is not supported by curfle."),
        };
    }


    /**
     * Dynamically pass methods to the default mailer.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws MailerNotFoundException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->mailer()->$method(...$parameters);
    }
}