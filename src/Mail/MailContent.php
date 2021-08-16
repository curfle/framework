<?php

namespace Curfle\Mail;

use Curfle\Agreements\Mail\MailContent as MailContentAgreement;

class MailContent implements MailContentAgreement
{

    /**
     * Subject of mail
     *
     * @var string
     */
    private string $subject = "";

    /**
     * Mails' attachments.
     *
     * @var array
     */
    private array $attachmants = [];

    /**
     * HTML formatted content.
     *
     * @var string
     */
    private string $content = "";

    /**
     * Plain text formatted content.
     *
     * @var string
     */
    private string $plainContent = "";

    /**
     * Sets the content.
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content) : static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets the plain content.
     *
     * @param string $content
     * @return $this
     */
    public function setPlainContent(string $content) : static
    {
        $this->plainContent = $content;
        return $this;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject) : static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Sets the attachments.
     *
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments) : static
    {
        $this->attachmants = $attachments;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function subject(): string
    {
        return $this->subject;
    }

    /**
     * @inheritDoc
     */
    public function attachments(): array
    {
        return $this->attachmants;
    }

    /**
     * @inheritDoc
     */
    public function html(): string
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function plain(): string
    {
        return $this->plainContent;
    }
}