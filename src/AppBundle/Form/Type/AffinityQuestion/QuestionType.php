<?php


namespace AppBundle\Form\Type\AffinityQuestion;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Router;
use Wamcar\User\Title;

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
                    ],
                ],
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
                    ],
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
        $questionName = $options['attr']['questionName'];

        $builder
            ->add('current_question_name', HiddenType::class, [
                'data' => $questionName
            ])
            ->add($questionName, self::PRO_FORM['questions'][$questionName]['formType'],
                self::PRO_FORM['questions'][$questionName]['formOptions'] ?? []);

        /*if (isset(self::PRO_FORM['questions'][$questionName]['enumDataTransformer'])) {
            dump(self::PRO_FORM['questions'][$questionName]['enumDataTransformer']);
            $builder->get($questionName)->addModelTransformer(new EnumDataTransformer(self::PRO_FORM['questions'][$questionName]['enumDataTransformer']));
        }*/

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
    }


}