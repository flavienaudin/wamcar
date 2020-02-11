<?php


namespace AppBundle\Form\Type\SpecificField;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\ProService;

class ProServiceCategoryServicesSelectType extends EntityType
{


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, ['category_description' => $options['category_description']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => ProService::class,
            'multiple' => true,
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'slug',
            'category_description' => null
        ]);
    }
}