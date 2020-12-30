<?php
session_start();

/**
 * @creator David
 * @project Udictate
 */

require_once "DB.class.php";
require_once 'Job.class.php';
require_once 'Validator.class.php';

class User extends DB {
    private $username;
    private $password;
    private $email;
    private $firstName;
    private $secondName;
    private $gender;
    private $dateOfBirth;
    private $streetAddress;
    private $city;
    private $country;
    private $db;
    private $userTable;
    private $addressTable;
    private $cityTable;
    private $countryTable;
    private $countryId;
    private $cityId;
    private $row1;
    private $userRow;

    /**
     * @param $city
     * @return bool
     */
    public function ifCityExists($city){
        $db=$this->h;
        $cityTable=$db->table("cities");
        $row=$cityTable->select()
            ->where('city',$city)
            ->get();
        if(!empty($row)){
            if($row[0]["city"]==true){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * @param $country
     * @return bool
     */
    public function ifCountryExists($country){
        $db=$this->h;
        $countryTable=$db->table('countries');
        $row1=$countryTable->select()
            ->where("country",$country)
            ->get();
        if(!empty($row1)){
            if(!empty($row1[0]["country"])){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * @param $username
     * @param $email
     * @return bool
     */
    public function isUserAvailable($username,$email){
        $usersTable=$this->h->table('users');
        $usersRow=$usersTable->select()
            ->where('username',$username)
            ->where("emailAddress",$email)
            ->get();
        if(!empty($usersRow)){
            if(!empty($usersRow[0]['username'])){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     * @param $firstName
     * @param $secondName
     * @param $gender
     * @param $dateOfBirth
     * @param $streetAddress
     * @param $city
     * @param $country
     * @return string
     */
    public function registerUser($username,$email,$password,$firstName,$secondName,$gender,$dateOfBirth,$streetAddress,$city,$country){
        $db=$this->h;
        //insert the city to cities table
        $cityTable=$db->table('cities');
        if($this->ifCityExists($city)){
            $row=$cityTable->select()
                ->where('city',$city)
                ->get();
            $cityId=$row[0]['id'];
        }
        else{
            $cityTable=$db->table('cities');
            $cityTable->insert(['city'=>$city])->execute();
            $cityId=$this->connection->lastInsertId();
        }
        $countryTable=$db->table("countries");
        if($this->ifCountryExists($country)){
            $row1=$countryTable->select()
                ->where('country',$country)
                ->get();
            $countryId=$row1[0]['id'];
        }
        else{
            $countryTable->insert(['country'=>$country])->execute();
            $countryId=$this->connection->lastInsertId();
        }
        $validator=new Validator\Validator();

        $validator->hasCharacter($password);
        $validator->hasDigit($password);
        $validator->hasUppercase($password);
        $validator->hasLowercase($password);
        $validator->isLongEnough($password);
        if(empty($validator->password_errors)){
            $userTable=$db->table('users');
            $dateOfBirth=date("Y:m:d",strtotime($dateOfBirth));
            if($this->isUserAvailable($username,$email)){
                return "user already exists";
            }
            else{
                $userTable->insert(
                    ['username'=>$username,"emailAddress"=>$email,'password'=>md5($password."ambrashc"),'firstName'=>$firstName,'surname'=>$secondName,'gender'=>$gender,'dateOfBirth'=>$dateOfBirth,'city'=>$cityId,'country'=>$countryId]
                )
                    ->execute();
                return "user registered succesfully";
            }
        }
        else{
            return $validator->password_errors;
        }
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function fetchUserProfile($userId){
        $usersTable=$this->h->table("users");
        return $usersTable->select()
            ->where("userId",$userId)
            ->join('cities','cities.id','=','users.city','inner')
            ->join('countries', 'countries.id','=','users.country','inner')
            ->get();
    }
    public function createUserThumbnail($userId,$thumbnail){
        if(is_uploaded_file($thumbnail)){
            move_uploaded_file($thumbnail,$thumbnail);
            $uploadFinished=true;
        }
        else{
            $uploadFinished=false;
        }
        //Upload a file here
        if($uploadFinished){
            $usersTable=$this->h->table('users');
            $usersTable->insert(['thumbnail',$thumbnail])
                ->execute();
        }
    }
    public function isPasswordExists($password){
        $usersTable=$this->h->table("users");
        $usersRow=$usersTable->select()
            ->where("passowrd",$password)
            ->get();
        if(!empty($usersRow[0]["password"])){
            return true;
        }
        else{
            return false;
        }
    }
    public function isUsernameExists($username){
        $usersTable=$this->h->table("users");
        $usersRow=$usersTable->select()
            ->where("username",$username)
            ->get();
        if(!empty($usersRow[0]["username"])){
            return true;
        }
        else{
            return false;
        }
    }
    public function isEmailExists($email){
        $usersTable=$this->h->table("users");
        $usersRow=$usersTable->select()
            ->where("emailAddress",$email)
            ->get();
        if(!empty($usersRow[0]["emailAddress"])){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @param $identifier
     * @param $password
     * @return string
     */
    public function signinUser($identifier, $password){
        $password=stripslashes($password);
        $usersTable=$this->h->table("users");
        $usersRow=$usersTable->select()
            ->where("emailAddress",$identifier)
            ->orWhere('username',$identifier)
            ->where('password',md5($password."ambrashc"))
            ->get();
        if(!empty($usersRow[0]["username"]) && ($usersRow[0]['password'])==md5($password."ambrashc")){
            $userRow=$usersRow[0];
            session_cache_expire(1209600);
            $_SESSION['username']= $userRow["username"];
            $_SESSION["userId"]=$userRow['userId'];

            return "authentication succesfull";
        }

        else{
            return "authentication failed";
        }
    }

    /**
     * @param $email
     * @return string
     */
    public function userGetToken($email){
        if($this->isEmailExists($email)){
            $usersTable=$this->h->table("users");
            $usersRow=$usersTable->select()
                ->where("emailAddress",$email)
                ->get();
            $userId=$usersRow[0]["userId"];
            $hashId=md5($email.(string)(strtotime("now")));

            $tokenTable=$this->h->table("passwordResets");
            $tokenTable->insert([
                "userId"=>$userId,
                "hashToken"=>$hashId,
                "hashExpiry"=>date("Y:m:d",strtotime("14 days")),
            ])->execute();
        }
        return $hashId;
    }

    /**
     * @param $token
     * @param $password
     * @return bool
     */
    public function updatePassword($token,$password){
        $tokenTable=$this->h->table("passwordResets");
        $hashRow=$tokenTable->select()
            ->where("hashToken",$token)
            ->get();
        $tokenExpiry=strtotime($hashRow[0]["hashExpiry"]);
        if($tokenExpiry<(string)strtotime("now")){
            return false;
        }
        else{
            $usersTable=$this->h->table("users");
            $usersTable->update()
                ->set("password",md5($password."ambrashc"))
                ->where("userId",$hashRow[0]["userId"])
                ->execute();
            $tokenTable->delete()
                ->where("hashId",$token);

            return true;
        }
    }

    public function userLogout(){
        if(isset($_SESSION['userId'])){
            session_destroy();
            echo "user loged out succesfully";
        }
        else{
            echo "User not logged in";
        }
    }

}