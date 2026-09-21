<?php

namespace designkarma\analytics;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use designkarma\analytics\widgets\AnalyticsWidget;
use yii\base\Event;

class Plugin extends BasePlugin
{
    public function init(): void
    {
        parent::init();

        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = AnalyticsWidget::class;
            }
        );

        Craft::info(
            'Analytics plugin loaded',
            __METHOD__
        );
    }
}