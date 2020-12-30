<?php
require_once("vendor\autoload.php");

$v = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
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