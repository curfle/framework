<?php

namespace Curfle\Mail;

use Curfle\Agreements\Mail\Mailable as MailableAgreement;
use Curfle\Mail\MailContent;

abstract class Mailable implements MailableAgreement
{
    /**
     * Returns the HTML formatted content of the email.
     *
     * @return string
     */
    abstract public function subject() : string;

    /**
     * Returns the HTML formatted content of the email.
     *
     * @return string
     */
    abstract public function content() : string;

    /**
     * Returns the content without HTML formatting.
     *
     * @return string
     */
    public function plainContent() : string
    {
        return preg_replace(
            "/\n\s+/",
            "\n",
            rtrim(
                html_entity_decode(
                    strip_tags($this->content())
                )
            )
        );
    }

    /**
     * Returns the list of attachment files.
     *
     * @return array
     */
    public function attachments() : array{
        return [];
    }

    /**
     * Builds a MailContent object.
     *
     * @return MailContent
     */
    public function build(): MailContent
    {
        return (new MailContent())
            ->setContent($this->content())
            ->setPlainContent($this->plainContent())
            ->setSubject($this->subject())
            ->setAttachments($this->attachments());
    }
}