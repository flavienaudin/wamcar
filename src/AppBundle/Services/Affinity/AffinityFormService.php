<?php


namespace AppBundle\Services\Affinity;


use AppBundle\Form\Type\AffinityQuestion\QuestionType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Router;

class AffinityFormService
{
    const PROFORM_SESSION_KEY = '_proform/';


    /** @var FormFactory */
    private $formFactory;
    /** @var Router */
    private $router;
    /** @var SessionInterface */
    private $session;

    /**
     * AffinityFormService constructor.
     * @param FormFactory $formFactory
     * @param Router $router
     * @param SessionInterface $session
     */
    public function __construct(FormFactory $formFactory, Router $router, SessionInterface $session)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->session = $session;
    }


    /**
     * @param string|null $questionName
     * @return FormInterface
     * @throws \Exception
     */
    public function getQuestionForm(?string $questionName): FormInterface
    {
        if (empty($questionName)) {
            $questionName = QuestionType::PRO_FORM['initial_question'];
        }
        if (isset(QuestionType::PRO_FORM['questions'][$questionName])) {
            $options = [
                'questionName' => $questionName
            ];

            $currentQuestionData = $this->session->get(self::PROFORM_SESSION_KEY . $questionName, null);
            return $this->formFactory->create(QuestionType::class, [$questionName => $currentQuestionData], $options);
        }
        // ERREUR
        throw new \Exception('[Get Question Form] Wrong form config: unknown question : ' . $questionName);
    }

    /**
     * @param string|null $currentQuestionName
     * @param bool $previous
     * @return FormInterface|null
     * @throws \Exception
     */
    public function nextQuestion(?string $currentQuestionName, bool $previous = false): ?FormInterface
    {
        if (empty($currentQuestionName)) {
            $nextQuestionName = QuestionType::PRO_FORM['initial_question'];
        } else if (isset(QuestionType::PRO_FORM['questions'][$currentQuestionName])) {
            if ($previous) {
                $nextQuestionName = QuestionType::PRO_FORM['questions'][$currentQuestionName]['previous'];
            } else {
                $nextQuestionName = QuestionType::PRO_FORM['questions'][$currentQuestionName]['next'];
            }
            if ($nextQuestionName == null) {
                // Form end
                return null;
            }
            if (is_array($nextQuestionName) && isset($nextQuestionName['function_name'])) {
                $functionParams = [];
                $functionParams['currentQuestionData'] = $this->session->get(self::PROFORM_SESSION_KEY . $currentQuestionName);
                if (isset($nextQuestionName['function_params'])) {
                    if (isset($nextQuestionName['function_params']['related_question'])) {
                        $functionParams['relatedQuestionData'] = $this->session->get(self::PROFORM_SESSION_KEY . $nextQuestionName['function_params']['related_question']);
                    }
                }
                $nextQuestionName = QuestionType::{$nextQuestionName['function_name']}($functionParams);
            }
        }

        if (!isset($nextQuestionName)) {
            // ERREUR
            throw new \Exception('Wrong form config: unknown ' . ($previous ? 'previous' : 'next') .
                ' question name for current question : ' . $currentQuestionName);
        }

        return $this->getQuestionForm($nextQuestionName);
    }
}