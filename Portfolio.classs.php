<?php
require_once("DB.class.php");
class Portfolio extends DB{
    public $workExperience;
    public $Education;
    public $Skills;
    public $Refferees;
    public $sample;
    public $format;
    public $instructions;
    public $speakerTags;
    public $fileFormat;
    public $assignment;
    public $work;
    public $client;
    public $clientContacts;
    public $orderDate;
    public $deliveredIn;
    public $clientFeedback;
    public $jobId;
    private $db;
    private $isAdmin;

    public function addSample($sample,$format,$instructions,$speakerTags,$fileFormat){

    }
}