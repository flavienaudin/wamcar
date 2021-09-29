<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Form\DTO\ScriptSectionDTO;
use AppBundle\Form\DTO\ScriptSequenceDTO;
use AppBundle\Form\DTO\ScriptVersionDTO;
use Wamcar\VideoCoaching\ScriptSection;
use Wamcar\VideoCoaching\ScriptSectionTypeRepository;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptSequenceRepository;
use Wamcar\VideoCoaching\ScriptVersion;
use Wamcar\VideoCoaching\ScriptVersionRepository;

class VideoProjectScriptService
{

    /** @var ScriptVersionRepository */
    private $scriptVersionRepository;
    /** @var ScriptSectionTypeRepository */
    private $scriptSectionTypeRepository;
    /** @var ScriptSequenceRepository */
    private $scriptSequenceRepoository;


    public function __construct(ScriptVersionRepository $scriptVersionRepository,
                                ScriptSectionTypeRepository $scriptSectionTypeRepository,
                                ScriptSequenceRepository $scriptSequenceRepoository)
    {
        $this->scriptVersionRepository = $scriptVersionRepository;
        $this->scriptSectionTypeRepository = $scriptSectionTypeRepository;
        $this->scriptSequenceRepoository = $scriptSequenceRepoository;
    }

    /**
     * Define the ScriptSection of the ScriptVersion according to Wamcar Formation
     * @param ScriptVersion $scriptVersion
     * @param ScriptVersionDTO $scriptVersionDTO
     * @return ScriptVersionDTO
     */
    public function setWamcarScriptTemplate(ScriptVersion $scriptVersion, ScriptVersionDTO $scriptVersionDTO)
    {

        // Accroche
        $accrocheType = $this->scriptSectionTypeRepository->findOneBy(['name' => 'Accroche']);
        if ($accrocheType) {
            $accrocheSectionDTO = new ScriptSectionDTO($scriptVersion);
            $accrocheSectionDTO->setType($accrocheType);
            $accrocheSectionDTO->setPosition(1);
            $scriptVersionDTO->addScriptSection($accrocheSectionDTO);
        }

        // Introduction
        $introductionType = $this->scriptSectionTypeRepository->findOneBy(['name' => 'Introduction']);
        if ($introductionType) {
            $introductionSectionDTO = new ScriptSectionDTO($scriptVersion);
            $introductionSectionDTO->setType($introductionType);
            $introductionSectionDTO->setPosition(2);
            $scriptVersionDTO->addScriptSection($introductionSectionDTO);
        }

        // Content
        $contentType = $this->scriptSectionTypeRepository->findOneBy(['name' => 'Contenu']);
        if ($contentType) {
            $contentSectionDTO = new ScriptSectionDTO($scriptVersion);
            $contentSectionDTO->setType($contentType);
            $contentSectionDTO->setPosition(3);
            $scriptVersionDTO->addScriptSection($contentSectionDTO);
        }

        // Outroduction
        $outroductionType = $this->scriptSectionTypeRepository->findOneBy(['name' => 'Outroduction']);
        if ($outroductionType) {
            $outroductionSectionDTO = new ScriptSectionDTO($scriptVersion);
            $outroductionSectionDTO->setType($outroductionType);
            $outroductionSectionDTO->setPosition(4);
            $scriptVersionDTO->addScriptSection($outroductionSectionDTO);
        }

        // Outroduction
        $callToActionType = $this->scriptSectionTypeRepository->findOneBy(['name' => 'Appels à l\'action']);
        if ($callToActionType) {
            $callToActionSectionDTO = new ScriptSectionDTO($scriptVersion);
            $callToActionSectionDTO->setType($callToActionType);
            $callToActionSectionDTO->setPosition(5);
            $scriptVersionDTO->addScriptSection($callToActionSectionDTO);
        }

        return $scriptVersionDTO;
    }

