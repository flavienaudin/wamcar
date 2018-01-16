<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\VehiclePictureDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiclePictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', HiddenType::class, [
            'label' => false,
            'error_bubbling' => true,
        ]);
        $builder->add('file', FileType::class, [
            'label' => false,
            'error_bubbling' => true,
        ]);
        $builder->add('caption', TextareaType::class, [
            'label' => false,
            'error_bubbling' => true,
        ]);
        $builder->add('isRemoved', CheckboxType::class, [
            'label' => false,
            'error_bubbling' => true,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VehiclePictureDTO::class,
            'translation_domain' => 'registration'
        ]);
    }


}
