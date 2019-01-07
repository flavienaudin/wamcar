<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static Guarantee SEARCH_SORTING_DATE()
 * @method static Guarantee SEARCH_SORTING_RELEVANCE()
 * @method static Guarantee SEARCH_SORTING_DISTANCE()
 * @method static Guarantee SEARCH_SORTING_PRICE_ASC()
 * @method static Guarantee SEARCH_SORTING_PRICE_DESC()
 */
final class Sorting extends Enum
{
    const SEARCH_SORTING_DATE = 'SEARCH_SORTING_DATE';
    const SEARCH_SORTING_RELEVANCE = 'SEARCH_SORTING_RELEVANCE';
    const SEARCH_SORTING_DISTANCE = 'SEARCH_SORTING_DISTANCE';
    const SEARCH_SORTING_PRICE_ASC = 'SEARCH_SORTING_PRICE_ASC';
    const SEARCH_SORTING_PRICE_DESC = 'SEARCH_SORTING_PRICE_DESC';
}


