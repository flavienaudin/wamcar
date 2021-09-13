<?php


namespace AppBundle\Services\App;


use AppBundle\Doctrine\Entity\FooterLink;
use AppBundle\Doctrine\Repository\FooterLinkRepository;

class FooterLinkService
{

    /** @var FooterLinkRepository */
    private $footerLinkRepository;

    /**
     * FooterLinkService constructor.
     * @param FooterLinkRepository $footerLinkRepository
     */
    public function __construct(FooterLinkRepository $footerLinkRepository)
    {
        $this->footerLinkRepository = $footerLinkRepository;
    }

    /**
     * @param FooterLink $footerLink
     */
    public function deleteFooterLink(FooterLink $footerLink)
    {
        $this->footerLinkRepository->remove($footerLink);
    }
}