<?php

namespace AppBundle\EventListener;


use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\PersonalVehicleRepository;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class SitemapSubscriber implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var ProVehicleRepository $proVehicleRepository */
    private $proVehicleRepository;
    /** @var PersonalVehicleRepository $personalVehicleRepository */
    private $personalVehicleRepository;
    /** @var UserRepository $userRepository */
    private $userRepository;
    /** @var GarageRepository $garageRepository */
    private $garageRepository;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param ProVehicleRepository $proVehicleRepository
     * @param PersonalVehicleRepository $personalVehicleRepository
     * @param UserRepository $userRepository
     * @param GarageRepository $garageRepository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator,
                                ProVehicleRepository $proVehicleRepository,
                                PersonalVehicleRepository $personalVehicleRepository,
                                UserRepository $userRepository, GarageRepository $garageRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->userRepository = $userRepository;
        $this->garageRepository = $garageRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerProVehiclePagesUrls($event->getUrlContainer(), $event->getSection());
    }

    /**
     * @param UrlContainerInterface $urls
     * @param null|string $section
     */
    public function registerProVehiclePagesUrls(UrlContainerInterface $urls, ?string $section = null): void
    {
        if (in_array($section, [null, 'proVehicle'], true)) {
            $proVehicles = $this->proVehicleRepository->findall();
            /** @var ProVehicle $proVehicle */
            foreach ($proVehicles as $proVehicle) {
                $urls->addUrl(
                    new UrlConcrete($this->urlGenerator->generate(
                        'front_vehicle_pro_detail',
                        ['slug' => $proVehicle->getSlug()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )), 'proVehicle'
                );
            }
        }
        if (in_array($section, [null, 'personalVehicle'], true)) {
            $personalVehicles = $this->personalVehicleRepository->findall();
            /** @var PersonalVehicle $personalVehicle */
            foreach ($personalVehicles as $personalVehicle) {
                $urls->addUrl(
                    new UrlConcrete($this->urlGenerator->generate(
                        'front_vehicle_personal_detail',
                        ['slug' => $personalVehicle->getSlug()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )), 'personalVehicle'
                );
            }
        }

        $proUserUpdate = in_array($section, [null, 'proUser'], true);
        $personalUserUpdate = in_array($section, [null, 'personalUser'], true);
        if ($proUserUpdate || $personalUserUpdate) {
            $users = $this->userRepository->findAll();

            /** @var BaseUser $user */
            foreach ($users as $user) {
                if ($user instanceof ProUser && $proUserUpdate) {
                    $urls->addUrl(new UrlConcrete(
                        $this->urlGenerator->generate('front_view_pro_user_info',
                            ['slug' => $user->getSlug()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )), 'proUser'
                    );
                } elseif ($user instanceof PersonalUser && $personalUserUpdate) {
                    $urls->addUrl(new UrlConcrete(
                        $this->urlGenerator->generate('front_view_personal_user_info',
                            ['slug' => $user->getSlug()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )), 'personalUser'
                    );
                }
            }
        }

        if (in_array($section, [null, 'garage'], true)) {
            $garages = $this->garageRepository->findall();
            /** @var Garage $garage */
            foreach ($garages as $garage) {
                $urls->addUrl(
                    new UrlConcrete($this->urlGenerator->generate(
                        'front_garage_view',
                        ['slug' => $garage->getSlug()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )), 'garage'
                );
            }
        }
    }
}