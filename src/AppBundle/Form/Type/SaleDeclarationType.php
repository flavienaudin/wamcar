<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\SaleDeclarationDTO;
use AppBundle\Form\Validator\Constraints\SaleDeclaration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

class SaleDeclarationType extends AbstractType
{

    /** @var LeadRepository */
    private $leadRepository;
    /** @var ProVehicleRepository */
    private $proVehicleRepository;

    /**
     * SaleDeclarationType constructor.
     * @param LeadRepository $leadRepository
     * @param ProVehicleRepository $proVehicleRepository
     */
    public function __construct(LeadRepository $leadRepository, ProVehicleRepository $proVehicleRepository)
    {
        $this->leadRepository = $leadRepository;
        $this->proVehicleRepository = $proVehicleRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerFirstName', TextType::class, [
                'required' => false
            ])
            ->add('customerLastName', TextType::class, [
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
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                $form = $formEvent->getForm();
                /** @var SaleDeclarationDTO $saleDeclarationDTO */
                $saleDeclarationDTO = $formEvent->getData();

                $proUserLeads = $this->leadRepository->findBy(['proUser' => $saleDeclarationDTO->getProUserSeller()]);
                $proUserVehicles = $this->proVehicleRepository->findDeletedProVehiclesByProUser($saleDeclarationDTO->getProUserSeller());

                $form
                    ->add('leadCustomer', EntityType::class, [
                        'class' => Lead::class,
                        'choices' => $proUserLeads,
                        'required' => false,
                        'choice_label' => 'fullname',
                        'choice_attr' => function (Lead $lead, $key, $value) {
                            return [
                                'data-firstname' => $lead->getFirstName(),
                                'data-lastname' => $lead->getLastName()
                            ];
                        }
                    ])
                    ->add('proVehicle', EntityType::class, [
                        'class' => ProVehicle::class,
                        'choices' => $proUserVehicles,
                        'required' => false,
                        'choice_label' => function (ProVehicle $proVehicle) {
                            return $proVehicle->getName() . ' - ' . $proVehicle->getPrice() . '€ (' . $proVehicle->getSlug() . ')';
                        },
                        'choice_attr' => function (ProVehicle $proVehicle, $key, $value) {
                            return [
                                'data-price' => $proVehicle->getPrice()
                            ];
                        }
                    ]);
            });

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