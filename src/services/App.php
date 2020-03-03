<?php

namespace leaplogic\estimatorwizard\services;

use craft\base\Component;

class App extends Component
{

    /**
     * @var Leads
     */
    public $leads;
    public $emails;
    public $settings;

    public function init()
    {
        $this->leads = new Leads();
        $this->emails = new Emails();
    }
}
