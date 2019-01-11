<?php

namespace AppBundle\Twig;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;

class URLFactoryExtension extends AbstractExtension
{

    /** @var RouterInterface $routing */
    private $routing;


    /**
     * URLFactoryExtension constructor.
     */
    public function __construct(RouterInterface $routing)
    {
        $this->routing = $routing;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('userInfoUrl', array($this, 'getUserInfoURL')),
        );
    }

    /**
     * Generate the url for the user profile page
     * @param BaseUser $user
     * @param array $routeParams
     * @param bool $absoluteURL if true then absolute URL is generated
     * @return string
     */
    public function getUserInfoURL(BaseUser $user, array $routeParams = [], bool $absoluteURL = false): string
    {
        $routeParams = array_merge(['slug' => $user->getSlug()], $routeParams);
        $referenceType = $absoluteURL ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;
        if ($user instanceof ProUser) {
            return $this->routing->generate('front_view_pro_user_info', $routeParams, $referenceType);
        } else {
            return $this->routing->generate('front_view_personal_user_info', $routeParams, $referenceType);
        }
    }
}