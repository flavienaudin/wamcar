<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProjectVehicleDTO;
use AppBundle\Utils\MileageChoice;
use AppBundle\Utils\YearsChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectVehicleType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $availableValues = $options['available_values'] ?? [];

        $builder
            ->add('make', ChoiceType::class, [
                'choices' => $availableValues['make'] ?? [],
                'placeholder' => count($availableValues['make'] ?? []) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $availableValues['model'] ?? [],
                'placeholder' => count($availableValues['model'] ?? []) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('yearMax', ChoiceType::class, [
                'choices' => YearsChoice::getLastYears(),
                'error_bubbling' => true,
            ])
            ->add('mileageMax', ChoiceType::class, [
                'choices' => MileageChoice::getMileageMax(),
                'required' => false,
                'error_bubbling' => true,
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectVehicleDTO::class,
            'translation_domain' => 'user',
            'label_format' => 'user.field.%name%.label'
        ]);
    }
}
