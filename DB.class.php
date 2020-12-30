<?php

include_once("vendor\autoload.php");
class DB{
    const USER_NAME="ihsdbyduagcybasd";
    const PASS_WORD="723dygyd673t6edg3g26tet2364tgtg6d2te6734tgdg6tqg3dg";
    public $connection;
    public $h;
    
    function __construct(){
        $this->connection = new PDO("mysql:host=localhost;dbname=Udictate;charset=utf8", DB::USER_NAME, DB::PASS_WORD);
        $this->connection->setAttribute(PDO::ERRMODE_WARNING, true);
        $connection=$this->connection;

        // create a new mysql query builder
        $this->h = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
        {
            $statement = $connection->prepare($queryString);
            $statement->execute($queryParameters);

            // when the query is fetchable return all results and let hydrahon do the rest
            // (there's no results to be fetched for an update-query for example)
            if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
            {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
        });


        
    }

}