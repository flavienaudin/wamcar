<?php

namespace AppBundle\Form\Type;


use AppBundle\Form\DTO\BoostPagesDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class BoostPagesType extends AbstractType
{

    /** @var RouterInterface $router */
    private $router;

    /**
     * BoostPagesType constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var BoostPagesDTO $data */
            $data = $formEvent->getData();
            $form = $formEvent->getForm();

            $urls = [];
            if($data->getUser() instanceof ProUser) {
                $urls['profilePage'] = $this->router->generate('front_view_pro_user_info', [
                    'slug' => $data->getUser()->getSlug()
                ]);
                array_map($data->getUser()->getVehicle(), function (ProVehicle $vehicle) {
                    // $urls[$vehicle->getId()] = $this->router
                });
            }elseif($data->getUser() instanceof PersonalUser){
                $urls['profilePage'] = $this->router->generate('front_view_personal_user_info', [
                    'slug' => $data->getUser()->getSlug()
                ]);
                array_map($data->getUser()->getVehicle(), function (PersonalVehicle $vehicle) {

                });
            }

            $form->add('urls', ChoiceType::class, [
                'choices' => $urls
            ]);

        });


    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BoostPagesDTO::class
        ]);
    }

}