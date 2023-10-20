<?php

namespace Src;

class StudyInfoModel
{
    // Columns
    private $studyId;
    private $patientId;
    private $studyDate;
    private $studyInstanceUid;
    private $updDtm;
    private $regDtm;
    private $delYn;     
    
    // Getters and Setters
    public function setStudyId($studyId)
    {
        $this->studyId = $studyId;
    }
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }
    public function setStudyDate($studyDate)
    {
        $this->studyDate = $studyDate;
    }
    public function setStudyInstanceUid($studyInstanceUid)
    {
        $this->studyInstanceUid = $studyInstanceUid;
    }
    public function setUpdDtm($updDtm)
    {
        $this->updDtm = $updDtm;
    }
    public function setRegDtm($regDtm)
    {
        $this->regDtm = $regDtm;
    }
    public function setDelYn($delYn)
    {
        $this->delYn = $delYn;
    }

    public function getStudyId()
    {
        return $this->studyId;
    }
    public function getPatientId()
    {
        return $this->patientId;
    }
    public function getStudyDate()
    {
        return $this->studyDate;
    }
    public function getStudyInstanceUid()
    {
        return $this->studyInstanceUid;
    }
    public function getUpdDtm()
    {
        return $this->updDtm;
    }
    public function getRegDtm()
    {
        return $this->regDtm;
    }
    public function getDelYn()
    {
        return $this->delYn;
    }    

    
    public function __construct($patientId, $studyDate, $studyInstanceUid, $updDtm, $regDtm, $delYn)
    {
        $this->patientId = $patientId;
        $this->studyDate = $studyDate;
        $this->studyInstanceUid = $studyInstanceUid;
        $this->updDtm = $updDtm;
        $this->regDtm = $regDtm;
        $this->delYn = $delYn;
    }


}