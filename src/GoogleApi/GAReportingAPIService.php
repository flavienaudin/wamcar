<?php

namespace GoogleApi;


use Symfony\Component\Routing\RouterInterface;
use Wamcar\User\ProUser;

class GAReportingAPIService
{

    const UNIQUE_PAGE_VIEWS_METRIC_NAME = "ga:uniquePageviews";
    const UNIQUE_PAGE_VIEWS_METRIC_ALIAS = "uniquePageViews";

    const UNIQUE_EVENTS_METRIC_NAME = "ga:uniqueEvents";
    const UNIQUE_EVENTS_METRIC_ALIAS = "uniqueEvents";

    const PAGE_PATH_DIMENSION_NAME = "ga:pagePath";
    const EVENT_LABEL_DIMENSION_NAME = "ga:eventLabel";
    const EVENT_CATEGORY_DIMENSION_NAME = "ga:eventCategory";

    // Dimension Filter Logical Operators (https://developers.google.com/analytics/devguides/reporting/core/v4/rest/v4/reports/batchGet#filterlogicaloperator)
    const OPERATOR_OR = "OR";
    const OPERATOR_AND = "AND";

    // Dimension Filter expression Operator (https://developers.google.com/analytics/devguides/reporting/core/v4/rest/v4/reports/batchGet#operator)
    const OPERATOR_REGEXP = "REGEXP";
    const OPERATOR_BEGINS_WITH = "BEGINS_WITH";
    const OPERATOR_ENDS_WITH = "ENDS_WITH";
    const OPERATOR_PARTIAL = "PARTIAL";
    const OPERATOR_EXACT = "EXACT";
    const OPERATOR_NUMERIC_EQUAL = "NUMERIC_EQUAL";
    const OPERATOR_NUMERIC_GREATER_THAN = "NUMERIC_GREATER_THAN";
    const OPERATOR_NUMERIC_LESS_THAN = "NUMERIC_LESS_THAN";
    const OPERATOR_IN_LIST = "IN_LIST";


    /** @var RouterInterface */
    private $router;
    /** @var string */
    private $applicationName;
    /** @var string */
    private $viewId;
    /** @var \Google_Service_AnalyticsReporting */
    private $analytics;

    /**
     * GAReportingAPIService constructor.
     * @param RouterInterface $router
     * @param string $applicationName
     * @param string $viewId
     */

    public function __construct(RouterInterface $router, string $applicationName, string $viewId)
    {
        $this->router = $router;
        $this->applicationName = $applicationName;
        $this->viewId = $viewId;

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName($this->applicationName);
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->analytics = new \Google_Service_AnalyticsReporting($client);

    }

