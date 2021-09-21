<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DTO\ScriptSequenceDTO;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\VideoCoaching\ScriptShotType;

class ScriptSequenceType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('dialogue', TextareaType::class, [
                'required' => false,
            ])
            ->add('scene', TextareaType::class, [
                'required' => false,
            ])
            ->add('shot', EntityType::class, [
                'class' => ScriptShotType::class,
                'choice_label' => 'label',
                'required' => false,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ScriptSequenceDTO::class
        ]);
    }
}