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

        // If problem to access the client_secret.json file with Docker and The .env config
        // $KEY_FILE_LOCATION  = __DIR__.'/../../../../../app/config/googleApi/client_secret.json';
        // $client->setAuthConfig($KEY_FILE_LOCATION);
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

        // Common "eventLabel" Dimension object
        $eventCategoryDimension = new \Google_Service_AnalyticsReporting_Dimension();
        $eventCategoryDimension->setName(self::EVENT_CATEGORY_DIMENSION_NAME);

        //--------------------------------//
        // Page Profil du conseiller auto //
        //--------------------------------//
        // Dimension Filter Clause
        $profilePageDimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $profilePageDimensionFilterClause->setOperator(self::OPERATOR_OR);
        // Dimension Filter : ga:pagePath== ProUser profile page
        $profilePageDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $profilePageDimensionFilter->setDimensionName(self::PAGE_PATH_DIMENSION_NAME);
        $profilePageDimensionFilter->setOperator(self::OPERATOR_EXACT);
        $profilePageDimensionFilter->setExpressions(array($this->router->generate('front_view_pro_user_info', [
            'slug' => $proUser->getSlug()
        ])));
        $profilePageDimensionFilterClause->setFilters([$profilePageDimensionFilter]);
        // Create the ReportRequest object for the Profile Page
        $profilePageRequest = new \Google_Service_AnalyticsReporting_ReportRequest();
        $profilePageRequest->setViewId($this->viewId);
        $profilePageRequest->setDateRanges([$dateRange, $dateRange2]);
        $profilePageRequest->setMetrics([$uniquePageViewsMetric]);
        $profilePageRequest->setDimensions([$pagePathDimension]);
        $profilePageRequest->setDimensionFilterClauses($profilePageDimensionFilterClause);


        //------------------//
        // Nb ShowTel event //
        //------------------//
        // Dimension Filter Clause
        $contactsEventDimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        // Dimension Filter : ga:eventLabel like (launchMpPro<ProUser.id>|(Smartphone|PC)showtelpro(Fixe|Mobile<ProUser.id>)
        $contactsEventDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $contactsEventDimensionFilter->setDimensionName(self::EVENT_LABEL_DIMENSION_NAME);
        $contactsEventDimensionFilter->setOperator(self::OPERATOR_REGEXP);
        $contactsEventDimensionFilter->setExpressions('^(launchMPpro|(Smartphone|PC)showtelpro(Fixe|Mobile))' . $proUser->getId() . '$');
        $contactsEventDimensionFilterClause->setFilters([$contactsEventDimensionFilter]);

        // Create the ReportRequest object for the Profile Page
        $showTelEventRequest = new \Google_Service_AnalyticsReporting_ReportRequest();
        $showTelEventRequest->setViewId($this->viewId);
        $showTelEventRequest->setDateRanges([$dateRange, $dateRange2]);
        $showTelEventRequest->setMetrics([$uniqueEventsMetric]);
        $showTelEventRequest->setDimensions([$pagePathDimension, $eventLabelDimension]);
        $showTelEventRequest->setDimensionFilterClauses($contactsEventDimensionFilterClause);


        //------------------//
        // ProUser Véhicles //
        //------------------//
        // Dimension Filter Clause
        $vehiclePagesDimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $vehiclePagesDimensionFilterClause->setOperator(self::OPERATOR_OR);
        $vehiclesDimensionFilters = [];
        foreach ($proUser->getVehicles() as $vehicle) {
            // Vehicle Page Dimension Filter : ga:pagePath== Vehicle page
            $vehiclePageDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
            $vehiclePageDimensionFilter->setDimensionName(self::PAGE_PATH_DIMENSION_NAME);
            $vehiclePageDimensionFilter->setOperator(self::OPERATOR_EXACT);
            $vehiclePageDimensionFilter->setExpressions(array($this->router->generate('front_vehicle_pro_detail', [
                'slug' => $vehicle->getSlug()
            ])));
            $vehiclesDimensionFilters[] = $vehiclePageDimensionFilter;

            // Vehicle Page Dimension Filter : ga:pagePath== Vehicle page
            $vehicleLikeDimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
            $vehicleLikeDimensionFilter->setDimensionName(self::PAGE_PATH_DIMENSION_NAME);
            $vehicleLikeDimensionFilter->setOperator(self::OPERATOR_EXACT);
            $vehicleLikeDimensionFilter->setExpressions(array($this->router->generate('front_user_like_pro_vehicle', [
                'slug' => $vehicle->getSlug()
            ])));
            $vehiclesDimensionFilters[] = $vehicleLikeDimensionFilter;
        }
        $vehiclePagesDimensionFilterClause->setFilters([$vehiclesDimensionFilters]);

        // Create the ReportRequest object for the Profile Page
        $vehiclesPagesRequest = new \Google_Service_AnalyticsReporting_ReportRequest();
        $vehiclesPagesRequest->setViewId($this->viewId);
        $vehiclesPagesRequest->setDateRanges([$dateRange, $dateRange2]);
        $vehiclesPagesRequest->setMetrics([$uniquePageViewsMetric]);
        $vehiclesPagesRequest->setDimensions([$pagePathDimension]);
        $vehiclesPagesRequest->setDimensionFilterClauses($vehiclePagesDimensionFilterClause);


        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$profilePageRequest, $showTelEventRequest, $vehiclesPagesRequest]);

        $reports = $this->analytics->reports->batchGet($body);
        //dump($reports);
        return $this->readProUserReport($reports);
    }


    /**
     * Parses and store in an array the Analytics Reporting API V4 response.
     *
     * @param \Google_Service_AnalyticsReporting_GetReportsResponse $reports An Analytics Reporting API V4 response.
     * @return array of report
     */
    private function readProUserReport(\Google_Service_AnalyticsReporting_GetReportsResponse $reports): array
    {
        $proUserStatistics = [];
        // Profile Page Report
        $profilePageReport = $reports[0];
        $header = $profilePageReport->getColumnHeader();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $profilePageReport->getData()->getRows();
        $proUserStatistics['profilePage'] = [];
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[$rowIndex];
            $metricsByDateRange = $row->getMetrics();
            for ($j = 0; $j < count($metricsByDateRange); $j++) {
                // Each DateRange
                $values = $metricsByDateRange[$j]->getValues();
                for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    $proUserStatistics['profilePage'][$j][$entry->getName()] = $values[$k];
                }
            }
        }

        // Contacts events Report
        $contactsEventsReport = $reports[1];
        $rows = $contactsEventsReport->getData()->getRows();
        $metricsValues = [
            'telephone' => [],
            'message' => []
        ];
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            // Each dimension = $eventLabel (.*showtelpro.*<proUser.id> | launchMPpro<proUser.id>)
            $row = $rows[$rowIndex];
            $metricsByDateRange = $row->getMetrics();
            $onPage= $row->getDimensions()[0];
            $eventLabel = $row->getDimensions()[1];
            for ($j = 0; $j < count($metricsByDateRange); $j++) {
                // Each DateRange
                if(!isset($metricsValues['telephone'][$j])){
                    $metricsValues['telephone'][$j] = [
                        'total' => 0,
                        'onPage' => []
                    ];
                }
                if(!isset($metricsValues['message'][$j])) {
                    $metricsValues['message'][$j] = [
                        'total' => 0,
                        'onPage' => []
                    ];
                }
                $values = $metricsByDateRange[$j]->getValues();
                if (strpos($eventLabel, 'showtelpro') !== FALSE) {
                    $metricsValues['telephone'][$j]['total'] += intval($values[0]);
                    if(!isset($metricsValues['telephone'][$j]['onPage'][$onPage])){
                        $metricsValues['telephone'][$j]['onPage'][$onPage] = 0;
                    }
                    $metricsValues['telephone'][$j]['onPage'][$onPage] += intval($values[0]);
                } elseif (strpos($eventLabel, 'launchMPpro') !== FALSE) {
                    $metricsValues['message'][$j]['total'] += intval($values[0]);
                    if(!isset($metricsValues['message'][$j]['onPage'][$onPage])){
                        $metricsValues['message'][$j]['onPage'][$onPage] = 0;
                    }
                    $metricsValues['message'][$j]['onPage'][$onPage] += intval($values[0]);
                }
            }
        }
        $proUserStatistics['contactsEvents'] = $metricsValues;

        // Vehicles Pages Report
        $vehiclesPagesReport = $reports[2];
        $rows = $vehiclesPagesReport->getData()->getRows();
        $proUserStatistics['vehiclesPages'] = [];
        $proUserStatistics['vehiclesLikes'] = [];
        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            // Each dimension = vehicle page path
            $row = $rows[$rowIndex];
            $metricsByDateRange = $row->getMetrics();
            $vehiclePagePath = $row->getDimensions()[0];
            $vehicleMetrics = [];
            for ($j = 0; $j < count($metricsByDateRange); $j++) {
                // Each DateRange
                $values = $metricsByDateRange[$j]->getValues();
                $vehicleMetrics[$j] = $values[0];
            }
            if(strpos($vehiclePagePath,'/user/like') !== FALSE){
                $proUserStatistics['vehiclesLikes'][$vehiclePagePath] = $vehicleMetrics;
            }elseif(strpos($vehiclePagePath,'/user/like') !== FALSE) {
                $proUserStatistics['vehiclesPages'][$vehiclePagePath] = $vehicleMetrics;
            }
        }

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