<?php

namespace Src;

require "./model/studyInfoModel.php";

use Src\StudyInfoModel;

date_default_timezone_set("Asia/Seoul");

class StudyInfoController
{
    // Connection
    private $conn;
    private $requestMethod;
    // Table
    private $dbTable = "study_info";
    // Primary Key
    private $studyId;

    // temporary array for 'Create', 'Update'
    private $inputVals = array();
    // Data Created Time
    private $crtTime;


	public function getStudyId() {
		return $this->studyId;
	}
	public function setStudyId($studyId){
		$this->studyId = $studyId;
		return $this;
	}

    // DB Connection
    public function __construct($conn, $requestMethod, $studyId, $data)
    {
        $this->conn = $conn;
        $this->requestMethod = $requestMethod;
        $this->studyId = $studyId;
        $this->inputVals = $data;
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
                $response = $this->createStudy($this->inputVals);
                break;
            case 'PUT':
                $response = $this->updateStudy($this->studyId, $this->inputVals);
                break;
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


    private function createStudy($inputVals)
    {

        $this->crtTime = date("Y-m-d H:i:s");

        $model = new StudyInfoModel($inputVals['patient_id'], $inputVals['study_date'], $inputVals['study_instance_uid'],
                                    $this->crtTime, $this->crtTime, $inputVals['del_yn']);
        
        
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

        } catch(\PDOException $e){
            exit($e->getMessage());
        }

        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE study_id = LAST_INSERT_ID()";
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Study Created',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($response_body);

        return $response;
    }


    private function updateStudy($studyId, $inputVals)
    {
        
        $result = $this->find($studyId);

        if(! $result){
            return $this->notFoundResponse();
        }
        
        $query = "UPDATE " . $this->dbTable . " 
                    SET
                        study_date = :studyDate,
                        study_instance_uid = :studyInstanceUid,
                        upd_dtm = :updDtm
                    WHERE
                        study_id = :studyId";
        
        $updTime = date("Y-m-d H:i:s");
        
        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":studyDate", $inputVals['study_date']);
            $statement->bindParam(":studyInstanceUid", $inputVals['study_instance_uid']);
            $statement->bindParam(":updDtm", $updTime);
            $statement->bindParam(":studyId", $studyId, \PDO::PARAM_INT);

            $statement->execute();

        } catch(\PDOException $e){
            exit($e->getMessage());
        }


        $query2 = "SELECT * FROM " . $this->dbTable . " WHERE study_id = " . $studyId;
        $statement2 = $this->conn->query($query2);


        $response_body = array(
            'message' => 'Study Updated',
            'model' => $statement2->fetchAll(\PDO::FETCH_ASSOC)
        );

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($response_body);

        return $response;
    }


    private function deleteStudy($studyId)
    {
        $result = $this->find($studyId);

        if(! $result){
            return $this->notFoundResponse();
        }

        //$query = "DELETE FROM " . $this->dbTable . " WHERE study_id = :studyId";
        $query = "UPDATE " . $this->dbTable . " SET del_yn = 'Y' WHERE study_id = :studyId";

        try{
            $statement = $this->conn->prepare($query);

            $statement->bindParam(":studyId", $result['study_id']);

            $statement->execute();

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
                upd_dtm, reg_dtm, del_yn FROM " . $this->dbTable . " WHERE study_id = :studyId AND del_yn = 'N'";
        
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


