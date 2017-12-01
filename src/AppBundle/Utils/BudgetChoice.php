<?php


namespace AppBundle\Utils;


class BudgetChoice
{
    /**
     * Return a list of budget
     *
     * @return array
     */
    public static function getList(): array
    {
        return [
            '10 000 €' => '10000',
            '20 000 €' => '20000',
            '30 000 €' => '30000'
        ];
    }
}
