<?php

namespace AppBundle\Twig;

use AppBundle\Services\Notification\NotificationManagerExtended;
use Mgilet\NotificationBundle\NotifiableInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


/**
 * Twig extension to display notifications (extending Mgillet/NotificationExtension)
 **/
class NotificationPlusExtension extends AbstractExtension
{

    /** @var NotificationManagerExtended */
    protected $notificationManagerExtended;
    /** @var TokenStorage */
    protected $storage;
    /** @var Environment */
    protected $twig;

    /**
     * NotificationPlusExtension constructor.
     * @param NotificationManagerExtended $notificationManagerExtended
     * @param TokenStorage $storage
     * @param Environment $twig
     */
    public function __construct(NotificationManagerExtended $notificationManagerExtended, TokenStorage $storage, Environment $twig)
    {
        $this->notificationManagerExtended = $notificationManagerExtended;
        $this->storage = $storage;
        $this->twig = $twig;
    }

    /**
     * @return array available Twig functions
     */
    public function getFunctions()
    {
        return array_merge(
            array(
                new TwigFunction('wamcar_notification_render', array($this, 'render_extended'), array(
                    'is_safe' => array('html')
                ))
            )
        );
    }


    /**
     * Rendering notifications in Twig
     *
     * @param array $options
     * @param NotifiableInterface $notifiable
     *
     * @return null|string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render_extended(NotifiableInterface $notifiable, array $options = array())
    {
        return $this->renderNotifications($notifiable, $options);
    }


    /**
     * Render notifications of the notifiable as a list
     *
     * @param NotifiableInterface $notifiable
     * @param array $options
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderNotifications(NotifiableInterface $notifiable, array $options)
    {
        $seen = array_key_exists('seen', $options) ? $options['seen'] : null;
        $order = array_key_exists('order', $options) ? $options['order'] : null;
        $offset = array_key_exists('offset', $options) ? $options['offset'] : 0;
        $limit = array_key_exists('limit', $options) ? $options['limit'] : null;

        $notifications = $this->notificationManagerExtended->getNotifications($notifiable, $seen, $order, $offset, $limit);

        // if the template option is set, use custom template
        $template = array_key_exists('template', $options) ? $options['template'] : '@MgiletNotification/notification_list.html.twig';

        return $this->twig->render($template, ['notificationList' => $notifications]);
    }
}