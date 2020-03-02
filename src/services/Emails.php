<?php

namespace leaplogic\estimatorwizard\services;

use leaplogic\estimatorwizard\elements\LeadEstimate;

use Craft;
use craft\base\Component;
use yii\base\Exception;
use craft\mail\Mailer;
use craft\mail\Message;
use craft\helpers\StringHelper;

class Emails extends Component
{

    /**
     * @var Leads
     */
    public $settings;

    public function init()
    {
        
    }

    public function sendLeadEstimateEmail($event): bool
    {
            $settings = Craft::$app->plugins->getPlugin('estimator-wizard')->getSettings();

            $isNewLead = $event->isNew ?? false;
            if(!$isNewLead) {
                return false;
            }


            if ($event->lead == LeadEstimate::class) {
                Craft::info(Craft::t('estimator-wizard', 'The event does not match the leaplogic\esitimatorwizard\element\LeadEstimate class.'), __METHOD__);
                return false;
            }

            if (StringHelper::isBlank($event->lead->contactEmail)) {
                Craft::info(Craft::t('estimator-wizard', 'There is no contact email'), __METHOD__);
                return false;
            }

            $object = $event->lead;

            $html = $this->renderTemplate($settings->emailTemplatePath . "/email.html", [
                'settings' => $settings,
                'object' => $object,
                'email' => [
                    'subjectLine' => $object->pathLabel . " Renovation - Lovette | Design + Build"
                ]
            ]);

            $text = $this->renderTemplate($settings->emailTemplatePath . "/email.txt", [
                'settings' => $settings,
                'object' => $object,
                'email' => [
                    'subjectLine' => $object->pathLabel . " Renovation - Lovette | Design + Build"
                ]
            ]);

            $email = new Message();
            $email->setSubject($object->pathLabel . " Renovation - Lovette | Design + Build");
            $email->setFrom(["noreply@lovettedesignbuild.com" => "Lovette | Design + Build"]);
            $email->setTo($event->contactEmail);
            $email->setTextBody($text);
            $email->setHtmlBody($html);
            
            if($this->sendEmail($email)) {
                return true;
            }
            
            
        
        return false;
    }

    public function sendInternalEmail($event): bool
    {
        $isNewLead = $event->isNew ?? false;
        if(!$isNewLead) {
            return false;
        }

        if ($event->lead == LeadEstimate::class) {
            Craft::info(Craft::t('estimator-wizard', 'The event does not match the leaplogic\esitimatorwizard\element\LeadEstimate class.'), __METHOD__);
            return false;
        }

        $settings = $this->settings;
        $toEmails = is_string($settings->emailTo) ? StringHelper::split($settings->emailTo) : $settings->emailTo;

        $object = $event->lead;
        $html = $this->renderTemplate($this->settings->emailInternalTemplatePath . "/email.html", [
            'settings' => $this->settings,
            'object' => $object,
            'email' => [
                'subjectLine' => $object->pathLabel . " Renovation - Lovette | Design + Build"
            ]
        ]);

        $email = new Message();
        $email->setSubject("New Lead Estimate | ". $object->contactEmail );
        $email->setFrom(["noreply@lovettedesignbuild.com" => "Lovette | Design + Build"]);
        $email->setHtmlBody($html);

        $mailer = new Mailer();

        foreach ($toEmails as $toEmail) {
            $email->setTo($toEmail);
            $mailer->send($email);
        }

    }



    private function sendEmail( $message ): bool
    {

        $mailer = new Mailer();

        if($mailer->send($message)) {
            return true;
        }

        Craft::info(Craft::t('estimator-wizard', 'Failed to send email.'), __METHOD__);
        throw new Exception('Failed to send email');

        return false;
    }
}
