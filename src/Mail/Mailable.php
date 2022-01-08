<?php

namespace Curfle\Mail;

use Curfle\Agreements\Mail\Mailable as MailableAgreement;
use Curfle\View\View;

abstract class Mailable implements MailableAgreement
{
    /**
     * Returns the HTML formatted content of the email.
     *
     * @return string
     */
    abstract public function subject() : string;

    /**
     * Returns the HTML formatted content of the email or a view containing the HTML formatted content.
     *
     * @return string|View
     */
    abstract public function content() : string|View;

    /**
     * Transforms the content into a string.
     *
     * @return string
     */
    protected function loadContent() : string
    {
        // load content
        $content = $this->content();

        // redenr view if content is a view
        if($content instanceof View)
            $content = $content->render();

        return $content;
    }

    /**
     * Returns the content without HTML formatting.
     *
     * @return string
     */
    public function plainContent() : string
    {
        // load content
        $content = $this->loadContent();

        // just use the <body></body>
        if (preg_match('/<body>(.*)<\/body>/is', $content, $match) == 1)
            $content = $match[1];

        return preg_replace(
            "/\n\s+/",
            "\n",
            rtrim(
                html_entity_decode(
                    strip_tags($content)
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
            ->setContent($this->loadContent())
            ->setPlainContent($this->plainContent())
            ->setSubject($this->subject())
            ->setAttachments($this->attachments());
    }
}