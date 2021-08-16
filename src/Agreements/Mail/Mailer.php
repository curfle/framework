<?php

namespace Curfle\Agreements\Mail;

interface Mailer{
    /**
     * Adds one or more recipients.
     *
     * @param string|array $recipient
     * @return $this
     */
    public function to(string|array $recipient): static;

    /**
     * Adds one or more cc recipients.
     *
     * @param string|array $recipient
     * @return $this
     */
    public function cc(string|array $recipient): static;

    /**
     * Adds one or more bcc recipients.
     *
     * @param string|array $recipient
     * @return $this
     */
    public function bcc(string|array $recipient): static;

    /**
     * Adds one or more recipients for the reply.
     *
     * @param string|array $recipient
     * @return $this
     */
    public function replyTo(string|array $recipient): static;

    /**
     * Sets the senders' email and name.
     *
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function from(string $email, string $name): static;

    /**
     * Sends an email.
     *
     * @param Mailable $mail
     * @return bool
     */
    public function send(Mailable $mail) : bool;
}