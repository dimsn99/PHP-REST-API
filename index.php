<?php
    
    error_reporting(E_ERROR | E_PARSE);

    require "./common/Database.php";
    require "./controller/studyInfoController.php";
    require "./controller/seriesInfoController.php";
    require "./controller/sopInfoController.php";

    use Src\Database;
    use Src\StudyInfoController;
    use Src\SeriesInfoController;
    use Src\SopInfoController;

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    $parseUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $parseUri);
    $params = Array();
    

    $data = json_decode(file_get_contents("php://input"),TRUE);
    // endpoints starting with '/studyInfo' for GET shows all studyInfos
    // everything else results in a 404 Not Found
    if($uri[2] !== 'studyInfo'){
        if($uri[2] !== 'seriesInfo'){
            if($uri[2] !== 'sopInfo'){
                header("HTTP/1.1 404 Not Found");
                exit();
            }
        }
    }


    $requestMethod = $_SERVER["REQUEST_METHOD"];

    /////////////////////////////////////////////////////////////////////////
    $controller = null;
    $dbConnection = (new Database())->connect();

    if ($uri[2] == 'studyInfo') {        
        // the study id is, of course, optional and must be a number
        $studyId = null;
        if(isset($uri[3])){
            $studyId = (int) $uri[3];
        }
        $controller = new StudyInfoController($dbConnection, $requestMethod, $studyId, $data);
    
    } elseif ($uri[2] == 'seriesInfo') {
    
        // the series id is, of course, optional and must be a number
        $seriesId = null;
        if(isset($uri[3])){
            $seriesId = (int) $uri[3];
        }
        $controller = new SeriesInfoController($dbConnection, $requestMethod, $seriesId, $data);
    
    } elseif ($uri[2] == 'sopInfo') {
    
        // the sop id is, of course, optional and must be a number
        $sopId = null;
        if(isset($uri[3])){
            $sopId = (int) $uri[3];
        }
       $controller = new SopInfoController($dbConnection, $requestMethod, $sopId, $data);    
    }
    if ($controller != null) $controller->processRequest();


?>
