<?php

namespace AppBundle\Form\Builder\Garage;


use AppBundle\Doctrine\Entity\GarageBanner;
use AppBundle\Doctrine\Entity\GarageLogo;
use AppBundle\Doctrine\Entity\GaragePicture;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\Garage;

class GarageFromDTOBuilder
{
    /**
     * @param GarageDTO $dto
     * @param null|Garage $garage
     * @return Garage
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function buildFromDTO($dto, ?Garage $garage): Garage
    {
        if (null === $garage) {
            return $this->newGarageFromDto($dto);
        } else {
            return $this->editGarageFromDto($dto, $garage);
        }
    }

    /**
     * Create a new Garage from dto
     *
     * @param GarageDTO $dto
     * @return Garage
     */
    protected function newGarageFromDto($dto): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects dto argument to be an instance of '%s', '%s' given",
                GarageDTO::class,
                get_class($dto))
            );
        }

        $garage =  new Garage(
            $dto->googlePlaceId,
            $dto->name,
            $dto->siren,
            $dto->openingHours,
            $dto->presentation,
            $dto->getAddress(),
            $dto->phone,
            null,
            null,
            $dto->googleRating

        );

        if ($dto->banner->file){
            $banner = new GarageBanner($garage, $dto->banner->file);
            $garage->setBanner($banner);
        }
        if ($dto->logo->file){
            $logo = new GarageLogo($garage, $dto->logo->file);
            $garage->setLogo($logo);
        }

        return $garage;
    }

    /**
     * Edit a Garage from dto
     *
     * @param GarageDTO $dto
     * @param Garage $garage
     * @return Garage
     */
    protected function editGarageFromDto($dto, $garage): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                GarageDTO::class,
                get_class($dto)
            );
        }

        $garage->setGooglePlaceId($dto->googlePlaceId);
        $garage->setName($dto->name);
        $garage->setEmail($dto->email);
        $garage->setPhone($dto->phone);
        $garage->setSiren($dto->siren);
        $garage->setOpeningHours($dto->openingHours);
        $garage->setPresentation($dto->presentation);
        $garage->setBenefit($dto->benefit);
        $garage->setGoogleRating($dto->googleRating);
        $garage->setAddress($dto->getAddress());
        if ($dto->banner) {
            if ($dto->banner->isRemoved) {
                $garage->removeBanner();
            } elseif ($dto->banner->file) {
                $banner = new GarageBanner($garage, $dto->banner->file);
                $garage->setBanner($banner);
            }
        }
        if ($dto->logo) {
            if ($dto->logo->isRemoved) {
                $garage->removeLogo();
            } elseif ($dto->logo->file) {
                $logo = new GarageLogo($garage, $dto->logo->file);
                $garage->setLogo($logo);
            }
        }

        return $garage;
    }

}
