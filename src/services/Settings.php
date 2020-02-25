<?php


namespace leaplogic\estimatorwizard\services;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\db\Query;
use craft\helpers\StringHelper;
use craft\services\Plugins;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Settings extends Component 
{
    public function saveSettings($plugin, $settings): Model
    {
        // The existing settings
        $pluginSettings = $plugin->getSettings();

        // Have namespace?
        $settings = $settings['settings'] ?? $settings;
   

        foreach ($pluginSettings->getAttributes() as $settingHandle => $value) {
            if (isset($settings[$settingHandle])) {
                $pluginSettings->{$settingHandle} = $settings[$settingHandle] ?? $value;
            }
        }

        if (!$pluginSettings->validate()) {
            return $pluginSettings;
        }
        
        Craft::$app->getPlugins()->savePluginSettings($plugin, $pluginSettings->getAttributes());

        return $pluginSettings;
    }
}