<?php

namespace Src;

class SopInfoModel
{
    // Columns
    private $sopId;
    private $patientId;
    private $studyId;
    private $seriesId;
    private $sopInstanceUid;
    private $imgId;
    private $updDtm;
    private $regDtm;
    private $delYn;     
    

    // Getters and Setters
    public function setSopId($sopId)
    {
        $this->sopId = $sopId;
    }
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }
    public function setStudyId($studyId)
    {
        $this->studyId = $studyId;
    }
    public function setSeriesId($seriesId)
    {
        $this->seriesId = $seriesId;
    }
    public function setSopInstanceUid($sopInstanceUid)
    {
        $this->sopInstanceUid = $sopInstanceUid;
    }
    public function setImgId($imgId)
    {
        $this->imgId = $imgId;
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

    public function getSopId()
    {
        return $this->sopId;
    }
    public function getPatientId()
    {
        return $this->patientId;
    }
    public function getStudyId()
    {
        return $this->studyId;
    }
    public function getSeriesId()
    {
        return $this->seriesId;
    }
    public function getSopInstanceUid()
    {
        return $this->sopInstanceUid;
    }
    public function getImgId()
    {
        return $this->imgId;
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


    public function __construct($patientId, $studyId, $seriesId, $sopInstanceUid, $imgId, $updDtm, $regDtm, $delYn)
    {
        $this->patientId = $patientId;
        $this->studyId = $studyId;
        $this->seriesId = $seriesId;
        $this->sopInstanceUid = $sopInstanceUid;
        $this->imgId = $imgId;
        $this->updDtm = $updDtm;
        $this->regDtm = $regDtm;
        $this->delYn = $delYn;
    }


}