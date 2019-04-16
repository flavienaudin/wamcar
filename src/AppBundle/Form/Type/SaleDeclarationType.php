<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\SaleDeclarationDTO;
use AppBundle\Form\Validator\Constraints\SaleDeclaration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaleDeclarationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sellerFirstName', TextType::class, [
                'required' => true
            ])
            ->add('sellerLastName', TextType::class, [
                'required' => false
            ])
            ->add('buyerFirstName', TextType::class, [
                'required' => false
            ])
            ->add('buyerLastName', TextType::class, [
                'required' => false
            ])
            ->add('transactionSaleAmount', IntegerType::class, [
                'required' => false
            ])
            ->add('transactionPartExchangeAmount', IntegerType::class, [
                'required' => false
            ])
            ->add('transactionCommentary', TextareaType::class, [
                'required' => false
            ])
            ->add('proUserSellerId', HiddenType::class)
            ->add('leadBuyerId', HiddenType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SaleDeclarationDTO::class,
            'constraints' => [new SaleDeclaration()]
        ]);
    }
}