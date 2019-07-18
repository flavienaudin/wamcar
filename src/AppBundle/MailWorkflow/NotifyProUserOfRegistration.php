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
use Wamcar\User\ProUser;

class NotifyProUserOfRegistration extends AbstractEmailEventHandler implements UserEventHandler
{

    /** @var array */
    private $adminsEmails;

    public function __construct(Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, string $type, array $adminsEmails)
    {
        parent::__construct($mailer, $router, $templating, $translator, $type);

        $this->adminsEmails = [];
        foreach ($adminsEmails as $monitorsEmail => $name) {
            $this->adminsEmails[] = new EmailContact($monitorsEmail, $name);
        }
    }

    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, ProUserCreated::class);

        /** @var BaseUser $user */
        $user = $event->getUser();

        $trackingKeywords = ($user->isPro() ? 'advisor' : 'customer') . $user->getId();
        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'confirm_email_advisor',
            'utm_term' => $trackingKeywords
        ];

        // TODO : Adapater l'email ? identifiant = email mais mot de passe non connu de l'utilisateur
        $this->send(
            $this->translator->trans('notifyProUserOfRegistration.object', [], 'email'),
            'Mail/notifyProUserOfRegistration.html.twig',
            [
                'common_utm' => $commonUTM,
                'username' => $user->getFirstName(),
                'user_mail' => $user->getEmail(),
                'url_profile_page' => $this->router->generate("front_view_current_user_info", array_merge($commonUTM, [
                    'utm_content' => 'link_profile'
                ]), UrlGeneratorInterface::ABSOLUTE_URL),
                'url_contact_page' => $this->router->generate("contact", array_merge($commonUTM, [
                    'utm_content' => 'link_contact'
                ]), UrlGeneratorInterface::ABSOLUTE_URL),
                'url_profile_page_button' => $this->router->generate("front_view_current_user_info", array_merge($commonUTM, [
                    'utm_content' => 'button_profile'
                ]), UrlGeneratorInterface::ABSOLUTE_URL),
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
                'url_profile_page' => $user instanceof ProUser ?
                    $this->router->generate("front_view_pro_user_info", ['slug' => $user->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                    : $this->router->generate("front_view_personal_user_info", ['slug' => $user->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList($this->adminsEmails)
        );
    }
}
