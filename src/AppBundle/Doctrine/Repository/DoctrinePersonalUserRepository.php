<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrinePersonalUserRepository extends EntityRepository implements UserRepository, RegisteredWithConfirmationProvider, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;
    use PasswordResettableRepositoryTrait;
    use SluggableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

    /**
     * Search for new PersonalUsers (registrations) between the ($refDatetime or now() - 1H) and ($refDatetime or now() - 1H - $delay H)
     * @param int $since Number of hours since the last search
     * @param \DateTime|null $refDatetime : The dateime of reference to search before
     * @return array of PersonalUser
     * @throws \Exception
     */
    public function findNewRegistations(int $since, ?\DateTime $refDatetime = null): array
    {
        if ($refDatetime != null) {
            $selectIntervalEnd = $refDatetime;
        } else {
            $selectIntervalEnd = new \DateTime("now");
        }
        $selectIntervalEnd->sub(new \DateInterval('PT1H'));
        $selectIntervalStart = clone $selectIntervalEnd;
        $selectIntervalStart->sub(new \DateInterval('PT' . $since . 'H'));

        $mainQb = $this->createQueryBuilder('u');
        $mainQb
            ->where($mainQb->expr()->between('u.createdAt', ':afterDate', ':beforeDate'))
            ->andWhere($mainQb->expr()->isNotNull('u.userProfile.city.name'));
        $mainQb
            ->setParameter(':afterDate', $selectIntervalStart)
            ->setParameter(':beforeDate', $selectIntervalEnd);
        return $mainQb->getQuery()->execute();
    }
}
