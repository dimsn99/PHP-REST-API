<?php

namespace Src;

require $_SERVER['DOCUMENT_ROOT']."/TheiaRestApi/model/studyInfoModel.php";

use Src\StudyInfoModel;

class StudyInfoController
{
    // Connection
    private $conn;
    private $requestMethod;
    // Table
    private $dbTable = "study_info";
    // Primary Key
    private $studyId;

	public function getStudyId() {
		return $this->studyId;
	}
	public function setStudyId($studyId){
		$this->studyId = $studyId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requestMethod, $studyId)
    {
        $this->conn = $conn;
        $this->requestMethod = $requestMethod;
        $this->studyId = $studyId;
    }


    public function processRequest()
    {
        switch($this->requestMethod){
            case 'GET':
                if($this->studyId){
                    $response = $this->getStudy($this->studyId);
                } else{
                    $response = $this->getAllStudies();
                };
                break;
            case 'POST':
                if($this->studyId){
                    $response = $this->updateStudy($this->studyId);
                } else{
                    $response = $this->createStudy();
                }
                break;
            //case 'PUT':
            //    $response = $this->updateStudy($this->studyId);
            //    break;
            case 'DELETE':
                $response = $this->deleteStudy($this->studyId);
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


    private function getAllStudies()
    {
        $query = "SELECT study_id, patient_id, study_date, study_instance_uid, 
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


    private function getStudy($studyId)
    {
        $result = $this->find($studyId);
        if(!$result){
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function createStudy()
    {

        $model = new StudyInfoModel($_POST['patient_id'], $_POST['study_date'], $_POST['study_instance_uid'],
                                    $_POST['upd_dtm'], $_POST['reg_dtm'], $_POST['del_yn']);

        /*
        $this->model->setPatientId($_POST['patient_id']);
        $this->model->setStudyDate($_POST['study_date']);
        $this->model->setstudyInstanceUid($_POST['study_instance_uid']);
        $this->model->setUpdDtm($_POST['upd_dtm']);
        $this->model->setRegDtm($_POST['reg_dtm']);
        $this->model->setDelYn($_POST['del_yn']);
        */

        $query = "INSERT INTO " . $this->dbTable . " (study_id, patient_id, 
                study_date, study_instance_uid, upd_dtm, reg_dtm, del_yn)
                VALUES (:studyId, :patientId, :studyDate, :studyInstanceUid, 
                :updDtm, :regDtm, 'N')";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":studyId", $model->getStudyId());
            $statement->bindParam(":patientId", $model->getPatientId());
            $statement->bindParam(":studyDate", $model->getStudyDate());
            $statement->bindParam(":studyInstanceUid", $model->getStudyInstanceUid());
            $statement->bindParam(":updDtm", $model->getUpdDtm());
            $statement->bindParam(":regDtm", $model->getRegDtm());

            $statement->execute();
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Study Created'));
        return $response;
    }


    private function updateStudy($studyId)
    {
        
        $result = $this->find($studyId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $result['patient_id'] = $_POST['patient_id'];
        $result['study_date'] = $_POST['study_date'];
        $result['study_instance_uid'] = $_POST['study_instance_uid'];
        $result['upd_dtm'] = $_POST['upd_dtm'];
        $result['reg_dtm'] = $_POST['reg_dtm'];
        $result['del_yn'] = $_POST['del_yn'];
    

        $statement = "UPDATE " . $this->dbTable . " 
                    SET
                        patient_id = :patientId,
                        study_date = :studyDate,
                        study_instance_uid = :studyInstanceUid,
                        upd_dtm = :updDtm,
                        reg_dtm = :regDtm,
                        del_yn = 'N'
                    WHERE
                        study_id = :studyId";
        
        try{
            $statement = $this->conn->prepare($statement);

            $statement->bindParam(":patientId", $result['patient_id']);
            $statement->bindParam(":studyDate", $result['study_date']);
            $statement->bindParam(":studyInstanceUid", $result['study_instance_uid']);
            $statement->bindParam(":updDtm", $result['upd_dtm']);
            $statement->bindParam(":regDtm", $result['reg_dtm']);
            $statement->bindParam(":studyId", $studyId);

            $statement->execute();
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Study Updated!'));
        return $response;
    }


    private function deleteStudy($studyId)
    {
        $result = $this->find($studyId);

        if(! $result){
            return $this->notFoundResponse();
        }

        $query = "DELETE FROM " . $this->dbTable . " WHERE study_id = :studyId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":studyId", $result['study_id']);

            $statement->execute();
            //$statement->rowCount();
        } catch(\PDOException $e){
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Study Deleted!'));
        return $response;
    }


    public function find($studyId)
    {
        $query = "SELECT study_id, patient_id, study_date, study_instance_uid, 
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE study_id = :studyId";
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":studyId", $studyId);

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


