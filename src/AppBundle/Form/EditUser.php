<?php


namespace AppBundle\Form;


use AppBundle\DTO\Form\EditUserData;
use AppBundle\Form\Traits\HasPasswordTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUser extends BaseUser
{
    use HasPasswordTrait;

    protected $isPasswordRequired = false;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => EditUserData::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'wamcar_edit_user';
    }
}
