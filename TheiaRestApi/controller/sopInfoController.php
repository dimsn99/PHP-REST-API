<?php

namespace Src;

require $_SERVER['DOCUMENT_ROOT']."/TheiaRestApi/model/sopInfoModel.php";

use Src\SopInfoModel;

class SopInfoController
{
    // Connection
    private $conn;
    private $requstMethod;
    // Table
    private $dbTable = "sop_info";
    // Primary Key
    private $sopId;

	public function getSopId() {
		return $this->sopId;
	}
	
	public function setSopId($sopId){
		$this->sopId = $sopId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requstMethod, $sopId)
    {
        $this->conn = $conn;
        $this->requstMethod = $requstMethod;
        $this->sopId = $sopId;
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
                if($this->sopId){
                    $response = $this->updateSop($this->sopId);
                } else{
                    $response = $this->createSop();
                }
                break;
            //case 'PUT':
            //    $response = $this->updateSop($this->sopId);
            //    break;
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
                img_id, upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . "";
        
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


    private function createSop()
    {

        $model = new SopInfoModel($_POST['patient_id'], $_POST['study_id'], $_POST['series_id'],
                                    $_POST['sop_instance_uid'], $_POST['img_id'], $_POST['upd_dtm'],
                                    $_POST['reg_dtm'], $_POST['del_yn']);

        /*
        $this->setPatientId($_POST['patient_id']);
        $this->setStudyId($_POST['study_id']);
        $this->setSeriesId($_POST['series_id']);
        $this->setsopInstanceUid($_POST['sop_instance_uid']);
        $this->setImgId($_POST['img_id']);
        $this->setUpdDtm($_POST['upd_dtm']);
        $this->setRegDtm($_POST['reg_dtm']);
        $this->setDelYn($_POST['del_yn']);
        */

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
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Sop Created'));
        return $response;
    }


    private function updateSop($sopId)
    {
        $result = $this->find($sopId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $result['patient_id'] = $_POST['patient_id'];
        $result['study_id'] = $_POST['study_id'];
        $result['series_id'] = $_POST['series_id'];
        $result['sop_instance_uid'] = $_POST['sop_instance_uid'];
        $result['img_id'] = $_POST['img_id'];
        $result['upd_dtm'] = $_POST['upd_dtm'];
        $result['reg_dtm'] = $_POST['reg_dtm'];
        $result['del_yn'] = $_POST['del_yn'];

        $statement = "UPDATE " . $this->dbTable . "
                    SET
                        patient_id = :patientId,
                        study_id = :studyId,
                        series_id = :seriesId,
                        sop_instance_uid = :sopInstanceUid,
                        img_id = :imgId,
                        upd_dtm = :updDtm,
                        reg_dtm = :regDtm,
                        del_yn = 'N'
                    WHERE
                        sop_id = :sopId";
        
        try{
            $statement = $this->conn->prepare($statement);

            $statement->bindParam(":patientId", $result['patient_id']);
            $statement->bindParam(":studyId", $result['study_id']);
            $statement->bindParam(":seriesId", $result['series_id']);
            $statement->bindParam(":sopInstanceUid", $result['sop_instance_uid']);
            $statement->bindParam(":imgId", $result['img_id']);
            $statement->bindParam(":updDtm", $result['upd_dtm']);
            $statement->bindParam(":regDtm", $result['reg_dtm']);
            $statement->bindParam(":sopId", $sopId);

            $statement->execute();
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Sop Updated!'));
        return $response;
    }


    private function deleteSop($sopId)
    {
        $result = $this->find($sopId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $query = "DELETE FROM " . $this->dbTable . " WHERE sop_id = :sopId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":sopId", $result['sop_id']);

            $statement->execute();
            //$statement->rowCount();
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
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE sop_id = :sopId";
        
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


