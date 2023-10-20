<?php

namespace Src;

class SeriesInfoModel
{
    // Columns
    private $seriesId;
    private $patientId;
    private $studyId;
    private $seriesInstanceUid;
    private $updDtm;
    private $regDtm;
    private $delYn;     
    

    // Getters and Setters
    public function setSeriesId($seriesId)
    {
        $this->seriesId = $seriesId;
    }
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }
    public function setStudyId($studyId)
    {
        $this->studyId = $studyId;
    }
    public function setSeriesInstanceUid($seriesInstanceUid)
    {
        $this->seriesInstanceUid = $seriesInstanceUid;
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

    public function getSeriesId()
    {
        return $this->seriesId;
    }
    public function getPatientId()
    {
        return $this->patientId;
    }
    public function getStudyId()
    {
        return $this->studyId;
    }
    public function getSeriesInstanceUid()
    {
        return $this->seriesInstanceUid;
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


    public function __construct($patientId, $studyId, $seriesInstanceUid, $updDtm, $regDtm, $delYn)
    {
        $this->patientId = $patientId;
        $this->studyId = $studyId;
        $this->seriesInstanceUid = $seriesInstanceUid;
        $this->updDtm = $updDtm;
        $this->regDtm = $regDtm;
        $this->delYn = $delYn;
    }


}