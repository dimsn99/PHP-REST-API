<?php

namespace Src;

class Database
{

    private $dbConnection = null;

    public function __construct()
    {

        $host = '127.0.0.1';
        $port = '5306';
        $db = 'TheiaDB';
        $user = 'root';
        $pass = 'GenorayTheia';

        try{
            $this->dbConnection = new \PDO(
                "mysql:host=$host; port=$port; dbname=$db",
                $user,
                $pass
            );
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
    }

    public function connect()
    {
        return $this->dbConnection;
    }

}

?>
