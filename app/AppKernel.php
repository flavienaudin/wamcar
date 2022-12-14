<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new FOS\CKEditorBundle\FOSCKEditorBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new JMose\CommandSchedulerBundle\JMoseCommandSchedulerBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Mgilet\NotificationBundle\MgiletNotificationBundle(),
            new Presta\SitemapBundle\PrestaSitemapBundle(),
            new RobertoTru\ToInlineStyleEmailBundle\RobertoTruToInlineStyleEmailBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new AppBundle\AppBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test', 'inte'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            // Disabled to avoid error in service declaration
            // $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
