<?php

namespace Src;

require "./model/seriesInfoModel.php";

use Src\SeriesInfoModel;

date_default_timezone_set("Asia/Seoul");

class SeriesInfoController
{
    // Connection
    private $conn;
    private $requstMethod;
    // Table
    private $dbTable = "series_info";
    // Primary Key
    private $seriesId;

    // temporary array for 'Create', 'Update'
    private $inputVals = array();
    // Data Created Time
    private $crtTime;


	public function getSeriesId() {
		return $this->seriesId;
	}
	public function setSeriesId($seriesId){
		$this->seriesId = $seriesId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requstMethod, $seriesId, $data)
    {
        $this->conn = $conn;
        $this->requstMethod = $requstMethod;
        $this->seriesId = $seriesId;
        $this->inputVals = $data;
    }


    public function processRequest()
    {
        switch($this->requstMethod){
            case 'GET':
                if($this->seriesId){
                    $response = $this->getSeries($this->seriesId);
                } else{
                    $response = $this->getAllSeries();
                };
                break;
            case 'POST':
                $response = $this->createSeries($this->inputVals);
                break;
            case 'PUT':
                $response = $this->updateSeries($this->seriesId, $this->inputVals);
                break;
            case 'DELETE':
                $response = $this->deleteSeries($this->seriesId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if($response['body']){
            echo $response['body'];
        }
    }


    private function getAllSeries()
    {
        $query = "SELECT series_id, patient_id, study_id, series_instance_uid, 
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE del_yn = 'N'";
        
        try{
            $statement = $this->conn->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function getSeries($seriesId)
    {
        $result = $this->find($seriesId);
        if(!$result){
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function createSeries($inputVals)
    {

        $this->crtTime = date("Y-m-d H:i:s");

        $model = new SeriesInfoModel($inputVals['patient_id'], $inputVals['study_id'], $inputVals['series_instance_uid'],
                                    $this->crtTime, $this->crtTime, $inputVals['del_yn']);

        $query = "INSERT INTO " . $this->dbTable . " (series_id, patient_id, 
                study_id, series_instance_uid, upd_dtm, reg_dtm, del_yn)
                VALUES (:seriesId, :patientId, :studyId, :seriesInstanceUid, 
                :updDtm, :regDtm, 'N')";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":seriesId", $model->getSeriesId());
            $statement->bindParam(":patientId", $model->getPatientId());
            $statement->bindParam(":studyId", $model->getStudyId());
            $statement->bindParam(":seriesInstanceUid", $model->getSeriesInstanceUid());
            $statement->bindParam(":updDtm", $model->getUpdDtm());
            $statement->bindParam(":regDtm", $model->getRegDtm());

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE series_id = LAST_INSERT_ID()";
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Series Created',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($response_body);

        return $response;
    }


    private function updateSeries($seriesId, $inputVals)
    {
        $result = $this->find($seriesId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $query = "UPDATE " . $this->dbTable . "
                    SET    
                        series_instance_uid = :seriesInstanceUid,
                        upd_dtm = :updDtm
                    WHERE
                        series_id = :seriesId";
        
        $updTime = date("Y-m-d H:i:s");

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":seriesInstanceUid", $inputVals['series_instance_uid']);
            $statement->bindParam(":updDtm", $updTime);
            $statement->bindParam(":seriesId", $seriesId, \PDO::PARAM_INT);

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE series_id = " . $seriesId;
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Series Updated',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );


        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($response_body);

        return $response;
    }


    private function deleteSeries($seriesId)
    {
        $result = $this->find($seriesId);

        if(! $result){
            return $this->notFoundResponse();
        }

        //$query = "DELETE FROM " . $this->dbTable . " WHERE series_id = :seriesId";
        $query = "UPDATE " . $this->dbTable . " SET del_yn = 'Y' WHERE series_id = :seriesId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":seriesId", $result['series_id']);

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Series Deleted!'));
        return $response;
    }


    public function find($seriesId)
    {
        $query = "SELECT series_id, patient_id, study_id, series_instance_uid, 
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE series_id = :seriesId AND del_yn = 'N'";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":seriesId", $seriesId);

            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }


}


