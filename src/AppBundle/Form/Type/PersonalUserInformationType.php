<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\UserInformationDTO;
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

        $this->addAutocompletableCityField($builder, $builder->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class,
            'translation_domain' => 'user',
            'label_format' => 'user.field.%name%.label'
        ]);
    }
}
