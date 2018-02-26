<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProjectVehicleDTO;
use AppBundle\Utils\MileageChoice;
use AppBundle\Utils\YearsChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectVehicleType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $availableMakes = $options['available_makes'] ?? [];
        $availableModels = $options['available_models'] ?? [];

        //Give the index in the collection
        $indexCollection = $builder->getName();

        $makes = $availableMakes['make'] ?? [];
        $models = $availableModels[$indexCollection]['model'] ?? [];

        $builder
            ->add('id', HiddenType::class)
            ->add('make', ChoiceType::class, [
                'choices' => $makes,
                'placeholder' => count($makes) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $models,
                'placeholder' => count($models) === 1 ? false : '',
                'error_bubbling' => true,
            ])
            ->add('yearMin', ChoiceType::class, [
                'choices' => YearsChoice::getLastYears(58),
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('mileageMax', ChoiceType::class, [
                'choices' => MileageChoice::getMileageMax(),
                'required' => false,
                'error_bubbling' => true,
            ]);

        // Replace choice by text to prevent validation, as fields are dynamically filled
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            foreach (['make', 'model'] as $field) {
                if ($form->has($field)) {
                    $form->remove($field);
                    $form->add($field, TextType
                    ::class);
                }
            }


        });
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
        $resolver->setRequired('available_makes');
        $resolver->setRequired('available_models');
    }
}
