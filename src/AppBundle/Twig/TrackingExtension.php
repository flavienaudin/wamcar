<?php


namespace AppBundle\Twig;


use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\BaseVehicle;

class TrackingExtension extends AbstractExtension
{
    const VALUE_UNLOGGED = '0';
    const VALUE_ADVISOR = 'Advisor';
    const VALUE_CUSTOMER = 'Customer';
    const VALUE_GARAGE = 'Garage';
    const VALUE_NONE = 'None';

    public function getFilters()
    {
        return [
            new TwigFilter('wtFromDataAttrValue', array($this, 'getWtFromDataAttrValue')),
            new TwigFilter('wtToDataAttrValue', array($this, 'getWtToDataAttrValue'))
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('wtNoneValue', array($this, 'getWtNoneValue')),
            new TwigFunction('wtLikeDataAttributes', array($this, 'getLikeWtDataAttributes')),
            new TwigFunction('wtSearchFormDataAttributes', array($this, 'getSearchFormWtDataAttributes')),
            new TwigFunction('wtDirectorySearchFormDataAttributes', array($this, 'getDirectorySearchFormWtDataAttributes'))
        ];
    }

    /**
     * Get the value of attribute data-from to for [GA] tracking
     * @param BaseUser|null $from
     * @return string
     */
    public function getWtFromDataAttrValue(?BaseUser $from): string
    {
        if ($from == null) {
            return self::VALUE_UNLOGGED;
        }
        if ($from->isPro()) {
            return self::VALUE_ADVISOR . $from->getId();
        } elseif ($from->isPersonal()) {
            return self::VALUE_CUSTOMER . $from->getId();
        }
        return self::VALUE_UNLOGGED;
    }

    /**
     * Get the value of attribute data-from to for [GA] tracking
     * @param null|mixed $to
     * @return string
     */
    public function getWtToDataAttrValue($to): string
    {
        if ($to == null) {
            return self::VALUE_UNLOGGED;
        }
        if ($to instanceof ProUser) {
            return self::VALUE_ADVISOR . $to->getId();
        } elseif ($to instanceof PersonalUser) {
            return self::VALUE_CUSTOMER . $to->getId();
        } elseif ($to instanceof Garage) {
            return self::VALUE_GARAGE . $to->getId();
        }
        return self::VALUE_UNLOGGED;
    }

    /**
     * @return string
     */
    public function getWtNoneValue(): string
    {
        return self::VALUE_NONE;
    }

    /**
     * @param BaseUser|null $fromUser The user, connected or not, who is (un)liking
     * @param BaseUser $toUser The seller of the vehicule
     * @param BaseLikeVehicle|null $likeVehicle The like or null
     * @param BaseVehicle $vehicle The vehicle to (un)like
     * @return string
     */
    public function getLikeWtDataAttributes(?BaseUser $fromUser, BaseUser $toUser, ?BaseLikeVehicle $likeVehicle, BaseVehicle $vehicle): string
    {
        if ($likeVehicle == null || $likeVehicle->getValue() === 0) {
            $action = 'LI';
        } else {
            $action = 'UL';
        }
        return ' data-wtaction="' . $action . ' ' . $vehicle->getSlug() . '" data-wtfrom="' . $this->getWtFromDataAttrValue($fromUser)
            . '" data-wtto="' . $this->getWtToDataAttrValue($toUser) . '"';
    }

    /**
     * @param FormView $form
     * @return array
     */
    public function getSearchFormWtDataAttributes(FormView $form, ?BaseUser $currentUser): array
    {
        if ($form->vars['submitted']) {
            $attributes = ['data-wtaction' => 'SA', 'data-wtfrom' => $this->getWtFromDataAttrValue($currentUser)];
            // text
            if (!empty($text = $this->getFormElementValue($form, 'text'))) {
                $attributes['data-wtrequest'] = $text;
            }
            // cityName, radius, postalCode  || latitude, longitude
            if (!empty($city = $this->getFormElementValue($form, 'cityName'))) {
                $attributes['data-wtcity'] = $city . '#' . $this->getFormElementValue($form, 'postalCode');
                if (!empty($radius = $this->getFormElementValue($form, 'radius'))) {
                    $attributes['data-wtradius'] = $radius;

                }
            }
            // type
            if (!empty($type = $this->getFormElementValue($form, 'type'))) {
                $attributes['data-wttype'] = $type;
            }
            // sorting
            if (!empty($sorting = $this->getFormElementValue($form, 'sorting'))) {
                $attributes['data-wtsorting'] = $sorting;
            }
            /* make, model, transmission, fuel
            mileageMax, yearsMin, yearsMax, budgetMin, budgetMax*/
            return $attributes;
        }
        return [];
    }

    /**
     * @param FormView $form
     * @return array
     */
    public function getDirectorySearchFormWtDataAttributes(FormView $form, ?BaseUser $currentUser): array
    {
        if ($form->vars['submitted']) {
            $attributes = ['data-wtaction' => 'SC', 'data-wtfrom' => $this->getWtFromDataAttrValue($currentUser)];
            // text
            if (!empty($text = $this->getFormElementValue($form, 'text'))) {
                $attributes['data-wtrequest'] = $text;
            }
            // cityName, radius, postalCode  || latitude, longitude
            if (!empty($city = $this->getFormElementValue($form, 'cityName'))) {
                $attributes['data-wtcity'] = $city . '#' . $this->getFormElementValue($form, 'postalCode');
                if (!empty($radius = $this->getFormElementValue($form, 'radius'))) {
                    $attributes['data-wtradius'] = $radius;

                }
            }
            // sorting
            if (!empty($sorting = $this->getFormElementValue($form, 'sorting'))) {
                $attributes['data-wtsorting'] = $sorting;
            }

            return $attributes;
        }
        return [];
    }

    private function getFormElementValue(FormView $form, string $name): ?string
    {
        if (isset($form->children[$name]) && !empty($form->children[$name]->vars['value'])) {
            if (is_array($form->children[$name]->vars['value'])) {
                return join(',', $form->children[$name]->vars['value']);
            } else {
                return $form->children[$name]->vars['value'];
            }
        }
        return null;
    }
}