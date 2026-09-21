<?php

namespace designkarma\analytics\widgets;

use Craft;
use craft\base\Widget;
use designkarma\analytics\services\Analytics;
use designkarma\analytics\web\assets\AnalyticsAsset;

class AnalyticsWidget extends Widget
{
    public int $days = 30;

    public static function displayName(): string
    {
        return 'Google Analytics';
    }

    public static function icon(): ?string
    {
        return 'chart-line';
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'analytics/widgets/settings',
            [
                'widget' => $this,
            ]
        );
    }

    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

        $analytics = new Analytics();

        $overview = $analytics->getOverview($this->days);

        return Craft::$app->getView()->renderTemplate(
            'analytics/widgets/analytics',
            [
                'days' => $this->days,
                'users' => $overview['users'],
                'sessions' => $overview['sessions'],
                'pageViews' => $overview['pageViews'],
                'changes' => $overview['changes'],
                'dailyViews' => $analytics->getDailyViews($this->days),
                'topPages' => $analytics->getTopPages($this->days),
                'trafficSources' => $analytics->getTrafficSources($this->days),
            ]
        );
    }
}