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

    private ?BetaAnalyticsDataClient $client = null;

    private function getClient(): BetaAnalyticsDataClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

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

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentialsPath,
        ]);

        return $this->client;
    }

    public function getPropertyId(): string
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

    public function getConfigurationStatus(): array
    {
        $settings = Plugin::getInstance()->getSettings();

        $propertyId = Craft::parseEnv($settings->propertyId);
        $credentialsPath = Craft::parseEnv($settings->credentialsPath);

        $propertyConfigured = !empty($propertyId);
        $credentialsConfigured = !empty($credentialsPath);
        $credentialsExist = $credentialsConfigured
            && file_exists($credentialsPath);

        return [
            'configured' => $propertyConfigured && $credentialsExist,
            'propertyConfigured' => $propertyConfigured,
            'credentialsConfigured' => $credentialsConfigured,
            'credentialsExist' => $credentialsExist,
        ];
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

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getDateRanges(int $days): array
    {
        return [
            'currentStart' => "{$days}daysAgo",
            'currentEnd' => 'yesterday',
            'previousStart' => ($days * 2) . 'daysAgo',
            'previousEnd' => ($days + 1) . 'daysAgo',
        ];
    }

    public function getOverview(int $days = 30): array
    {
        return $this->remember(
            "overview-comparison:{$days}days",
            function() use ($days) {
                $client = $this->getClient();
                $ranges = $this->getDateRanges($days);

                $response = $client->runReport(
                    new RunReportRequest([
                        'property' => 'properties/' . $this->getPropertyId(),
                        'date_ranges' => [
                            new DateRange([
                                'start_date' => $ranges['currentStart'],
                                'end_date' => $ranges['currentEnd'],
                                'name' => 'current',
                            ]),
                            new DateRange([
                                'start_date' => $ranges['previousStart'],
                                'end_date' => $ranges['previousEnd'],
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
                    $rangeName = $row
                        ->getDimensionValues()[0]
                        ->getValue();

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
            }
        );
    }

    public function getDailyViews(int $days = 30): array
    {
        return $this->remember(
            "daily-views:{$days}days",
            function() use ($days) {
                $client = $this->getClient();
                $propertyId = $this->getPropertyId();
                $ranges = $this->getDateRanges($days);

                $request = (new RunReportRequest())
                    ->setProperty("properties/{$propertyId}")
                    ->setDateRanges([
                        new DateRange([
                            'start_date' => $ranges['currentStart'],
                            'end_date' => $ranges['currentEnd'],
                        ]),
                    ])
                    ->setDimensions([
                        new Dimension([
                            'name' => 'date',
                        ]),
                    ])
                    ->setMetrics([
                        new Metric([
                            'name' => 'screenPageViews',
                        ]),
                    ]);

                $response = $client->runReport($request);

                $viewsByDate = [];

                foreach ($response->getRows() as $row) {
                    $date = $row
                        ->getDimensionValues()[0]
                        ->getValue();

                    $views = (int)$row
                        ->getMetricValues()[0]
                        ->getValue();

                    $viewsByDate[$date] = $views;
                }

                $timezone = new \DateTimeZone(
                    Craft::$app->getTimeZone()
                );

                $end = new \DateTimeImmutable(
                    'yesterday',
                    $timezone
                );

                $start = $end->modify(
                    '-' . ($days - 1) . ' days'
                );

                $dailyViews = [];

                for (
                    $date = $start;
                    $date <= $end;
                    $date = $date->modify('+1 day')
                ) {
                    $key = $date->format('Ymd');

                    $dailyViews[] = [
                        'date' => $date->format('Y-m-d'),
                        'label' => $date->format('j M'),
                        'views' => $viewsByDate[$key] ?? 0,
                    ];
                }

                return $dailyViews;
            }
        );
    }

    public function getTopPages(int $days = 30): array
    {
        return $this->remember(
            "top-pages:{$days}days",
            function() use ($days) {
                $client = $this->getClient();
                $propertyId = $this->getPropertyId();
                $ranges = $this->getDateRanges($days);

                $request = (new RunReportRequest())
                    ->setProperty("properties/{$propertyId}")
                    ->setDateRanges([
                        new DateRange([
                            'start_date' => $ranges['currentStart'],
                            'end_date' => $ranges['currentEnd'],
                        ]),
                    ])
                    ->setDimensions([
                        new Dimension([
                            'name' => 'pageTitle',
                        ]),
                        new Dimension([
                            'name' => 'pagePath',
                        ]),
                    ])
                    ->setMetrics([
                        new Metric([
                            'name' => 'screenPageViews',
                        ]),
                    ])
                    ->setOrderBys([
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy([
                                'metric_name' => 'screenPageViews',
                            ]),
                            'desc' => true,
                        ]),
                    ])
                    ->setLimit(5);

                $response = $client->runReport($request);

                $pages = [];

                foreach ($response->getRows() as $row) {
                    $pages[] = [
                        'title' => $row
                            ->getDimensionValues()[0]
                            ->getValue(),
                        'path' => $row
                            ->getDimensionValues()[1]
                            ->getValue(),
                        'views' => (int)$row
                            ->getMetricValues()[0]
                            ->getValue(),
                    ];
                }

                return $pages;
            }
        );
    }

    public function getTrafficSources(int $days = 30): array
    {
        return $this->remember(
            "traffic-sources:{$days}days",
            function() use ($days) {
                $client = $this->getClient();
                $ranges = $this->getDateRanges($days);

                $response = $client->runReport(
                    new RunReportRequest([
                        'property' => 'properties/' . $this->getPropertyId(),
                        'date_ranges' => [
                            new DateRange([
                                'start_date' => $ranges['currentStart'],
                                'end_date' => $ranges['currentEnd'],
                            ]),
                        ],
                        'dimensions' => [
                            new Dimension([
                                'name' => 'sessionSource',
                            ]),
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
                        'source' => $row
                            ->getDimensionValues()[0]
                            ->getValue(),
                        'sessions' => (int) $row
                            ->getMetricValues()[0]
                            ->getValue(),
                    ];
                }

                return $sources;
            }
        );
    }
}