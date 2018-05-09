<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DTO\VehicleOfferDTO;
use AppBundle\Form\Type\SpecificField\AmountType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType, TextareaType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Funding;
use Wamcar\Vehicle\Enum\Guarantee;

class VehicleOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', AmountType::class, [
                'error_bubbling' => true,
            ])
            ->add('catalogPrice', AmountType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('discount', TextType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
            ->add('guarantee', ChoiceType::class, [
                'choices' => Guarantee::toArray(),
                'error_bubbling' => true,
                'choice_translation_domain' => 'enumeration',
                'required' => false
            ])
            ->add('otherGuarantee', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('funding', ChoiceType::class, [
                'choices' => Funding::toArray(),
                'error_bubbling' => true,
                'choice_translation_domain' => 'enumeration',
                'required' => false
            ])
            ->add('otherFunding', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('additionalServices', TextareaType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('reference', TextType::class, [
                'error_bubbling' => true,
                'required' => false
            ])
        ;

        $builder->get('guarantee')->addModelTransformer(new EnumDataTransformer(Guarantee::class));
        $builder->get('funding')->addModelTransformer(new EnumDataTransformer(Funding::class));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VehicleOfferDTO::class,
            'translation_domain' => 'registration'
        ]);
    }


}
