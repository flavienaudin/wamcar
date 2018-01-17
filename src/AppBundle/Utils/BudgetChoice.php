<?php


namespace AppBundle\Utils;


class BudgetChoice
{
    /**
     * Return a list of budget
     *
     * @return array
     */
    public static function getListMax(): array
    {
        return [
            '250 €' => '250',
            '500 €' => '500',
            '750 €' => '750',
            '1 000 €' => '1000',
            '1 500 €' => '1500',
            '2 000 €' => '2000',
            '2 500 €' => '2500',
            '3 000 €' => '3000',
            '3 500 €' => '3500',
            '4 000 €' => '4000',
            '4 500 €' => '4500',
            '5 000 €' => '5000',
            '5 500 €' => '5500',
            '6 000 €' => '6000',
            '6 500 €' => '6500',
            '7 000 €' => '7000',
            '7 500 €' => '7500',
            '8 000 €' => '8000',
            '8 500 €' => '8500',
            '9 000 €' => '9000',
            '9 500 €' => '9500',
            '10 000 €' => '10000',
            '11 000 €' => '11000',
            '12 000 €' => '12000',
            '13 000 €' => '13000',
            '14 000 €' => '14000',
            '15 000 €' => '15000',
            '16 000 €' => '16000',
            '17 000 €' => '17000',
            '18 000 €' => '18000',
            '19 000 €' => '19000',
            '20 000 €' => '20000',
            '22 500 €' => '22500',
            '25 000 €' => '25000',
            '27 500 €' => '27500',
            '30 000 €' => '30000',
            '32 500 €' => '32500',
            '35 000 €' => '35000',
            '37 500 €' => '37500',
            '40 000 €' => '40000',
            '42 500 €' => '42500',
            '45 000 €' => '45000',
            '47 500 €' => '47500',
            '50 000 €' => '50000',
            'Plus de 50 000 €' => PHP_INT_MAX
        ];
    }
    /**
     * Return a list of budget
     *
     * @return array
     */
    public static function getListMin(): array
    {
        return [
            '0 €' => '0',
            '250 €' => '250',
            '500 €' => '500',
            '750 €' => '750',
            '1 000 €' => '1000',
            '1 500 €' => '1500',
            '2 000 €' => '2000',
            '2 500 €' => '2500',
            '3 000 €' => '3000',
            '3 500 €' => '3500',
            '4 000 €' => '4000',
            '4 500 €' => '4500',
            '5 000 €' => '5000',
            '5 500 €' => '5500',
            '6 000 €' => '6000',
            '6 500 €' => '6500',
            '7 000 €' => '7000',
            '7 500 €' => '7500',
            '8 000 €' => '8000',
            '8 500 €' => '8500',
            '9 000 €' => '9000',
            '9 500 €' => '9500',
            '10 000 €' => '10000',
            '11 000 €' => '11000',
            '12 000 €' => '12000',
            '13 000 €' => '13000',
            '14 000 €' => '14000',
            '15 000 €' => '15000',
            '16 000 €' => '16000',
            '17 000 €' => '17000',
            '18 000 €' => '18000',
            '19 000 €' => '19000',
            '20 000 €' => '20000',
            '22 500 €' => '22500',
            '25 000 €' => '25000',
            '27 500 €' => '27500',
            '30 000 €' => '30000',
            '32 500 €' => '32500',
            '35 000 €' => '35000',
            '37 500 €' => '37500',
            '40 000 €' => '40000',
            '42 500 €' => '42500',
            '45 000 €' => '45000',
            '47 500 €' => '47500',
            '50 000 €' => '50000'
        ];
    }
}
