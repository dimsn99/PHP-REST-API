<?php

namespace Src;

require $_SERVER['DOCUMENT_ROOT']."/TheiaRestApi/model/seriesInfoModel.php";

use Src\SeriesInfoModel;

class SeriesInfoController
{
    // Connection
    private $conn;
    private $requstMethod;
    // Table
    private $dbTable = "series_info";
    // Primary Key
    private $seriesId;

	public function getSeriesId() {
		return $this->seriesId;
	}
	public function setSeriesId($seriesId){
		$this->seriesId = $seriesId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requstMethod, $seriesId)
    {
        $this->conn = $conn;
        $this->requstMethod = $requstMethod;
        $this->seriesId = $seriesId;
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
                if($this->seriesId){
                    $response = $this->updateSeries($this->seriesId);
                } else{
                    $response = $this->createSeries();
                }
                break;
            //case 'PUT':
            //    $response = $this->updateSeries($this->seriesId);
            //    break;
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
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . "";
        
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


    private function createSeries()
    {

        $model = new SeriesInfoModel($_POST['patient_id'], $_POST['study_id'], $_POST['series_instance_uid'],
                                    $_POST['upd_dtm'], $_POST['reg_dtm'], $_POST['del_yn']);

        /*
        $this->setPatientId($_POST['patient_id']);
        $this->setStudyId($_POST['study_id']);
        $this->setseriesInstanceUid($_POST['series_instance_uid']);
        $this->setUpdDtm($_POST['upd_dtm']);
        $this->setRegDtm($_POST['reg_dtm']);
        $this->setDelYn($_POST['del_yn']);
        */

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
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Series Created'));
        return $response;
    }


    private function updateSeries($seriesId)
    {
        $result = $this->find($seriesId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $result['patient_id'] = $_POST['patient_id'];
        $result['study_id'] = $_POST['study_id'];
        $result['series_instance_uid'] = $_POST['series_instance_uid'];
        $result['upd_dtm'] = $_POST['upd_dtm'];
        $result['reg_dtm'] = $_POST['reg_dtm'];
        $result['del_yn'] = $_POST['del_yn'];
    

        $statement = "UPDATE " . $this->dbTable . "
                    SET
                        patient_id = :patientId,
                        study_id = :studyId,
                        series_instance_uid = :seriesInstanceUid,
                        upd_dtm = :updDtm,
                        reg_dtm = :regDtm,
                        del_yn = 'N'
                    WHERE
                        series_id = :seriesId";
        
        try{
            $statement = $this->conn->prepare($statement);

            $statement->bindParam(":patientId", $result['patient_id']);
            $statement->bindParam(":studyId", $result['study_id']);
            $statement->bindParam(":seriesInstanceUid", $result["series_instance_uid"]);
            $statement->bindParam(":updDtm", $result['upd_dtm']);
            $statement->bindParam(":regDtm", $result['reg_dtm']);
            $statement->bindParam(":seriesId", $seriesId);

            $statement->execute();
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Series Updated!'));
        return $response;
    }


    private function deleteSeries($seriesId)
    {
        $result = $this->find($seriesId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $query = "DELETE FROM " . $this->dbTable . " WHERE series_id = :seriesId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":seriesId", $result['series_id']);

            $statement->execute();
            //$statement->rowCount();
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
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE series_id = :seriesId";
        
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