    /**
     * @param ScriptVersionDTO $scriptVersionDTO Les informations de la version du script
     * @return ScriptVersion
     * @throws \Exception
     */
    public function create(ScriptVersionDTO $scriptVersionDTO)
    {
        $scriptVersion = new ScriptVersion();
        $scriptVersion->setVideoProjectIteration($scriptVersionDTO->getVideoProjectIteration());
        $scriptVersion->setTitle($scriptVersionDTO->getTitle());

        // While no selection of template for script (TODO : add script template selection)
        // Default template is Wamcar
        // TODO : load from db the template (TODO : add Script Template Entity)
        $scriptVersionDTO = $this->setWamcarScriptTemplate($scriptVersion, $scriptVersionDTO);

        /** @var ScriptSectionDTO $scriptSectionDTO */
        foreach ($scriptVersionDTO->getScriptSections() as $scriptSectionDTO) {
            $scriptSection = new ScriptSection($scriptVersion);
            $scriptSection->setType($scriptSectionDTO->getType());
            $scriptSection->setPosition($scriptSectionDTO->getPosition());

            /** @var ScriptSequenceDTO $scriptSequenceDTO */
            foreach ($scriptSectionDTO->getScriptSequences() as $scriptSequenceDTO) {
                $scriptSection->addScriptSequence(ScriptSequenceDTO::createScriptSequenceFromDTO($scriptSequenceDTO));
            }
            $scriptVersion->addScriptSection($scriptSection);
        }

        $this->scriptVersionRepository->add($scriptVersion);
        return $scriptVersion;
    }

    /**
     * @param ScriptVersionDTO $scriptVersionDTO Les informations de la version du script
     * @param ScriptVersion $scriptVersion La version du script à éditer
     * @return ScriptVersion
     */
    public function updateMainInfo(ScriptVersionDTO $scriptVersionDTO, ScriptVersion $scriptVersion)
    {
        $scriptVersion->setTitle($scriptVersionDTO->getTitle());

        $this->scriptVersionRepository->update($scriptVersion);
        return $scriptVersion;
    }


    /**
     * @param ScriptVersionDTO $scriptVersionDTO Les informations de la version du script
     * @param ScriptVersion $scriptVersion La version du script à éditer
     * @return ScriptVersion
     */
    public function updateScriptSections(ScriptVersionDTO $scriptVersionDTO, ScriptVersion $scriptVersion)
    {
        foreach ($scriptVersionDTO->getScriptSections() as $scriptSectionDTO) {
            $currentScripSection = $scriptVersion->getScriptSectionById($scriptSectionDTO->getId());
            if ($currentScripSection) {
                foreach ($scriptSectionDTO->getScriptSequences() as $scriptSequenceDTO) {
                    $currentScriptSequence = null;
                    if ($scriptSequenceDTO->getId()) {
                        $currentScriptSequence = $currentScripSection->getScriptSequenceById($scriptSequenceDTO->getId());

                        if ($scriptSequenceDTO->isEmpty()) {
                            $currentScripSection->removeScriptSequence($currentScriptSequence);
                            $currentScriptSequence = null;
                        }
                    } elseif (!$scriptSequenceDTO->isEmpty()) {
                        $currentScriptSequence = new ScriptSequence($currentScripSection);
                        $currentScripSection->addScriptSequence($currentScriptSequence);
                    }
                    if ($currentScriptSequence) {
                        if ($scriptSequenceDTO->getPosition()) {
                            $currentScriptSequence->setPosition($scriptSequenceDTO->getPosition());
                        }
                        $currentScriptSequence->setDialogue($scriptSequenceDTO->getDialogue());
                        $currentScriptSequence->setScene($scriptSequenceDTO->getScene());
                        $currentScriptSequence->setShot($scriptSequenceDTO->getShot());
                    }

                }
            }
        }
        $this->scriptVersionRepository->update($scriptVersion);
        return $scriptVersion;
    }

    /**
     * @param ScriptVersion $scriptVersion La version du script à supprimer.
     */
    public function delete(ScriptVersion $scriptVersion)
    {
        $this->scriptVersionRepository->remove($scriptVersion);
    }

    /**
     * @param ScriptSequenceDTO $scriptSequenceDTO
     * @param ScriptSequence $scriptSequence
     */
    public function updateScriptsequence(ScriptSequenceDTO $scriptSequenceDTO, ScriptSequence $scriptSequence)
    {
        $scriptSequence->setDialogue($scriptSequenceDTO->getDialogue());
        $scriptSequence->setScene($scriptSequenceDTO->getScene());
        $scriptSequence->setShot($scriptSequenceDTO->getShot());
        $this->scriptSequenceRepoository->update($scriptSequence);
    }
}