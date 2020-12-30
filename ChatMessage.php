<?php
/**
 * @creator David
 * @project Udictate
 */

session_start();
require_once "DB.class.php";
require_once "Validator.class.php";


class ChatMessage extends DB
{
    public $messageTable;
    public $messageId;
    public $messageBody;
    public $messageThread;
    public $messageReceivedTime;
    public $messageSender;
    public $messageRecipient;
    public $replyStatus;
    public $messageParent;
    public $messageChild;
    public $dbHandler;
    public $h;
    public $validator;

    public function isSender($userId,$messageId){
        $messagesTable=$this->h->table("messages_table");
        $booleanValue=$messagesTable->select()
            ->where("messageSender",$userId)
            ->andwhere("messageId",$messageId)
            ->get();
        if(!empty($booleanValue)){
            return true;
        }
        else{
            return false;
        }
    }
    public function isRecipient($userId,$messageId){
        $messagesTable=$this->h->table("messages_table");
        $booleanValue=$messagesTable->select()
            ->where("messageRecipient",$userId)
            ->andWhere("messageId",$messageId)
            ->get();
        if(!empty($booleanValue)){
            return true;
        }
        else{
            return true;
        }
    }

    public function createMessage($messageSender,$messageRecipient,$messageBody){
        $validator= new Validator\Validator();
        if($validator->isMessage($messageBody)){
            $messagesTable=$this->h->table("messages_table");
            $messageThread=uniqid((string)date("now"));
            $threadsTable=$this->h->table("message_threads");
            $threadsTable->insert(['messageThread'=>$messageThread])->execute();
            $threadId=$this->connection->lastInsertId();
            $messagesTable->insert([
                "messageThread"=>$threadId,
                "messageSender"=>$messageSender,
                "messageRecipient"=>$messageRecipient,
                "messageBody"=>$messageBody
            ])->execute();
        }
        else{
            return $validator->messageErrors;
        }

    }

    public function replyMessage($messageRecipient,$messageBody,$messageParent){
        $validator=new Validator\Validator();
        if($validator->isMessage($messageBody)){
            if($this->isRecipient($_SESSION["userId"],$messageParent)){
                $messagesTable=$this->h->table("messages_table");
                $messages=$messagesTable->select("messageThread")
                    ->where("messageId",$messageParent)
                    ->get();
                $threadId=$messages[0]["messageThread"];
                $messagesTable->insert([
                    "messageSender"=>$_SESSION["userId"],
                    "messageRecipient"=>$messageRecipient,
                    "messageBody"=>$messageBody,
                    "messageThread"=>$threadId,
                    "messageParent"=>$messageParent,
                ])->execute();

                $messagesTable->update()
                    ->set("messageReplySatus",1)
                    ->set("messageStatus",1)
                    ->where("messageId",$messageParent)
                    ->execute();
                return json_encode(["message"=>"Message sent."]);
            }
            else{
                return json_encode(["message"=>"You cannot reply to this message."]);
            }
        }
        else{
            return json_encode(["message"=>"This is not a Valid message"]);
        }
    }
    public function viewUnseenMessages($userId){
        $messagesTable=$this->h->table("messages_table");
        $messages=$messagesTable->select()
            ->where("messageRecipient",$userId)
            ->andWhere("messageStatus",1)
            ->get();
        print_r($messages);
        if(!empty($messages)){
            if($messages[0]){
                return json_encode($messages);
            }
            else{
                return json_encode(["message"=>"You do not have any unread messages"]);
            }
        }
        else{
            return json_encode(["message"=>"message does not exist"]);
        }
    }
    public function selectAllMessages($userId){
        $messagesTable=$this->h->table("messages_table");
        $messages=$messagesTable->select()
            ->where("messageSender",$userId)
            ->orWhere("messageRecipient",$userId)
            ->get();
        if(!empty($messages)){
            return json_encode($messages);
        }
        else{
            return json_encode($messages);
        }

    }

    public function deleteMessages(...$messageIds){
        $messagesDeleted=[];
        $messagesUnauthorized=[];
        $messagesTable=$this->h->table("messages_table");
        foreach ($messageIds as $messageId){
            $counter=0;
            if($this->isRecipient($_SESSION["userId"],$messageId) || $this->isSender($_SESSION["userId"],$messageId)){
                $messagesTable->delete()
                    ->where("messageId",$messageId)
                    //->andWhere()
                    ->execute();
                array_push($messagesDeleted,["messageId"=>$messageId]);
            }
            else{
                array_push($messagesUnauthorized,["messageId"=>$messageId]);
            }

        }
        return json_encode(["messagesDeleted"=>$messagesDeleted,"messagesUnauthorized"=>$messagesUnauthorized]);
    }

    public function deleteChat($threadId){
        $messagesTable=$this->h->table("threads_table");

        $messages=$messagesTable->select()
            ->where("threadId",$threadId)
            ->get();
        foreach ($messages as $message){
            if($this->isSender($_SESSION["userId"],$message["messageId"])){
                $messagesTable->delete()
                    ->where("messageId",$message["messageId"])
                    ->execute();
            }
        }
        if(!empty($messages)){
            $threadsTable=$this->h->table("threads_table");
            $threadsTable->delete()
                ->where("threadId",$threadId)
                ->execute();
            return json_encode(["message"=>"conversation deleted succesfully"]);
        }
        else{
            return json_encode(["message"=>"Conversation delete failed"]);
        }

    }
}