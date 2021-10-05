<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\VideoProjectPictureDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoProjectPictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label' => false,
            'error_bubbling' => true,
            'required' => false,
        ]);
        $builder->add('isRemoved', CheckboxType::class, [
            'label' => false,
            'error_bubbling' => true,
            'required' => false,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VideoProjectPictureDTO::class
        ]);
    }


}
