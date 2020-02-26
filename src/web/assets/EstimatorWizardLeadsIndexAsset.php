<?php
namespace leaplogic\estimatorwizard\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class EstimatorWizardLeadsIndexAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@leaplogic/estimatorwizard/web/assets/';

        $this->depends = [
            CraftCpAsset::class
        ];

        $this->css = [];

        $this->js = [
            'js/leads-index.js'
        ];

        parent::init();
    }
}