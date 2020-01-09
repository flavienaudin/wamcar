<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ProPresentationVideoDTO;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Regex;

class ProPresentationVideoType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * ProPresentationVideoType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('videoTitle', TextType::class, [
                'required' => false
            ])
            ->add('youtubeVideoUrl', TextType::class, [
                'required' => false,
                'attr' => [
                    'pattern' => '^(https:\/\/www.youtube.com\/watch\?v=|https:\/\/youtu.be\/){1}[a-zA-Z0-9]+'
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/(https:\/\/www.youtube.com\/watch\?v=|https:\/\/youtu.be\/){1}[a-zA-Z0-9]+/',
                        'message' => $this->translator->trans('user.profile.edit.form.video.youtubeVideoUrl.invalidMEssage')
                    ])
                ],

            ])
            ->add('videoText', CKEditorType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'data_class' => ProPresentationVideoDTO::class
        ]);
    }


}