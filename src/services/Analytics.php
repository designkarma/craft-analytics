<?php

namespace designkarma\analytics\services;

use Craft;
use craft\helpers\App;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use yii\base\Component;

class Analytics extends Component
{
    private function getClient(): BetaAnalyticsDataClient
    {
        return new BetaAnalyticsDataClient();
    }

    private function getPropertyId(): string
    {
        return App::env('GOOGLE_ANALYTICS_PROPERTY_ID') ?? '';
    }

    public function getOverview(): array
    {
        $client = $this->getClient();

        $response = $client->runReport(
            new RunReportRequest([
                'property' => 'properties/' . $this->getPropertyId(),
                'date_ranges' => [
                    new DateRange([
                        'start_date' => '30daysAgo',
                        'end_date' => 'yesterday',
                    ]),
                ],
                'metrics' => [
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews']),
                ],
            ])
        );

        $row = $response->getRows()[0] ?? null;

        if (!$row) {
            return [
                'users' => 0,
                'sessions' => 0,
                'pageViews' => 0,
            ];
        }

        $values = $row->getMetricValues();

        return [
            'users' => (int) $values[0]->getValue(),
            'sessions' => (int) $values[1]->getValue(),
            'pageViews' => (int) $values[2]->getValue(),
        ];
    }

    public function getDailyViews(): array
    {
        $client = $this->getClient();

        $response = $client->runReport(
            new RunReportRequest([
                'property' => 'properties/' . $this->getPropertyId(),
                'date_ranges' => [
                    new DateRange([
                        'start_date' => '30daysAgo',
                        'end_date' => 'yesterday',
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'date']),
                ],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews']),
                ],
                'order_bys' => [
                    new OrderBy([
                        'dimension' => new OrderBy\DimensionOrderBy([
                            'dimension_name' => 'date',
                        ]),
                    ]),
                ],
            ])
        );

        $views = [];

        foreach ($response->getRows() as $row) {
            $views[] = (int) $row->getMetricValues()[0]->getValue();
        }

        return $views;
    }

    public function getTopPages(): array
    {
        $client = $this->getClient();

        $response = $client->runReport(
            new RunReportRequest([
                'property' => 'properties/' . $this->getPropertyId(),
                'date_ranges' => [
                    new DateRange([
                        'start_date' => '30daysAgo',
                        'end_date' => 'yesterday',
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'pageTitle']),
                ],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews']),
                ],
                'order_bys' => [
                    new OrderBy([
                        'metric' => new OrderBy\MetricOrderBy([
                            'metric_name' => 'screenPageViews',
                        ]),
                        'desc' => true,
                    ]),
                ],
                'limit' => 5,
            ])
        );

        $pages = [];

        foreach ($response->getRows() as $row) {
            $pages[] = [
                'title' => $row->getDimensionValues()[0]->getValue(),
                'views' => (int) $row->getMetricValues()[0]->getValue(),
            ];
        }

        return $pages;
    }
}