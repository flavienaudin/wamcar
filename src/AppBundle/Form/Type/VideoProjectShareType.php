<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class VideoProjectShareType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emails', ChoiceType::class, [
            'multiple' => true
        ]);

        // Replace choice by text to prevent validation, as fields are dynamically filled
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($form->has('emails') && isset($data['emails'])) {
                $form->remove('emails');
                $form->add('emails', ChoiceType::class, [
                    'choices' => $data['emails'],
                    'multiple' => true
                ]);
            }
        });
    }
}