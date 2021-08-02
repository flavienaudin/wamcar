<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\VideoVersionDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class VideoVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => new NotBlank()
            ])
            ->add('url', UrlType::class, [
                'required' => true,
                'constraints' => [new NotBlank(), new Url()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VideoVersionDTO::class
        ]);
    }
}