<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\EnumDataTransformer;
use AppBundle\Form\DataTransformer\YesNoDataTransformer;
use AppBundle\Form\DTO\VehicleOfferDTO;
use AppBundle\Form\DTO\VehicleSpecificsDTO;
use AppBundle\Form\Type\SpecificField\AmountType;
use AppBundle\Form\Type\SpecificField\StarType;
use AppBundle\Form\Type\SpecificField\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType, ChoiceType, DateType, IntegerType, TextareaType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\Vehicle\Enum\Funding;
use Wamcar\Vehicle\Enum\Guarantee;
use Wamcar\Vehicle\Enum\MaintenanceState;
use Wamcar\Vehicle\Enum\SafetyTestDate;
use Wamcar\Vehicle\Enum\SafetyTestState;

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
                'required' => false
            ])
            ->add('otherGuarantee', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('funding', ChoiceType::class, [
                'choices' => Funding::toArray(),
                'error_bubbling' => true,
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
