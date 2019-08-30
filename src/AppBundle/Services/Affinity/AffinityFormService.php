<?php


namespace AppBundle\Services\Affinity;


use AppBundle\Form\Type\AffinityQuestion\QuestionType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Router;

class AffinityFormService
{


    /** @var FormFactory */
    private $formFactory;
    /** @var Router */
    private $router;

    /**
     * AffinityFormService constructor.
     * @param FormFactory $formFactory
     * @param Router $router
     */
    public function __construct(FormFactory $formFactory, Router $router)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }


    /**
     * @param string|null $questionName
     * @param array|null $data
     * @return FormInterface
     * @throws \Exception
     */
    public function getQuestionForm(?string $questionName): FormInterface
    {
        if(empty($questionName)){
            $questionName = QuestionType::PRO_FORM['initial_question'];
        }
        if (isset(QuestionType::PRO_FORM['questions'][$questionName])) {
            /*$options = [
                'action' => $this->router->generate('proto_affinity_internal_pro_submit_form', [],
                    UrlGenerator::ABSOLUTE_URL),
                'method' => Request::METHOD_POST
            ];*/
            $attr = [
                'questionName' => $questionName
            ];
            return $this->formFactory->create(QuestionType::class, [], ['attr' => $attr]);

            /*$form->add('current_question_name', HiddenType::class, [
                'data' => $questionName
            ]);

            $form->add($questionName, self::PRO_FORM['questions'][$questionName]['formType'],
                self::PRO_FORM['questions'][$questionName]['formOptions'] ?? []);



            return $form;*/
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
        }

        if (!isset($nextQuestionName)) {
            // ERREUR
            throw new \Exception('Wrong form config: unknown ' . ($previous ? 'next' : 'previous') . ' question name for current question : ' . $currentQuestionName);
        }

        return $this->getQuestionForm($nextQuestionName);

        /*$options = [
            'action' => $this->router->generate('proto_affinity_internal_pro_submit_form', [],
                UrlGenerator::ABSOLUTE_URL),
            'method' => Request::METHOD_POST
        ];
        $form = $this->formFactory->create(QuestionType::class, [], $options);

        $form->add('current_question_name', HiddenType::class, [
            'data' => $nextQuestionName
        ]);
        $form->add($nextQuestionName, self::PRO_FORM['questions'][$nextQuestionName]['formType'],
            self::PRO_FORM['questions'][$nextQuestionName]['formOptions'] ?? []);


        return $form;*/
    }
}