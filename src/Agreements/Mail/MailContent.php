<?php

namespace Curfle\Agreements\Mail;

interface MailContent
{

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function subject(): string;

    /**
     * Returns the attachments.
     *
     * @return array
     */
    public function attachments(): array;

    /**
     * Returns the HTML formatted email content.
     *
     * @return string
     */
    public function html(): string;

    /**
     * Returns the plain text formatted email content.
     *
     * @return string
     */
    public function plain(): string;
}