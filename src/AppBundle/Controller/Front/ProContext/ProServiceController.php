<?php


namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\ProUserProSpecialitiesDTO;
use AppBundle\Form\Type\ProUserSpecialitiesSelectType;
use AppBundle\Form\Type\UserProServicesSelectType;
use AppBundle\Services\User\UserEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Wamcar\User\ProUser;

class ProServiceController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var UserEditionService */
    private $userEditionService;

    /**
     * ProServiceController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserEditionService $userEditionService
     */
    public function __construct(FormFactoryInterface $formFactory, UserEditionService $userEditionService)
    {
        $this->formFactory = $formFactory;
        $this->userEditionService = $userEditionService;
    }

    public function editProUserProServicesAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $currentUser = $this->getUser();
        if (!$currentUser instanceof ProUser) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.warning.user.unauthorized.edit_prouser_proservices'
            );
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $userProServicesByCategory = $currentUser->getProServicesByCategory();
        $selectProServicesByCategoryForm = $this->formFactory->create(UserProServicesSelectType::class, $userProServicesByCategory);
        $selectProServicesByCategoryForm->handleRequest($request);
        if ($selectProServicesByCategoryForm->isSubmitted() && $selectProServicesByCategoryForm->isValid()) {
            $selectedProServices = [];
            foreach ($selectProServicesByCategoryForm->getData() as $category => $services) {
                $selectedProServices = array_merge($selectedProServices, $services);
            }
            $this->userEditionService->updateProServicesOfUser($currentUser, $selectedProServices);
            if ($currentUser->getProUserProServices()->count() > 0) {
                return $this->redirectToRoute('front_edit_prouser_specialities');
            } else {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.user.edit.prouser_services');
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }
        return $this->render('/front/Seller/edit_proservices.html.twig', [
            'selectProServicesByCategoryForm' => $selectProServicesByCategoryForm->createView()
        ]);
    }

    public function editProUserSpecialitiesAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $currentUser = $this->getUser();
        if (!$currentUser instanceof ProUser) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.warning.user.unauthorized.edit_prouser_specialities'
            );
            return $this->redirectToRoute('front_view_current_user_info');
        }

        if ($currentUser->getProUserProServices()->count() === 0) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.error.user.edit.prouser_specialities.no_service'
            );
            return $this->redirectToRoute('front_edit_prouser_proservices');
        }

        $proUserProSpecialitiesDTO = new ProUserProSpecialitiesDTO($currentUser);
        $selectSpecialitiesForm = $this->formFactory->create(ProUserSpecialitiesSelectType::class, $proUserProSpecialitiesDTO);
        $selectSpecialitiesForm->handleRequest($request);
        if ($selectSpecialitiesForm->isSubmitted()) {
            if ($selectSpecialitiesForm->isValid()) {
                $this->userEditionService->updateProUserSpecialities($currentUser, $selectSpecialitiesForm->getData());
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.user.edit.prouser_specialities');
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        return $this->render('/front/Seller/edit_prospecialities.html.twig', [
            'selectSpecialitiesForm' => $selectSpecialitiesForm->createView()
        ]);
    }
}