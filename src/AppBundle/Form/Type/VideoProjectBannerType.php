<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\VideoProjectDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoProjectBannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('banner', VideoProjectPictureType::class, [
            'error_bubbling' => true
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VideoProjectDTO::class
        ]);
    }
}