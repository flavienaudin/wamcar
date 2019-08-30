<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\ProtoAffinityFormProAnwserDTO;
use AppBundle\Form\DTO\ProVehicleDTO;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Title;

class ProtoAffinityFormProAnwserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => Title::toArray(),
                'choice_label' => function ($value) {
                    return 'enum.title.' . strtolower($value);
                },
                'translation_domain' => 'enumeration'
            ])
            ->add('function')
            ->add('specialities', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Mecano' => 'mecano',
                    'Commercial' => 'seller',
                    'Directeur' => 'director',
                    'Chef VO' => 'vo_seller'
                ]
            ])
            ->add('contactMeans', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Messagerie interne sécurisée' => 'conversation',
                    'SMS' => 'sms',
                    'Appel téléphonique' => 'phone_call',
                    'E-mail' => 'email'
                ]
            ])/*->add('category', EntityType::class,[
                'mapped' => false
            ])*/
        ;

        $builder->get('contactMeans')->addEventListener(FormEvents::SUBMIT, function (FormEvent $formEvent) {
            $form = $formEvent->getForm();
            /** @var ProtoAffinityFormProAnwserDTO $answerData */
            $answerData = $formEvent->getData();
            if (in_array('sms', $answerData->contactMeans)
                or in_array('phone_call', $answerData->contactMeans)) {
                $form->getParent()->add('phoneNumber');
            }
        });

        $builder->get('title')->addModelTransformer(new EnumDataTransformer(Title::class));
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProtoAffinityFormProAnwserDTO::class
        ]);
    }
}