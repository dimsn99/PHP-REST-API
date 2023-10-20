<?php

namespace Src;

require "./model/sopInfoModel.php";

use Src\SopInfoModel;

date_default_timezone_set("Asia/Seoul");

class SopInfoController
{
    // Connection
    private $conn;
    private $requstMethod;
    // Table
    private $dbTable = "sop_info";
    // Primary Key
    private $sopId;

    // temporary array for 'Create', 'Update'
    private $inputVals = array();
    // Data Created Time
    private $crtTime;


	public function getSopId() {
		return $this->sopId;
	}
	public function setSopId($sopId){
		$this->sopId = $sopId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requstMethod, $sopId, $data)
    {
        $this->conn = $conn;
        $this->requstMethod = $requstMethod;
        $this->sopId = $sopId;
        $this->inputVals = $data;
    }


    public function processRequest()
    {
        switch($this->requstMethod){
            case 'GET':
                if($this->sopId){
                    $response = $this->getSop($this->sopId);
                } else{
                    $response = $this->getAllSops();
                };
                break;
            case 'POST':
                $response = $this->createSop($this->inputVals);
                break;
            case 'PUT':
                $response = $this->updateSop($this->sopId, $this->inputVals);
                break;
            case 'DELETE':
                $response = $this->deleteSop($this->sopId);
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


    private function getAllSops()
    {
        $query = "SELECT sop_id, patient_id, study_id, series_id, sop_instance_uid, 
                img_id, upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE del_yn = 'N'";
        
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


    private function getSop($sopId)
    {
        $result = $this->find($sopId);
        if(!$result){
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function createSop($inputVals)
    {

        $this->crtTime = date("Y-m-d H:i:s");

        $model = new SopInfoModel($inputVals['patient_id'], $inputVals['study_id'], $inputVals['series_id'],
                                    $inputVals['sop_instance_uid'], $inputVals['img_id'], $this->crtTime,
                                    $this->crtTime, $inputVals['del_yn']);


        $query = "INSERT INTO " . $this->dbTable . " (sop_id, patient_id, 
                study_id, series_id, sop_instance_uid, img_id, upd_dtm, reg_dtm, del_yn)
                VALUES (:sopId, :patientId, :studyId, :seriesId, :sopInstanceUid, :imgId,
                :updDtm, :regDtm, 'N')";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":sopId", $model->getSopId());
            $statement->bindParam(":patientId", $model->getPatientId());
            $statement->bindParam(":studyId", $model->getStudyId());
            $statement->bindParam(":seriesId", $model->getSeriesId());
            $statement->bindParam(":sopInstanceUid", $model->getSopInstanceUid());
            $statement->bindParam(":imgId", $model->getImgId());
            $statement->bindParam(":updDtm", $model->getUpdDtm());
            $statement->bindParam(":regDtm", $model->getRegDtm());
            
            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE sop_id = LAST_INSERT_ID()";
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Sop Created',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($response_body);
        
        return $response;
    }


    private function updateSop($sopId, $inputVals)
    {
        $result = $this->find($sopId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $query = "UPDATE " . $this->dbTable . "
                    SET
                        sop_instance_uid = :sopInstanceUid,
                        upd_dtm = :updDtm
                    WHERE
                        sop_id = :sopId";
        
        $updTime = date("Y-m-d H:i:s");
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":sopInstanceUid", $inputVals['sop_instance_uid']);
            $statement->bindParam(":updDtm", $updTime);
            $statement->bindParam(":sopId", $sopId, \PDO::PARAM_INT);

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE sop_id = " . $sopId;
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Sop Updated',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($response_body);
        
        return $response;
    }


    private function deleteSop($sopId)
    {
        $result = $this->find($sopId);

        if(! $result){
            return $this->notFoundResponse();
        }

        //$query = "DELETE FROM " . $this->dbTable . " WHERE sop_id = :sopId";
        $query = "UPDATE " . $this->dbTable . " SET del_yn = 'Y' WHERE sop_id = :sopId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":sopId", $result['sop_id']);

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Sop Deleted!'));
        return $response;
    }


    public function find($sopId)
    {
        $query = "SELECT sop_id, patient_id, study_id, series_id, sop_instance_uid, img_id,
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE sop_id = :sopId AND del_yn = 'N'";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":sopId", $sopId);

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


