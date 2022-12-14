<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\UserYoutubePlaylistInsertDTO;
use AppBundle\Form\Validator\Constraints\YoutubePlaylistExisting;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class YoutubePlaylistInsertType extends VideosInsertType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('playlistId', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(), new YoutubePlaylistExisting()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserYoutubePlaylistInsertDTO::class
        ]);
    }
}