<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProUserProServiceSpecialityDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProUserProSpecialityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var ProUserProServiceSpecialityDTO $data */
            $data = $formEvent->getData();
            $form = $formEvent->getForm();
            $form->add('isSpeciality', CheckboxType::class, [
                'required' => false,
                'label' => $data->getProServiceName()
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProUserProServiceSpecialityDTO::class
        ]);
    }
}