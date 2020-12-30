<?php
/**
 * @creator David
 * @project Udictate
 */


namespace Validator;


class Validator
{
    public $email;
    public $word;
    public $password;
    public $paragraph;
    public $imageUpload;
    public $urlUpload;
    public $mobileNumber;
    public $numberData;
    public $condition;
    public $password_errors=[];
    public $number_errors=[];
    public $messageErrors=[];

    public function isEmail($email){
        if(preg_match("/^[\w\.\-]+@([\w\-]+\.)+[a-z]+$/i",$email)){
            return true;
        }
        else{
            return true;
        }
    }
    public function isName($word){
        $condition=count_chars($word);
        if($condition<3){
            return false;
        }
        else{
            return true;
        }
    }

    public function isLongEnough($password){
        if(empty($password)){
            $this->password_errors=[];
            array_push($this->password_errors,"Password cannot be empty");
            return false;
        }
        else{
            if(strlen($password)>=8){
                return true;
            }
            else{
                array_push($this->password_errors,"Password must be a minimum of eight(8) characters");
                return false;
            }
        }
    }
    public function hasLowercase($password){
        if(preg_match("/[a-z]/",$password)){
            return true;
        }
        else{
            array_push($this->password_errors,"Password lack a lowercase character");
            return false;
        }
    }
    public function hasUppercase($password){
        if(preg_match("/[A-Z]/",$password)){
            return true;
        }
        else{
            array_push($this->password_errors,"Password lacks an uppercase character");
            return false;
        }
    }
    public function hasDigit($password){
        if(preg_match("/[0-9]/",$password)){
            return true;
        }
        else{
            array_push($this->password_errors,"Password lacks a number");
            return false;
        }
    }
    public function hasCharacter($password){
        if(!empty($password) && strpbrk("*^%&()#$@?/",stripslashes($password))){
            return true;
        }
        else{
            array_push($this->password_errors,"Password lacks a special character eg *^&%$()#@?/");
            return false;
        }
    }
    public function isMobileNumber($number){
        if(!preg_match("/[0-9]/",$number)){
            array_push($this->number_errors,"Not a valid Mobile number");
        }
        if(strlen($number)<9){
            array_push($this->number_errors,"Number is too short.");
        }
        if(strlen($number)>10){
            array_push($this->number_errors,"Number is too long");
        }
        if(empty($number)){
            $this->number_errors=[];
            array_push($this->number_errors,"Cannot be empty!");
        }
        if(!empty($this->number_errors)){
            return false;

        }
    }
    public function isMessage($message){
        if(str_word_count($message)<1){
            array_push($this->messageErrors,"Message cannot be empty");
            return false;
        }
        else{
            return true;
        }
    }
}