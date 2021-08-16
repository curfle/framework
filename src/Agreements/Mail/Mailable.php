<?php

namespace Curfle\Agreements\Mail;

interface Mailable
{
    public function build(): MailContent;
}