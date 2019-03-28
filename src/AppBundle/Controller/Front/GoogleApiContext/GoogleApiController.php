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


        /*
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_Analytics::ANALYTICS_READONLY);

        // If the user has already authorized this app then get an access token
        // else redirect to ask the user to authorize access to Google Analytics.
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            // Set the access token on the client.
            $client->setAccessToken($_SESSION['access_token']);

            // Create an authorized analytics service object.
            $analytics = new \Google_Service_AnalyticsReporting($client);

            // Call the Analytics Reporting API V4.
            $response = $this->getReport($analytics);

            return $this->render("front/Seller/pro_user_dashboard.html.twig", [
                'reports' => $this->printResults($response)
            ]);
        } else {
            return $this->redirectToRoute('google_api_oauth2callback');
        }*/

        return $this->render("front/Seller/pro_user_dashboard.html.twig", [
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
        $KEY_FILE_LOCATION  = __DIR__.'/../../../../../app/config/googleApi/client_secret.json';

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
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
        $VIEW_ID = "171901733";

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics(array($sessions));

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


    public function ouath2callbackAction()
    {
        // Create the client object and set the authorization configuration
        // from the client_secrets.json you downloaded from the Developers Console.
        $client = new \Google_Client();

        try {
            $client->setAuthConfig(__DIR__.'/../../../../../app/config/googleApi/client_secret.json');

            // $client->useApplicationDefaultCredentials();
            $client->setRedirectUri($this->generateUrl("google_api_oauth2callback"));
            $client->addScope(\Google_Service_Analytics::ANALYTICS_READONLY);
            dump($client);
            // Handle authorization flow from the server.
            if (!isset($_GET['code'])) {
                $auth_url = $client->createAuthUrl();
                return $this->redirect($auth_url);
            } else {
                $client->fetchAccessTokenWithAuthCode($_GET['code']);
                $_SESSION['access_token'] = $client->getAccessToken();
                $redirect_uri = $this->generateUrl("front_default");
                return $this->redirect($redirect_uri);
            }
        }catch(\Google_Exception $google_Exception){
            dump($google_Exception);
            return $this->redirectToRoute('front_default');
        }
    }
}