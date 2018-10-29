<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Validator\Constraints\Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\BaseUser;

class MessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BaseUser $user */
        $user = $options['user'];

        $builder
            ->add('content', TextareaType::class, [
                'required' => false
            ])
            ->add('attachments', CollectionType::class, [
                'entry_type' => FileType::class,
                'allow_add' => true,
                'label' => false
            ])
            ->add('send', SubmitType::class);

        /*$userVehicles = $user->getVehicles();
        if ($userVehicles != null && count($userVehicles) > 0) {*/
        $builder->add('selectVehicle', SubmitType::class);
        /*} else {
            TODO : Permettre la création d'un véhicule directement mais gére le cas multi-garages : Cf ConversationController->redirectionFromSubmitButton()
            $builder->add('createVehicle', SubmitType::class);
        }*/

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var MessageDTO $messageDTO */
            $messageDTO = $event->getData();
            $form = $event->getForm();

            if ($messageDTO->vehicle) {
                $form->add('vehicle', EntityType::class, [
                    'class' => get_class($messageDTO->vehicle),
                    'label' => false,
                    'required' => false,
                    'choice_label' => 'name'
                ]);
            }

            if ($messageDTO->isFleet) {
                $form->add('isFleet', CheckboxType::class, [
                    'label' => false,
                    'required' => false
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageDTO::class,
            'translation_domain' => 'message',
            'label_format' => 'message.field.%name%.label',
            'user' => null,
            'constraints' => new Message()
        ]);
    }
}
