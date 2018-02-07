<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Validator\Constraints\UniqueGarageSiren;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('vehicleHeaderId', TextType::class)
            ->add('content', TextareaType::class)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageDTO::class,
            'translation_domain' => 'message',
            'label_format' => 'message.field.%name%.label'
        ]);
    }
}
