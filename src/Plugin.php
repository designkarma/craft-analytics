<?php

namespace designkarma\analytics;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use designkarma\analytics\models\Settings;
use designkarma\analytics\services\Analytics;
use designkarma\analytics\widgets\AnalyticsWidget;
use yii\base\Event;

class Plugin extends BasePlugin
{
    public bool $hasCpSettings = true;

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

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        $analytics = new Analytics();

        return Craft::$app->getView()->renderTemplate(
            'google-analytics/settings',
            [
                'settings' => $this->getSettings(),
                'configuration' => $analytics->getConfigurationStatus(),
            ]
        );
    }
}