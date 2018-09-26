<?php

namespace TypeForm\Services;


use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use TypeForm\Exception\WrongContentException;
use Wamcar\User\PersonalUser;

class AffinityFormManager
{

    /** @var string $personalFormId */
    private $personalFormId;
    /** @var string $proFormId */
    private $proFormId;
    /** @var DoctrineUserRepository */
    private $userRepository;

    /**
     * AffinityFormManager constructor.
     * @param DoctrineUserRepository $userRepository
     */
    public function __construct(DoctrineUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $personalFormId
     */
    public function setPersonalFormId(string $personalFormId): void
    {
        $this->personalFormId = $personalFormId;
    }

    /**
     * @param string $proFormId
     */
    public function setProFormId(string $proFormId): void
    {
        $this->proFormId = $proFormId;
    }

    /**
     * @param array $formContent
     * @param string $originalJsonContent
     * @throws WrongContentException
     */
    public function treatPersonalForm(array $formContent, string $originalJsonContent)
    {
        if ($formContent['event_type'] !== 'form_response') {
            throw new WrongContentException("001");
        }
        if (!isset($formContent['form_response'])) {
            throw new WrongContentException("002");
        }
        $formResponse = $formContent['form_response'];
        if (!isset($formResponse['form_id']) || $formResponse['form_id'] !== $this->personalFormId) {
            throw new WrongContentException("003");
        }
        if (!isset($formResponse['token'])) {
            throw new WrongContentException("004");
        }
        if (!isset($formResponse['hidden']) || !isset($formResponse['hidden']['client_id'])) {
            throw new WrongContentException("005");
        }
        $user = $this->userRepository->getByClientId($formResponse['hidden']['client_id']);
        if (!$user instanceof PersonalUser) {
            throw new WrongContentException('006');
        }

        if($user->getAffinityAnswer() == null){
            $affinityAnswer = new AffinityAnswer($user,
                $formResponse['token'],
                $formResponse['form_id'],
                new \DateTime($formResponse['submitted_at']),
                $originalJsonContent
            );
            $user->setAffinityAnswer($affinityAnswer);
            $this->userRepository->update($user);
        }else{
            $user->getAffinityAnswer()->setToken($formResponse['token']);
            $user->getAffinityAnswer()->setFormId($formResponse['form_id']);
            $user->getAffinityAnswer()->setSubmittedAt(new \DateTime($formResponse['submitted_at']));
            $user->getAffinityAnswer()->setContent($originalJsonContent);
            $this->userRepository->update($user);
        }
    }
}