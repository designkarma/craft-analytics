<?php

namespace designkarma\analytics\services;

use Craft;
use designkarma\analytics\Plugin;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use yii\base\Component;

class Analytics extends Component
{
    private const CACHE_DURATION = 3600;

    private function getClient(): BetaAnalyticsDataClient
    {
        $settings = Plugin::getInstance()->getSettings();

        $credentialsPath = Craft::parseEnv($settings->credentialsPath);

        if (!$credentialsPath) {
            throw new \RuntimeException(
                'Google Analytics credentials path has not been configured.'
            );
        }

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException(
                "Google Analytics credentials file could not be found: {$credentialsPath}"
            );
        }

        return new BetaAnalyticsDataClient([
            'credentials' => $credentialsPath,
        ]);
    }

    private function getPropertyId(): string
    {
        $settings = Plugin::getInstance()->getSettings();

        $propertyId = Craft::parseEnv($settings->propertyId);

        if (!$propertyId) {
            throw new \RuntimeException(
                'Google Analytics Property ID has not been configured.'
            );
        }

        return $propertyId;
    }

    private function remember(string $key, callable $callback): mixed
    {
        return Craft::$app->getCache()->getOrSet(
            'craft-analytics:' . $this->getPropertyId() . ':' . $key,
            $callback,
            self::CACHE_DURATION
        );
    }

    private function calculateChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            1
        );
    }

    public function getOverview(int $days = 30): array
    {
        return $this->remember("overview-comparison:{$days}days", function() use ($days) {
            $client = $this->getClient();

            $currentStart = "{$days}daysAgo";
            $previousStart = ($days * 2) . "daysAgo";
            $previousEnd = ($days + 1) . "daysAgo";

            $response = $client->runReport(
                new RunReportRequest([
                    'property' => 'properties/' . $this->getPropertyId(),
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => $currentStart,
                            'end_date' => 'yesterday',
                            'name' => 'current',
                        ]),
                        new DateRange([
                            'start_date' => $previousStart,
                            'end_date' => $previousEnd,
                            'name' => 'previous',
                        ]),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                ])
            );

            $current = [
                'users' => 0,
                'sessions' => 0,
                'pageViews' => 0,
            ];

            $previous = [
                'users' => 0,
                'sessions' => 0,
                'pageViews' => 0,
            ];

            foreach ($response->getRows() as $row) {
                $values = $row->getMetricValues();
                $rangeName = $row->getDimensionValues()[0]->getValue();

                $data = [
                    'users' => (int) $values[0]->getValue(),
                    'sessions' => (int) $values[1]->getValue(),
                    'pageViews' => (int) $values[2]->getValue(),
                ];

                if ($rangeName === 'current') {
                    $current = $data;
                } elseif ($rangeName === 'previous') {
                    $previous = $data;
                }
            }

            return [
                'users' => $current['users'],
                'sessions' => $current['sessions'],
                'pageViews' => $current['pageViews'],
                'changes' => [
                    'users' => $this->calculateChange(
                        $current['users'],
                        $previous['users']
                    ),
                    'sessions' => $this->calculateChange(
                        $current['sessions'],
                        $previous['sessions']
                    ),
                    'pageViews' => $this->calculateChange(
                        $current['pageViews'],
                        $previous['pageViews']
                    ),
                ],
            ];
        });
    }

    public function getDailyViews(int $days = 30): array
    {
        return $this->remember("daily-views:{$days}days", function() use ($days) {
            $client = $this->getClient();

            $response = $client->runReport(
                new RunReportRequest([
                    'property' => 'properties/' . $this->getPropertyId(),
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
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
        });
    }

    public function getTopPages(int $days = 30): array
    {
        return $this->remember("top-pages:{$days}days", function() use ($days) {
            $client = $this->getClient();

            $response = $client->runReport(
                new RunReportRequest([
                    'property' => 'properties/' . $this->getPropertyId(),
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
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
        });
    }

    public function getTrafficSources(int $days = 30): array
    {
        return $this->remember("traffic-sources:{$days}days", function() use ($days) {
            $client = $this->getClient();

            $response = $client->runReport(
                new RunReportRequest([
                    'property' => 'properties/' . $this->getPropertyId(),
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'yesterday',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'sessionSource']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'sessions']),
                    ],
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy([
                                'metric_name' => 'sessions',
                            ]),
                            'desc' => true,
                        ]),
                    ],
                    'limit' => 5,
                ])
            );

            $sources = [];

            foreach ($response->getRows() as $row) {
                $sources[] = [
                    'source' => $row->getDimensionValues()[0]->getValue(),
                    'sessions' => (int) $row->getMetricValues()[0]->getValue(),
                ];
            }

            return $sources;
        });
    }
}