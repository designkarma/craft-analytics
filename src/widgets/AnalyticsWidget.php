<?php

namespace designkarma\analytics\widgets;

use Craft;
use craft\base\Widget;
use designkarma\analytics\services\Analytics;
use designkarma\analytics\web\assets\AnalyticsAsset;

class AnalyticsWidget extends Widget
{
    private const ALLOWED_DAYS = [7, 30, 90];

    public int $days = 30;

    public static function displayName(): string
    {
        return 'Google Analytics';
    }

    public static function icon(): ?string
    {
        return 'chart-line';
    }

    protected function defineRules(): array
    {
        return [
            [
                ['days'],
                'in',
                'range' => self::ALLOWED_DAYS,
            ],
        ];
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'google-analytics/widgets/settings',
            [
                'widget' => $this,
            ]
        );
    }

    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(
            AnalyticsAsset::class
        );

        $days = in_array(
            $this->days,
            self::ALLOWED_DAYS,
            true
        ) ? $this->days : 30;

        try {
            $analytics = new Analytics();
            $overview = $analytics->getOverview($days);

            return Craft::$app->getView()->renderTemplate(
                'google-analytics/widgets/analytics',
                [
                    'days' => $days,
                    'propertyId' => $analytics->getPropertyId(),
                    'users' => $overview['users'],
                    'sessions' => $overview['sessions'],
                    'pageViews' => $overview['pageViews'],
                    'changes' => $overview['changes'],
                    'dailyViews' => $analytics->getDailyViews($days),
                    'topPages' => $analytics->getTopPages($days),
                    'trafficSources' => $analytics->getTrafficSources($days),
                ]
            );
        } catch (\Throwable $e) {
            Craft::error(
                'Unable to load Google Analytics data: ' . $e->getMessage(),
                __METHOD__
            );

            return Craft::$app->getView()->renderTemplate(
                'google-analytics/widgets/error',
                [
                    'message' => Craft::$app
                        ->getConfig()
                        ->getGeneral()
                        ->devMode
                            ? $e->getMessage()
                            : null,
                ]
            );
        }
    }
}