<?php


namespace AppBundle\Twig;


use AppBundle\Form\Type\SearchProType;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wamcar\User\ProServiceCategory;

class FormExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('proServiceCategoryFieldName', [$this, 'getProServiceCategoryFieldName'])
        ];
    }

    /**
     * Get the field name of a ProServiceCategory
     * @param ProServiceCategory $proServiceCategory
     * @return string
     */
    public function getProServiceCategoryFieldName(ProServiceCategory $proServiceCategory)
    {
        return SearchProType::getCategoryFieldName($proServiceCategory);
    }
}