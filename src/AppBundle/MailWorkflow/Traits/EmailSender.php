<?php


namespace AppBundle\MailWorkflow\Traits;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Services\Mailer;
use Wamcar\User\User;
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
    /** @var  array */
    protected $parameters;
    /** @var  string */
    protected $type;

    /**
     * AbstractEmailEventHandler constructor.
     * @param Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     * @param array $parameters
     * @param string $type
     */
    public function __construct(
        Mailer $mailer,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        array $parameters,
        string $type
    )
    {
        $this->mailer           = $mailer;
        $this->router           = $router;
        $this->templating       = $templating;
        $this->translator       = $translator;
        $this->parameters       = $parameters;
        $this->type             = $type;
    }

    /**
     * @param string $subject
     * @param string $template
     * @param array $bodyParameters
     * @param EmailContact $recipient
     * @param \Swift_Attachment[] $attachments
     */
    public function send(string $subject, string $template, array $bodyParameters = [], EmailContact $recipient, array $attachments = [])
    {
        $this->mailer->sendMessage(
            $this->type,
            $subject,
            $this->renderTemplate($template, $bodyParameters),
            $recipient,
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
     * @param User $user
     * @return EmailContact
     */
    protected function createUserEmailContact(User $user): EmailContact
    {
        return new EmailContact($user->getEmail(), (null !== $user->getUserProfile())? $user->getUserProfile()->getName(): null);
    }
}