    /**
     * Retrieve GoogleAnalytics KPI by ProUser
     * @param ProUser $proUser
     * @return array
     */
    public function getProUserKPI(ProUser $proUser): array
    {
        // Common DateRange object
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("today");

        $dateRange2 = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange2->setStartDate("7daysAgo");
        $dateRange2->setEndDate("today");

        // Common "uniquePageViews" Metrics object
        $uniquePageViewsMetric = new \Google_Service_AnalyticsReporting_Metric();
        $uniquePageViewsMetric->setExpression(self::UNIQUE_PAGE_VIEWS_METRIC_NAME);
        $uniquePageViewsMetric->setAlias(self::UNIQUE_PAGE_VIEWS_METRIC_ALIAS);

        // Common "uniqueEvents" Metrics object
        $uniqueEventsMetric = new \Google_Service_AnalyticsReporting_Metric();
        $uniqueEventsMetric->setExpression(self::UNIQUE_EVENTS_METRIC_NAME);
        $uniqueEventsMetric->setAlias(self::UNIQUE_EVENTS_METRIC_ALIAS);

        // Common "pagePath" Dimension object
        $pagePathDimension = new \Google_Service_AnalyticsReporting_Dimension();
        $pagePathDimension->setName(self::PAGE_PATH_DIMENSION_NAME);

        // Common "eventLabel" Dimension object
        $eventLabelDimension = new \Google_Service_AnalyticsReporting_Dimension();
        $eventLabelDimension->setName(self::EVENT_LABEL_DIMENSION_NAME);

        // Common "eventCategory" Dimension object
        $eventCategoryDimension = new \Google_Service_AnalyticsReporting_Dimension();
        $eventCategoryDimension->setName(self::EVENT_CATEGORY_DIMENSION_NAME);

        //--------------------------------//
        // Page Profil du conseiller auto //
        // ProUser Véhicles               //
        //--------------------------------//
        // Dimension Filter Clause
        $profileAndVehiclesPagesDimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $profileAndVehiclesPagesDimensionFilterClause->setOperator(self::OPERATOR_OR);
        $profileAndVehiclesPagesDimensionFilters = [];
        // Dimension Filter : ga:pagePath== ProUser profile page
        $profilePageDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $profilePageDimensionFilter->setDimensionName(self::PAGE_PATH_DIMENSION_NAME);
        $profilePageDimensionFilter->setOperator(self::OPERATOR_EXACT);
        $profilePageDimensionFilter->setExpressions(array($this->router->generate('front_view_pro_user_info', [
            'slug' => $proUser->getSlug()
        ])));
        $profileAndVehiclesPagesDimensionFilters[] = $profilePageDimensionFilter;

        /* Plus d'affectation des véhicules aux vendeurs
        foreach ($proUser->getVehicles() as $vehicle) {
            // Vehicle Page Dimension Filter : ga:pagePath== Vehicle page
            $vehiclePageDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
            $vehiclePageDimensionFilter->setDimensionName(self::PAGE_PATH_DIMENSION_NAME);
            $vehiclePageDimensionFilter->setOperator(self::OPERATOR_EXACT);
            $vehiclePageDimensionFilter->setExpressions(array($this->router->generate('front_vehicle_pro_detail', [
                'slug' => $vehicle->getSlug()
            ])));
            $profileAndVehiclesPagesDimensionFilters[] = $vehiclePageDimensionFilter;
        }*/

        $profileAndVehiclesPagesDimensionFilterClause->setFilters($profileAndVehiclesPagesDimensionFilters);

        // Create the ReportRequest object for the Profile Page
        $profileAndVehiclesPageRequest = new \Google_Service_AnalyticsReporting_ReportRequest();
        $profileAndVehiclesPageRequest->setViewId($this->viewId);
        $profileAndVehiclesPageRequest->setDateRanges([$dateRange, $dateRange2]);
        $profileAndVehiclesPageRequest->setMetrics([$uniquePageViewsMetric]);
        $profileAndVehiclesPageRequest->setDimensions([$pagePathDimension]);
        $profileAndVehiclesPageRequest->setDimensionFilterClauses($profileAndVehiclesPagesDimensionFilterClause);

        //------------------//
        // Nb ShowTel event //
        //------------------//
        // Dimension Filter Clause
        $contactsEventDimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $contactsEventDimensionFilterClause->setOperator(self::OPERATOR_OR);

        // Dimension Filter : Contacts to the pro : ga:eventLabel like (LM .* to Advisor<ProUser.id>|SP (1|2) .* to Advisor<ProUser.id>)
        $contactsToAdvisorEventDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $contactsToAdvisorEventDimensionFilter->setDimensionName(self::EVENT_LABEL_DIMENSION_NAME);
        $contactsToAdvisorEventDimensionFilter->setOperator(self::OPERATOR_REGEXP);
        $contactsToAdvisorEventDimensionFilter->setExpressions('^(LM|SP (1|2)).*to Advisor' . $proUser->getId() . '$');

        // Dimension Filter : ShowTel from the pro : ga:eventLabel like (SP (1|2) from Advisor<ProUser.id> to (Customer|Advisor).*)
        $showtelFromAdvisorEventDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $showtelFromAdvisorEventDimensionFilter->setDimensionName(self::EVENT_LABEL_DIMENSION_NAME);
        $showtelFromAdvisorEventDimensionFilter->setOperator(self::OPERATOR_REGEXP);
        $showtelFromAdvisorEventDimensionFilter->setExpressions('^SP (1|2) from Advisor' . $proUser->getId() . ' to (Customer|Advisor)');

        $contactsEventDimensionFilterClause->setFilters([$contactsToAdvisorEventDimensionFilter, $showtelFromAdvisorEventDimensionFilter ]);

        // Create the ReportRequest object for the Profile Page
        $showTelEventRequest = new \Google_Service_AnalyticsReporting_ReportRequest();
        $showTelEventRequest->setViewId($this->viewId);
        $showTelEventRequest->setDateRanges([$dateRange, $dateRange2]);
        $showTelEventRequest->setMetrics([$uniqueEventsMetric]);
        $showTelEventRequest->setDimensions([$pagePathDimension, $eventLabelDimension]);
        $showTelEventRequest->setDimensionFilterClauses($contactsEventDimensionFilterClause);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$profileAndVehiclesPageRequest, $showTelEventRequest]);

        $reports = $this->analytics->reports->batchGet($body);
        //dump($reports);
        return $this->readProUserReport($reports, $proUser);
    }


    /**
     * Parses and store in an array the Analytics Reporting API V4 response.
     *
     * @param \Google_Service_AnalyticsReporting_GetReportsResponse $reports An Analytics Reporting API V4 response.
     * @param ProUser $proUser The user concerned by the report
     * @return array of report
     */
    private function readProUserReport(\Google_Service_AnalyticsReporting_GetReportsResponse $reports, ProUser $proUser): array
    {
        $proUserStatistics = ['profilePage' => [],'vehiclesPages' => []];
        // Profile Page Report
        $profilePageReport = $reports[0];
        $header = $profilePageReport->getColumnHeader();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $profilePageReport->getData()->getRows();
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[$rowIndex];
            $urlPage = $row->getDimensions()[0];
            $metricsByDateRange = $row->getMetrics();
            for ($j = 0; $j < count($metricsByDateRange); $j++) {
                // Each DateRange
                $values = $metricsByDateRange[$j]->getValues();
                for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    if(strpos($urlPage, $this->router->generate('front_view_pro_user_info', ['slug' => $proUser->getSlug() ])) !== FALSE) {
                        $proUserStatistics['profilePage'][$j][$entry->getName()] = $values[$k];
                    }elseif(strpos($urlPage, str_replace('FAKE', '', $this->router->generate('front_vehicle_pro_detail', ['slug' => 'FAKE' ]))) !== FALSE) {
                        $proUserStatistics['vehiclesPages'][$urlPage][$j][$entry->getName()] = $values[$k];
                    }
                }
            }
        }
        $orderedArray = $proUserStatistics['vehiclesPages'];
        uasort($orderedArray, function ($dateRangeMetrics1, $dateRangeMetrics2) {
            return $dateRangeMetrics1[0]['uniquePageViews'] - $dateRangeMetrics2[0]['uniquePageViews'];
        });
        $proUserStatistics['top5Vehicles'] = array_reverse(array_slice($orderedArray, -5), true);

        $contactsEventsReport = $reports[1];
        $rows = $contactsEventsReport->getData()->getRows();
        $metricsValues = [
            'telephone' => [],
            'message' => [],
            'telViews' => []
        ];
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            // Each dimension = $eventLabel (.*showtelpro.*<proUser.id> | launchMPpro<proUser.id>)
            // Each dimension = $eventLabel (SP (1|2).*to Advisor<proUser.id> | LM.*to Advisor<proUser.id>)

            $row = $rows[$rowIndex];
            $metricsByDateRange = $row->getMetrics();
            $onPage = $row->getDimensions()[0];
            $eventLabel = $row->getDimensions()[1];
            for ($j = 0; $j < count($metricsByDateRange); $j++) {
                // Each DateRange
                if (!isset($metricsValues['telephone'][$j])) {
                    $metricsValues['telephone'][$j] = [
                        'total' => 0,
                        'onPage' => []
                    ];
                }
                if (!isset($metricsValues['message'][$j])) {
                    $metricsValues['message'][$j] = [
                        'total' => 0,
                        'onPage' => []
                    ];
                }
                // Each DateRange
                if (!isset($metricsValues['telViews'][$j])) {
                    $metricsValues['telViews'][$j] = [
                        'total' => 0,
                        'onPage' => []
                    ];
                }
                $values = $metricsByDateRange[$j]->getValues();
                if (preg_match('/^SP (1|2) from .* to Advisor'.$proUser->getId().'$/', $eventLabel) === 1) {
                    $metricsValues['telephone'][$j]['total'] += intval($values[0]);
                    if (!isset($metricsValues['telephone'][$j]['onPage'][$onPage])) {
                        $metricsValues['telephone'][$j]['onPage'][$onPage] = 0;
                    }
                    $metricsValues['telephone'][$j]['onPage'][$onPage] += intval($values[0]);
                }
                elseif (preg_match('/^SP (1|2) from Advisor'.$proUser->getId().' to (Customer|Advisor).*$/', $eventLabel) === 1) {
                    $metricsValues['telViews'][$j]['total'] += intval($values[0]);
                    if (!isset($metricsValues['telViews'][$j]['onPage'][$onPage])) {
                        $metricsValues['telViews'][$j]['onPage'][$onPage] = 0;
                    }
                    $metricsValues['telViews'][$j]['onPage'][$onPage] += intval($values[0]);
                }
                elseif (preg_match('/LM from .* to Advisor'.$proUser->getId().'$/', $eventLabel ) === 1) {
                    $metricsValues['message'][$j]['total'] += intval($values[0]);
                    if (!isset($metricsValues['message'][$j]['onPage'][$onPage])) {
                        $metricsValues['message'][$j]['onPage'][$onPage] = 0;
                    }
                    $metricsValues['message'][$j]['onPage'][$onPage] += intval($values[0]);
                }
            }
        }
        $proUserStatistics['contactsEvents'] = $metricsValues;
        return $proUserStatistics;
    }


    /**
     * Parses and store in an array the Analytics Reporting API V4 response.
     *
     * @param \Google_Service_AnalyticsReporting_GetReportsResponse $reports An Analytics Reporting API V4 response.
     * @return array of report
     */
    private function printResults(\Google_Service_AnalyticsReporting_GetReportsResponse $reports)
    {
        $result = [];
        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $result[$reportIndex] = $this->readReportToArray($reports[$reportIndex]);
        }
        return $result;
    }

    /**
     * @param \Google_Service_AnalyticsReporting_Report $report
     * @return array
     */
    private function readReportToArray(\Google_Service_AnalyticsReporting_Report $report): array
    {
        $reportArray = [];
        $header = $report->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $reportArray['dimensionsNames'] = join(',', $dimensionHeaders);
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();

        $reportArray['rows'] = [];
        $rows = $report->getData()->getRows();
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $rowData = [];
            $row = $rows[$rowIndex];

            $rowData['metricsValues'] = [];

            $metrics = $row->getMetrics();
            for ($j = 0; $j < count($metrics); $j++) {
                // Each DateRange
                $values = $metrics[$j]->getValues();
                for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    $rowData['metricsValues'][$j][$entry->getName()] = $values[$k];
                }
            }
            $reportArray['rows'][join(',', $row->getDimensions())] = $rowData;
        }

        return $reportArray;
    }
}
