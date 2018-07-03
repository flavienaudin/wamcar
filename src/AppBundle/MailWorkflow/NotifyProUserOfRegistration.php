<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use AppBundle\MailWorkflow\Services\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class NotifyProUserOfRegistration extends AbstractEmailEventHandler implements UserEventHandler
{

    /** @var string */
    private $adminEmail;
    /** @var string */
    private $adminName;

    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, string $adminEmail, string $adminName)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);
        $this->adminEmail = $adminEmail;
        $this->adminName = $adminName;
    }

    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, ProUserCreated::class);

        /** @var BaseUser $user */
        $user = $event->getUser();

        // TODO : Adapater l'email ? identifiant = email mais mot de passe non connu de l'utilisateur
        $this->send(
            $this->translator->trans('notifyProUserOfRegistration.object', [], 'email'),
            'Mail/notifyProUserOfRegistration.html.twig',
            [
                'username' => $user->getFirstName(),
                'user_mail' => $user->getEmail(),
                'url_profile_page' => $this->router->generate("front_view_current_user_info", [], UrlGeneratorInterface::ABSOLUTE_URL),
                'url_contact_page' => $this->router->generate("contact", [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );

        $this->send(
            "Wamcar - Nouvelle inscription d'un professionnel",
            'Mail/notifyAdminOfProUserRegistration.html.twig',
            [
                'firstname' => $user->getFirstName(),
                'lastname' => $user->getLastName(),
                'user_mail' => $user->getEmail(),
                'url_profile_page' => $this->router->generate("front_view_user_info", ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([new EmailContact($this->adminEmail,$this->adminName)])
        );
    }
}
