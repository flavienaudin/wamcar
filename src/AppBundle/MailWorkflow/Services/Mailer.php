<?php
namespace AppBundle\MailWorkflow\Services;

use AppBundle\MailWorkflow\Model\EmailContact;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Mailer
{
    /** @var \Swift_Mailer $mailer */
    private $mailer;
    /** @var array $parameters */
    private $parameters;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(
        \Swift_Mailer $mailer,
        array $parameters,
        LoggerInterface $logger = null
    ) {
        $this->mailer           = $mailer;
        $this->parameters       = $parameters;
        $this->logger           = $logger;
    }

    /**
     * @param $type
     * @param $subject
     * @param $body
     * @param EmailContact $toContact
     * @param array $attachments
     */
    public function sendMessage($type, $subject, $body, EmailContact $toContact, array $attachments = [])
    {
        $fromEmail = new EmailContact($this->parameters['from_email']['mail'], $this->parameters['from_email']['name']);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail->getEmail(), $fromEmail->getName())
            ->setTo($toContact->getEmail())
            ->setBody($body, 'text/html');
        $message->getHeaders()->addTextHeader('X-Message-ID', $type);

        foreach ($attachments as $attachment) {
            // Attach it to the message
            $message->attach($attachment);
        }

        try {
            $this->mailer->send($message);
            $this->log(sprintf("A '%s' email was sent successfully to %s", $type, $toContact), [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            $this->log(sprintf("An error occured when sending a '%s' email to %s.", $type, $toContact->getEmail()), [
                'to'      => $toContact->getEmail(),
                'subject' => $subject
            ], LogLevel::ERROR);
        }
    }



    /**
     * @param string $title
     * @param array $context
     * @param string $level
     */
    private function log($title, array $context, $level = LogLevel::INFO)
    {
        if (null === $this->logger) {
            return;
        }
        $this->logger->log($level, $title, $context);
    }
}
