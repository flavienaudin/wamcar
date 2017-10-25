<?php
namespace AppBundle\MailWorkflow\Services;

use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Mailer
{
    /** @var \Swift_Mailer $mailer */
    private $mailer;
    /** @var LoggerInterface $logger */
    private $logger;
    /** @var EmailContact $defaultSender */
    private $defaultSender;

    public function __construct(
        \Swift_Mailer $mailer,
        LoggerInterface $logger = null,
        EmailContact $defaultSender
    ) {
        $this->mailer           = $mailer;
        $this->logger           = $logger;
        $this->defaultSender    = $defaultSender;
    }

    /**
     * @param $type
     * @param $subject
     * @param $body
     * @param EmailRecipientList $emailRecipientList
     * @param array $attachments
     */
    public function sendMessage($type, $subject, $body, EmailRecipientList $emailRecipientList, array $attachments = [])
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->defaultSender->getEmail(), $this->defaultSender->getName())
            ->setTo($emailRecipientList->toArray())
            ->setBody($body, 'text/html');
        $message->getHeaders()->addTextHeader('X-Message-ID', $type);

        foreach ($attachments as $attachment) {
            // Attach it to the message
            $message->attach($attachment);
        }

        try {
            $this->mailer->send($message);
            $this->log(sprintf("A '%s' email was sent successfully to %s", $type, $emailRecipientList), [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            $this->log(sprintf("An error occured when sending a '%s' email to %s.", $type, $emailRecipientList), [
                'to'      => $emailRecipientList->toArray(),
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
