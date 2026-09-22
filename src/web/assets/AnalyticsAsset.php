<?php

namespace designkarma\analytics\web\assets;

use craft\web\AssetBundle;

class AnalyticsAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->css = [
            'css/analytics.css',
        ];

        $this->js = [
            'js/analytics.js',
        ];

        parent::init();
    }
}