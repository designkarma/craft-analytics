<?php

namespace designkarma\analytics\widgets;

use Craft;
use craft\base\Widget;
use designkarma\analytics\services\Analytics;
use designkarma\analytics\web\assets\AnalyticsAsset;

class AnalyticsWidget extends Widget
{
    public static function displayName(): string
    {
        return 'Google Analytics';
    }

    public static function icon(): ?string
    {
        return 'chart-line';
    }

    public function getBodyHtml(): ?string
	{
		Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

	    $analytics = new Analytics();

	    $overview = $analytics->getOverview();

	    return Craft::$app->getView()->renderTemplate(
	        'analytics/widgets/analytics',
	        [
	            'users' => $overview['users'],
		        'sessions' => $overview['sessions'],
		        'pageViews' => $overview['pageViews'],
		        'changes' => $overview['changes'],
		        'dailyViews' => $analytics->getDailyViews(),
		        'topPages' => $analytics->getTopPages(),
	        ]
	    );
	}
}