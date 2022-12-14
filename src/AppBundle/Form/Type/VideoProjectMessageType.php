<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\VideoProjectMessageDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VideoProjectMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('attachments', CollectionType::class, [
                'required' => false,
                'entry_type' => FileType::class,
                'entry_options' => [
                    'required' => false
                ],
                'allow_add' => true,
                'label' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VideoProjectMessageDTO::class
        ]);
    }
}