<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\VideoProjectViewersDTO;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Utils\AccentuationUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProUser;

class VideoProjectShareType extends AbstractType
{

    /** @var PathUserPicture */
    private $pathUserPicture;

    /**
     * VideoProjectShareType constructor.
     * @param PathUserPicture $pathUserPicture
     */
    public function __construct(PathUserPicture $pathUserPicture)
    {
        $this->pathUserPicture = $pathUserPicture;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('coworkers', VideoProjectCoworkerType::class, [
            'choices' => $options['coworkers'],
            'choice_label' => function (ProUser $coworker) {
                $imagePath = $this->pathUserPicture->getPath($coworker->getAvatar(), 'user_mini_thumbnail', AccentuationUtils::remove($coworker->getFullName()));

                return '<img src="' . $imagePath . '" alt="' . $coworker->getFullName() . '" title="' . $coworker->getFullName() . '">'
                    . $coworker->getFullName();
            }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => VideoProjectViewersDTO::class]);
        $resolver->setRequired('coworkers');
    }
}