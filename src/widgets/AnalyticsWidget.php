<?php

namespace designkarma\analytics\widgets;

use Craft;
use craft\base\Widget;
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

	    return Craft::$app->getView()->renderTemplate(
	        'analytics/widgets/analytics',
	        [
	            'users' => 1248,
	            'sessions' => 1683,
	            'pageViews' => 2941,
	            'topPages' => [
	                [
	                    'title' => 'Homepage',
	                    'views' => 842,
	                ],
	                [
	                    'title' => 'Craft CMS Development',
	                    'views' => 463,
	                ],
	                [
	                    'title' => 'Craft CMS vs WordPress',
	                    'views' => 327,
	                ],
	                [
	                    'title' => 'Contact',
	                    'views' => 184,
	                ],
	            ],
	            'dailyViews' => [
				    82, 91, 88, 105, 112, 98, 121, 136, 129, 143,
				    151, 147, 162, 155, 171, 184, 176, 193, 201, 188,
				    214, 207, 221, 235, 228, 246, 239, 258, 271, 263,
				],
	        ]
	    );
	}
}