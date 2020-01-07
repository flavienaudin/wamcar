<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\YesNoDataTransformer;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\Type\SpecificField\YesNoType;
use AppBundle\Form\Type\Traits\AutocompleteableCityTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalUserInformationType extends UserInformationType
{
    use AutocompleteableCityTrait;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('phoneDisplay', YesNoType::class, [
                'required' => false
            ]);
        $builder->get('phoneDisplay')->addModelTransformer(new YesNoDataTransformer());
        $this->addAutocompletableCityField($builder, $builder->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class
        ]);
    }
}
