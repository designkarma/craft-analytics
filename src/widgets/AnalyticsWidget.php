<?php

namespace designkarma\analytics\widgets;

use craft\base\Widget;

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
        return '<p>Hello from Google Analytics 👋</p>';
    }
}