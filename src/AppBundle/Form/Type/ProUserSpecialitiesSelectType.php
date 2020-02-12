<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserProSpecialitiesDTO;
use AppBundle\Form\Validator\Constraints\MaxSpecialitySelection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProUserSpecialitiesSelectType extends AbstractType
{
    const MAX_SPECIALITY_SELECTION = 3;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('proUserProServicesForSpecialities', CollectionType::class, [
            'entry_type' => ProUserProSpecialityType::class,
            'allow_add' => false,
            'allow_delete' => false,
            'constraints' => [new  MaxSpecialitySelection([
                'max' => $options['max_selection']
            ])]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProUserProSpecialitiesDTO::class,
            'max_selection' => self::MAX_SPECIALITY_SELECTION
        ]);
    }
}