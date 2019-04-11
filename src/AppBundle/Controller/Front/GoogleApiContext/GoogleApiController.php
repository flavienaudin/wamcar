<?php

namespace AppBundle\Controller\Front\GoogleApiContext;


use AppBundle\Controller\Front\BaseController;

class GoogleApiController extends BaseController
{

    public function testGoogleAnalyticsAction()
    {
        $analytics = $this->initializeAnalytics();
        // Call the Analytics Reporting API V4.
        $response = $this->getReport($analytics);
        return $this->render("front/Seller/pro_user_performances.html.twig", [
            'reports' => $this->printResults($response)
        ]);
    }

    /**
     * Initializes an Analytics Reporting API V4 service object.
     *
     * @return \Google_Service_AnalyticsReporting An authorized Analytics Reporting API V4 service object.
     */
    private function initializeAnalytics()
    {
        $KEY_FILE_LOCATION = __DIR__ . '/../../../../../app/config/googleApi/client_secret.json';

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Wamcar");

//        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new \Google_Service_AnalyticsReporting($client);

        return $analytics;
    }

    /**
     * Queries the Analytics Reporting API V4.
     *
     * @param \Google_Service_AnalyticsReporting $analytics service An authorized Analytics Reporting API V4 service object.
     * @return \Google_Service_AnalyticsReporting_GetReportsResponse The Analytics Reporting API V4 response.
     */
    private function getReport(\Google_Service_AnalyticsReporting $analytics)
    {
        // Wamcar DEV Flavien
        // $VIEW_ID = "171901733";
        // Wamcar Prod filtré
        $VIEW_ID = "121219279";

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("today");

        // Create the "pageViews" Metrics object.
        $pageViews = new \Google_Service_AnalyticsReporting_Metric();
        $pageViews->setExpression("ga:pageviews");
        $pageViews->setAlias("pageViews");

        // Create the "sessions" Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");

        $dimension = new \Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName("ga:pagePath");

        // Create Dimension Filter : ga:pagePath==/conseiller-auto/maxime-chenais
        $dimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setOperator("AND");

        $dimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName("ga:pagePath");
        $dimensionFilter->setOperator("EXACT");
        $dimensionFilter->setExpressions(array("/conseiller-auto/maxime-chenais"));
        $dimensionFilterClause->setFilters([$dimensionFilter]);


        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$pageViews, $sessions]);
//        $request->setDimensions([$dimension]);
        $request->setDimensionFilterClauses($dimensionFilterClause);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        return $analytics->reports->batchGet($body);
    }


    /**
     * Parses and prints the Analytics Reporting API V4 response.
     *
     * @param \Google_Service_AnalyticsReporting_GetReportsResponse $reports An Analytics Reporting API V4 response.
     * @return array of report
     */
    private function printResults(\Google_Service_AnalyticsReporting_GetReportsResponse $reports)
    {
        $result = [];
        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $reportArray = [];

            /** @var \Google_Service_AnalyticsReporting_Report $report */
            $report = $reports[$reportIndex];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();

            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[$rowIndex];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    $reportArray[$dimensionHeaders[$i]] = $dimensions[$i];
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        $reportArray[$entry->getName()] = $values[$k];
                    }
                }
            }
            $result[$reportIndex] = $reportArray;
        }
        return $result;
    }
}