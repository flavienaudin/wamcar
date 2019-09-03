<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuestionType extends AbstractType
{

    const PRO_FORM = [
        'initial_question' => 'firstname',
        'questions' => [
            'firstname' => [
                'formType' => TextType::class,
                'formOptions' => [
                    'required' => true,
                    'label' => 'Quel est votre prénom ?',
                    'attr' => [
                        'placeholder' => 'Prénom'
                    ]
                ],
                'formConstraints' => [NotBlank::class => []],
                'previous' => null,
                'next' => 'lastname'
            ],
            'lastname' => [
                'formType' => TextType::class,
                'formOptions' => [
                    'required' => false,
                    'label' => 'Quel est votre nom ?',
                    'attr' => [
                        'placeholder' => 'Nom'
                    ]
                ],
                'previous' => 'firstname',
                'next' => 'title'
            ],
            'title' => [
                'formType' => TitleQuestion::class,
                'previous' => 'lastname',
                'next' => 'function'
            ],
            'function' => [
                'formType' => SellerFunctionQuestion::class,
                'previous' => 'title',
                'next' => 'expertise_fields'
            ],
            'expertise_fields' => [
                'formType' => ExpertiseFieldsQuestion::class,
                'previous' => 'function',
                'next' => 'contact_means'
            ],
            'contact_means' => [
                'formType' => ContactMeansQuestion::class,
                'previous' => 'expertise_fields',
                'next' => [
                    'function_name' => 'contactMeansNext'
                ]
            ],
            'phone_number' => [
                'formType' => TextType::class,
                'formOptions' => [
                    'required' => true,
                    'label' => 'Votre numéro de téléphone ?',
                    'attr' => ['pattern' => '^0\d{9}$'],
                    'invalid_message' => 'user.profile.edit.form.phonePro.invalid_format'
                ],
                'previous' => 'contact_means',
                'next' => 'availibities'
            ],
            'availibities' => [
                'formType' => ChoiceType::class,
                'formOptions' => [
                    'required' => false,
                    'label' => 'Vous êtes disponible plutôt',
                    'choices' => [
                        'morning' => 'Le matin (de 8h à 12h)',
                        'noon' => 'Le midi (de 12h à 14h)',
                        'afternoon' => 'L\'après-midi (de 14h à 18h)',
                        'evening' => 'Le soir (de 18h à 20h)',
                        'week' => 'En semaine',
                        'week-end' => 'Le week-end'
                    ]
                ],
                'previous' => [
                    'function_name' => 'availibitiesPrevious',
                    'function_params' => [
                        'related_question' => 'contact_means'
                    ]
                ],
                'next' => null
            ]
        ]
    ];

    /** @var Router */
    private $router;

    /**
     * QuestionType constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questionName = $options['questionName'];

        $formOptions = self::PRO_FORM['questions'][$questionName]['formOptions'] ?? [];
        if (isset(self::PRO_FORM['questions'][$questionName]['formConstraints'])) {
            $constraints = [];
            foreach (self::PRO_FORM['questions'][$questionName]['formConstraints'] as $constraintClass => $constraintParam) {
                if (!empty($constraintParam)) {
                    $constraints[] = new $constraintClass($constraintParam);
                } else {
                    $constraints[] = new $constraintClass();
                }
            }

            $formOptions['constraints'] = $constraints;
        }

        $builder
            ->add('current_question_name', HiddenType::class, [
                'data' => $questionName
            ])
            ->add($questionName, self::PRO_FORM['questions'][$questionName]['formType'], $formOptions);

        // Submit buttons : Previous / Next
        if (self::PRO_FORM['questions'][$questionName]['previous'] != null) {
            $builder->add('previous', SubmitType::class, [
                'label' => 'Précédent',
                'attr' => ['class' => 'button']
            ]);
        }
        $builder->add('next', SubmitType::class, [
            'label' => 'Suivant',
            'attr' => ['class' => 'js-default_submit button']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => $this->router->generate('proto_affinity_internal_pro_submit_form', [],
                UrlGenerator::ABSOLUTE_URL),
            'method' => Request::METHOD_POST
        ]);
        $resolver->setDefined('questionName');
        $resolver->setAllowedTypes('questionName', 'string');
    }

    public static function contactMeansNext(array $data)
    {
        if (in_array('phone_call', $data['currentQuestionData'])
            or in_array('sms', $data['currentQuestionData'])) {
            return 'phone_number';
        }
        else{
            return 'availibities';
        }
    }
    public static function availibitiesPrevious(array $data)
    {
        if (in_array('phone_call', $data['relatedQuestionData'])
            or in_array('sms', $data['relatedQuestionData'])) {
            return 'phone_number';
        }
        else{
            return 'contact_means';
        }
    }

}