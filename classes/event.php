<?php

class Event
{

    public const TBL_EVENT = "tbl_event";

    public bool $debug = false;

    private ?int $eventId = null;

    private ?string $eventName = null;

    private ?string $eventDate = null;

    private float $eventFee;

    private mysqli $connection;

    public function setEventID(?int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function getEventID():? int
    {
        return $this->eventId;
    }

    public function setEventName(?string $eventName)
    {
        $this->eventName = $eventName;
    }

    public function getEventName():? string
    {
        return $this->eventName;
    }

    public function setEventDate(?string $eventDate)
    {
        $this->eventDate = $eventDate;
    }

    public function getEventDate():? string
    {
        return $this->eventDate;
    }

    public function setEventParticipationFee(float $eventFee)
    {
        $this->eventFee = $eventFee;
    }

    public function getEventParticipationFee(): float
    {
        return $this->eventFee;
    }

    public function setConnection(mysqli $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function fInsert()
    {
        $vName = $this->getEventName();
        $vDate = $this->getEventDate();
        $vFee = $this->getEventParticipationFee();
        $aBindParam = [];
        $sBindString = "";

        $sQuery = "INSERT INTO " . Event::TBL_EVENT . " " . chr(10);
        $sQuery .= " (`ev_name`, `ev_fee`, `ev_date`) " . chr(10);
        $sQuery .= " VALUES (?, ?, ?)";
        $aBindParam[] = $vName;
        $aBindParam[] = $vFee;
        $aBindParam[] = $vDate;
        $sBindString .= "sds";
        if ($this->debug) {
            print_r($sQuery);
            print_r($aBindParam);
            print_r($sBindString);
        }
        try {
            $vConn = $this->getConnection();
            $vStatement = $vConn->prepare($sQuery);
            if (!empty($aBindParam))
                $vStatement->bind_param($sBindString, ...$aBindParam);
            $vStatement->execute();
            if ($vConn->insert_id)
                return $vConn->insert_id;
            else
                return false;
        } catch (Exception $excep) {
            trigger_error(
                    "Error in Inserting: " . $excep->getMessage() . " (" . $excep->getCode() . ")",
                    E_USER_ERROR
            );
        }
    }

    function fList() {
        $vEventID = $this->getEventID();
        $vName = $this->getEventName();
        $vDate = $this->getEventDate();
        $vFee = $this->getEventParticipationFee();
        $aBindParam = [];
        $sBindString = "";
        $aResult = [];

        $sQuery = "SELECT  id,`ev_name`, `ev_fee`, `ev_date` FROM " . Event::TBL_EVENT . " "
                . " WHERE ev_status <> 5 "; //5:deleted

        if ($vEventID) {
            $sBindString .= "i";
            $aBindParam[] = $vEventID;
            $sQuery .= " AND id = ?";
        }
        if (!empty($vName)) {
            $sBindString .= "s";
            $aBindParam[] = "%" . $vName . "%";
            $sQuery .= " AND ev_name LIKE ?";
        }
        if (!empty($vDate)) {
            $sBindString .= "s";
            $aBindParam[] = "%" . $vDate . "%";
            $sQuery .= " AND ev_date LIKE ?";
        }
        if ((float) $vFee >= 0) {
            $sBindString .= "d";
            $aBindParam[] = $vFee;
            $sQuery .= " AND ev_fee = ?";
        }
        if ($this->debug) {
            print_r($sQuery);
            print_r($aBindParam);
            print_r($sBindString);
        }

        try {
            $vConn = $this->getConnection();
            $vStatement = $vConn->prepare($sQuery);
            //var_dump($sBindString);
            //var_dump($aBindParam); exit;
            if (!empty($aBindParam))
                $vStatement->bind_param($sBindString, ...$aBindParam);

            $vStatement->execute();
            $vStatement->bind_result($id, $ev_name, $ev_fee, $ev_date);
            while ($vStatement->fetch()) {
                $aResult[] = (object) [
                            "id" => $id,
                            "eventName" => $ev_name,
                            "eventFee" => $ev_fee,
                            "eventDate" => $ev_date
                ];
            }
            $vStatement->close();
        } catch (Exception $excep) {
            trigger_error("Selection failed with Error: " . $excep->getMessage() . " (" . $excep->getCode() . ")", E_USER_ERROR);
        }

        return $aResult;
    }

    public function fReadEventsJson($vPath = "assets/", $vFileName = "events.json") {
        $strJsonFileContents = file_get_contents($vPath . $vFileName);
        return json_decode($strJsonFileContents, true);
    }

}
