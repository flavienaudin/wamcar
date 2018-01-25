<?php


namespace AppBundle\MailWorkflow\Traits;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Wamcar\User\BaseUser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

trait EmailSender
{
    /** @var Mailer */
    protected $mailer;
    /** @var UrlGeneratorInterface  */
    protected $router;
    /** @var EngineInterface  */
    protected $templating;
    /** @var TranslatorInterface  */
    protected $translator;
    /** @var  string */
    protected $type;

    /**
     * AbstractEmailEventHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param string $type
     */
    public function __construct(
        Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        string $type
    )
    {
        $this->mailer           = $mailer;
        $this->router           = $router;
        $this->templating       = $templating;
        $this->translator       = $translator;
        $this->type             = $type;
    }

    /**
     * @param string $subject
     * @param string $template
     * @param array $bodyParameters
     * @param EmailRecipientList $recipients
     * @param \Swift_Attachment[] $attachments
     */
    public function send(string $subject, string $template, array $bodyParameters = [], EmailRecipientList $recipients, array $attachments = [])
    {
        $this->mailer->sendMessage(
            $this->type,
            $subject,
            $this->renderTemplate($template, $bodyParameters),
            $recipients,
            $attachments
        );
    }

    /**
     * Return the HTML with inlined CSS
     *
     * @param string $template
     * @param array $bodyParameters
     * @return string
     */
    protected function renderTemplate(string $template, array $bodyParameters): string
    {
        return $this->templating->render($template, $bodyParameters);
    }

    /**
     * Create an email contact destined to $user
     *
     * @param BaseUser $user
     * @return EmailContact
     */
    protected function createUserEmailContact(BaseUser $user): EmailContact
    {
        return new EmailContact($user->getEmail(), $user->getFullName());
    }
}
