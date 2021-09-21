<?php


namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\ScriptVersionDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScriptVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('scriptSections', CollectionType::class, [
                'label' => false,
                'entry_type' => ScriptSectionFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => true,
                'required' => true,
                'error_bubbling' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ScriptVersionDTO::class
        ]);
    }
}