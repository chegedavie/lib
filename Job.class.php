<?php
require_once("DB.class.php");
//require_once("connection.php");

/**
 * Class Job
 */
class Job extends DB{
    private $jobId;
    private $job;
    private $dateReceived;
    private $dateDelivered;
    private $clientId;
    private $clientInstructions;
    private $starRating;
    private $clientFeedback;
    private $makePortfolio;
    private $table;
    private $db;
    private $jobFormat;
    private $jobDesign;
    private $jobPrice;
    private $row;
    private $userId;

    private function is_delivered($jobId){
        $db =$this->h;
        $table=$db->table('jobs');
        $row=$table->select()
            ->where('id',$jobId)
            ->get();
        return $row[0]['Status'];
    }

    public function isOwnedByUser($jobId,$userId){
        //hhhh

    }

    /**
     * @param $clientId
     * @param $job
     * @param $jobFormat
     * @param $clientInstructions
     * @param $jobPrice
     */
    public function createJob($clientId,$job,$jobFormat,$clientInstructions,$jobPrice){
        $db=$this->h;
        $table=$db->table('jobs');
        $table->insert(
            ['job'=>$job,'userId'=>$clientId,'clientInstructions'=>$clientInstructions,'jobFormat'=>$jobFormat,'jobPrice'=>$jobPrice
            ])->execute();
    }

    /**
     * @param $jobId
     * @param $clientInstructions
     */
    public function updateClientInstructions($jobId,$clientInstructions){
        $db=$this->h;
        $table=$db->table('jobs');
        $table->update()
            ->set('clientInstructions', $clientInstructions)
            ->where('id',$jobId)
            ->execute();
    }

    /**
     * @param $jobId
     * @param $starRating
     * @param $clientFeedback
     * @param $makePortfolio
     * @return string
     */
    public function createUserFeedback($jobId,$starRating,$clientFeedback,$makePortfolio){
        if($this->is_delivered($jobId)){
            $db=$this->h;
            $table=$db->table('jobs');
            $table->update()
                ->set('starRating',$starRating)
                ->set('clientFeedback',$clientFeedback)
                ->set('makePortfolio',$makePortfolio)
                ->where('id',$jobId)
                ->execute();
        }
        else{
            return "This job is not activated";
        }
    }

    /**
     * @param $jobId
     *
     * This function delevers the complete file and the feedback request
     * Actually the mail should include a form to the user to be filled and submitted directly to our site.
     * Only an admin is allowed use of this function.
     * finally configure a cron to delete all files that have existed for more than two months after delivery
     */
    public function deliverJob($jobId){
        $db=$this->h;
        $table=$db->table('jobs');
        $table->update()
            ->set('status',1)
            ->execute();
        /**
         * Here send the job and the feedback url to the client via email.
         * use mimemail as you need a fancy email. ie not plain text
         */
    }
    public function getJob($jobId){
        $db=$this->h;
        $table=$db->table('jobs');
        return json_encode($table->select()
            ->where('id',$jobId)
            ->get());
    }

    public function orderRework($jobId){
        if($this->isOwnedByUser($jobId,$_SESSION["userId"])){
            $jobsTable=$this->h->table("jobs");
            $reworksTable=$this->h->table("reworks");
            $reworksTable->insert([
                "jobId"=>$jobId
            ])->execute();
            $jobsTable->upadate()
                ->set("jobStatus","rework")
                ->execute();
            return json_encode(["message"=>"Rework order done succesfully"]);
        }
        else{
            return json_encode(["message"=>"Rework order on Job that does not belong to the user"]);
        }

    }


}