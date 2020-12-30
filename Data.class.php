<?php
// This is a general class for data manipulation
class Data{
    const DATABASE="Udictate";
    const USER_NAME="Vilclericanas";
    const PASS_WORD="ah0032iojhjfcsw6r5q3r99xghwqvy";
    public $data=[];
    private $connection;
    private $i=0;
    private $j=0;
    private $arrayLength;
    public $mergedArray=[];
    public $Kvalue;
    public $Vvalue;
    public $outputArray;

    private function initiateConnection(){
        $DATABASE=Data::DATABASE;
        $USER_NAME=Data::USER_NAME;
        $PASS_WORD=Data::PASS_WORD;
        try{
            $connection= new PDO("mysql:host=localhost;dbname=$DATABASE", $USER_NAME,$PASS_WORD);
            return $connection;
        }
        catch(PDOException $exception){
            printf("connection failed: %s", $exception->getMessage());
        }

    }
    private function prepPositionalPlaceholders($data,$columns){
        if(array_diff($data,$columns)!==[]){
            $stringData="";
            $mergedArray=array_combine($data,$columns);
            foreach($mergedArray as $Kvalue=>$Vvalue){
                if($Vvalue!==end($mergedArray)){
                    $stringData.="$Vvalue=?, ";
                    //echo "$Vvalue=?, ";
                }
                else{
                    $stringData.="$Vvalue=? ";
                    //"$Vvalue=? ";
                }
            }
        }
        else{

        }
        echo $stringData;
        return $stringData;
    }

    public function updateData($table,$data,$columns,$param,$condition,$param1=false,$condition1=false,$groupBy=false){
        $updateOptions=Data::prepPositionalPlaceholders($data,$columns) ;
        if($groupBy!==false){
            if($param1!==false){
                $query="UPDATE $table SET $updateOptions where $param=? AND $param1=? GROUP BY ?";
                echo $query;
                array_push($data,$condition,$condition1,$groupBy);
                $stm=Data::initiateConnection()->prepare($query)->execute($data);
            }
            else{
                $query="UPDATE $table SET $updateOptions where $param=? GROUP BY ?";
                echo $query;
                array_push($data,$condition,$groupBy);
                $stm=Data::initiateConnection()->prepare($query)->execute($data);
            }
        }
        else{
            if($param1!==false){
                $query="UPDATE $table SET $updateOptions where $param=? AND $param1=?";
                echo $query;
                array_push($data,$condition,$condition1);
                $stm=Data::initiateConnection()->prepare($query)->execute($data);
            }
            else{
                $query="UPDATE $table SET $updateOptions where $param=?";
                echo $query;
                array_push($data,$condition);
                $stm=Data::initiateConnection()->prepare($query)->execute($data);
            }   
        }

    }
    public function AddNewData($table,$data,$columns){
        $arrayLength=count($columns);
        echo $arrayLength;
        $stringData="";
        $stringColumns="";
        for($i=0;$i<$arrayLength;$i++){
            $current=$columns[$i];
            if($columns[$i]!==$columns[$arrayLength-1]){
                $stringData.="$current,";
                $stringColumns.="?,";
            }
            else{
                $stringData.="$current";
                $stringColumns.="?";
            }
        }
        $query="INSERT INTO $table($stringData) VALUES($stringColumns)";
        $stm=Data::initiateConnection()->prepare($query)->execute($data);
    }
    public function findData($table,$param=false,$condition=false,$param1=false,$condition1=false){
        
        if($param1==false){
            $stmt = Data::initiateConnection()->prepare("SELECT * FROM $table WHERE $param=?");
            $stmt->execute([$condition]); 
            return $stmt->fetch();
        }

        else{
            $query="SELECT * FROM $table WHERE $param=? AND $param1=?";
            $stm = Data::initiateConnection()->prepare($query);
            $stm->execute([$condition,$condition1]);
            return $stm->fetch();
        }
    }
    public function readData($table,$param=false,$condition=false,$param1=false,$condition1=false){
        if($param1!==false&& $param!==false){
            $query="SELECT * FROM $table WHERE $param=? AND $param1=?";
            $stm=Data::initiateConnection()->prepare($query);
            $stm->execute([$condition,$condition1]);
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        if($param!==false && $param1==false){
            $query="SELECT * FROM $table WHERE $param=?";
            $stm=Data::initiateConnection()->prepare($query);
            $stm->execute([$condition]);
            return $stm->fetchAll();
        }
        if($param==false && $param1==false){
            $query="SELECT * FROM $table";
            $stm=Data::initiateConnection()->query($query);
            return $stm->fetchAll();
        }
        if($param1!==false && $param==false){
            $query="SELECT * FROM $table WHERE $param1=?";
            $stm=Data::initiateConnection()->prepare($query);
            $stm->execute([$condition1]);
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
    }

}