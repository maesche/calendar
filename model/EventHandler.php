<?php

include_once("helpers" . DIRECTORY_SEPARATOR . "ErrorHandler.php");
include_once("model/class/Room.php");
include_once("model/class/Event.php");
include_once("model/class/Db.php");

class EventHandler {

    /**
     * Retourne un ou plusieurs événements dans un array
     *
     * @params $room (Room), $begin (Date), $end (Date), $id (int)
     * @return array(Event)
     */
    public function getEvents($room=null, $begin=null, $end=null, $id = null) {
        $db = new Db();
        $events = array();



        $select_Event =
                "SELECT
                    E.event_id,
                    E.owner,
                    E.title,
                    E.description,
                    DATE_FORMAT(D.begin, '%Y-%m-%d') AS E_Dbegin,
                    DATE_FORMAT(D.begin, '%H:%i') AS E_Hbegin,
                    DATE_FORMAT(D.end, '%Y-%m-%d') AS E_Dend,
                    DATE_FORMAT(D.end, '%H:%i') AS E_Hend,
                    D.event_date_id,
                    E.mode,
                    (SELECT
                        DATE_FORMAT(MAX(D.end), '%Y-%m-%d') as lastDate
                     FROM
                        event_dates D
                     WHERE
                        E.event_id = D.event_id) AS lastDate
                FROM events E
                     LEFT OUTER JOIN event_dates D
                         ON E.event_id = D.event_id ";

        $sql_where = "";

        $sql_orderby = "";
//si aucune id n'est disponible, nous recherchons tous les événements
//dans l'intervalle indiqué

        if ($id == null) {
            $sql_where .= "WHERE ";
//si la date de début n'est pas nulle
            if ($begin != null) {
                $sql_where .= "D.begin >= '$begin'";
//si la date de fin n'est pas nulle
                if ($end != null) {
                    $sql_where .= " AND D.end <= '$end 23:59' AND ";
                }
            }
//chercher dans une salle donnée
            if ($room != null) {
                $roomId = $room->getId();
                $sql_where .= "E.room_id=$roomId ";
            } else {
                $sql_where .= "1=1 ";
            }
        } else {
            $sql_where .= "WHERE E.event_id = $id ";
        }

        $sql_orderby .= "ORDER BY D.begin";

        $sql = $select_Event . $sql_where . $sql_orderby;



        $return = $db->select($sql);


        foreach ($return as $ret) {
            $E_id = $ret["event_id"];
            $E_owner = stripslashes($ret["owner"]);
            $E_title = stripslashes($ret["title"]);
            $E_description = stripslashes($ret["description"]);
            $E_Dbegin = $ret["E_Dbegin"];
            $E_Hbegin = $ret["E_Hbegin"];
            $E_Dend = $ret["E_Dend"];
            $E_Hend = $ret["E_Hend"];
            $D_id = $ret["event_date_id"];
            $E_mode = $ret["mode"];
            $E_lastDate = $ret["lastDate"];

            $event = new Event($E_id, $E_owner, $E_title, $E_description, $E_Dbegin, $E_Hbegin, $E_Dend, $E_Hend, $E_mode, $D_id, $E_lastDate);

            $events[] = $event;
        }


        return $events;
    }

    /* is called for current repeating event in eventdialog
     * get all other repeating dates for event
     */

    public function getSiblings($event, $after = null) {
        $dates = null;
        $db = new Db();

        $sql = "SELECT
                    event_date_id,
                    DATE_FORMAT(begin, '%Y-%m-%d') AS date,
                    DATE_FORMAT(begin, '%H:%i') AS begin,
                    DATE_FORMAT(end, '%H:%i') AS end
                FROM
                    event_dates
                WHERE
                    event_id='{$event->getId()}'";

        if ($after != null) {
            $sql .= " AND end >'$after'";
        }

        $return = $db->select($sql);

        foreach ($return as $ret) {
            $id = $ret["event_date_id"];
            $date = $ret["date"];
            $start = $ret["begin"];
            $end = $ret["end"];
            $dates[] = array("event_id" => $id, "date" => $date, "start" => $start, "end" => $end);
        }

        return $dates;
    }

    /*
     * Update an event
     * 
     * 
     * $dates = array(date => array("start"=> $start, "end" => $end))
     */

    public function update($room, $event, $dates = null) {

        $event_id = $event->getId();


        //Update whole event
        if ($dates == null) {

            $db = new Db();

            $sql = "UPDATE events SET";
            $sql .= " owner='{$event->getOwner()}'";

            $title = $event->getTitle();
            $description = $event->getDescription();
            $mode = $event->getMode();

            if (isset($title)) {
                $sql .= ", title='{$event->getTitle()}'";
            }
            if (isset($description)) {
                $sql .= ", description='{$event->getDescription()}'";
            }
            if (isset($mode)) {
                $sql .= ", mode='{$event->getMode()}'";
            }
            $sql .= " WHERE event_id='$event_id'";

            $db->update($sql);
        } 
        //update dates specified in $dates
        else {
            $this->add($room, $event, $dates);
            $this->delete($room, $event, true, $event->getDBegin());
        }

        return true;
    }

    /*
     * @param $room : room
     * @param $event : événement
     * @param $forceInsert: si un événement est répétitif et qu'une partie des
     *                      plages horaires est déjà prise, on insert la partie
     *                      qui n'est pas rejetée
     */

    /*
     * dates = array($date => array("start"=> $start, "end"=> $end))
     */

    public function add($room, $event, $dates = null) {

        $success = true;
        if (count($dates) > 0) {
            $event_id = $event->getOwner() . microtime() . rand(0, 10);

            $sql = "INSERT INTO events (event_id, room_id, owner, title, description, mode) ";
            $sql .= "VALUES ";
            $sql .= "(";
            $sql .= "'" . $event_id . "'";
            $sql .= ", ";
            $sql .= "'" . $room->getId() . "'";
            $sql .= ", ";
            $sql .= "'" . $event->getOwner() . "'";
            $sql .= ", ";
            $sql .= "'" . $event->getTitle() . "'";
            $sql .= ", ";
            $sql .= "'" . $event->getDescription() . "'";
            $sql .= ", ";
            $sql .= "'" . $event->getMode() . "'";
            $sql .= ")";

            $sql_dates = "INSERT INTO event_dates (event_id, begin, end) ";
            $sql_dates .= "VALUES ";

            $count = 0;

            foreach ($dates as $date => $details) {
                $begin = $details["start"];
                $end = $details["end"];
                $sql_dates .= "(";
                $sql_dates .= "'$event_id'";
                $sql_dates .= ", ";
                $sql_dates .= "'$date $begin'";
                $sql_dates .= ", ";
                $sql_dates .= "'$date $end'";
                $sql_dates .= ") ";

                if ($count < count($dates) - 1) {
                    $sql_dates .= ", ";
                }
                $count++;
            }

            $db = new Db();
            $db->insert($sql, false);
            $db->insert($sql_dates);
        } else {
//aucune date spécifiée
            $success = false;
        }

        return $success;
    }

    public function delete($room, $event, $deleteOnlyCurrent = false, $deleteAfter = null, $deleteBefore = null) {
        $success = true;
        $connection = null;
        $query = null;
        $result = null;

        $sql = "DELETE FROM events WHERE event_id = '" . $event->getId() . "'";
        try {
            $db = new Db();
            $siblings = $this->getSiblings($event);
            $lastDate = $siblings[count($siblings) - 1]["date"];
            $isLastElement = $event->getDBegin() >= $lastDate && count($siblings) <= 1;

            if ($deleteOnlyCurrent && !$isLastElement) {
                $sql = "DELETE FROM event_dates WHERE event_date_id = '" . $event->getDateId() . "'";

                if ($deleteAfter != null || $deleteBefore != null) {
                    $sql = "DELETE FROM event_dates WHERE event_id = '" . $event->getId() . "'";

                    if ($deleteAfter != null) {
                        $sql .= " AND begin >='$deleteAfter'";
                    }
                    if ($deleteBefore != null) {
                        $sql .= " AND end <= '$deleteAfter";
                    }
                }
            }

            $success = $db->delete($sql);
        } catch (Exception $e) {
            ErrorHandler::Error($e);
        }


        return $success;
    }

    public function checkAvailability($room, $dates, $startDate = null, $endDate = null) {

        $verifiedEvents = array("available" => array(), "unavailable" => array());

        $maxEvents = $room->getMaxEvents();



        $currentEvents = array();

        foreach ($this->getEvents($room, $startDate, $endDate) as $e) {
            $id = $e->getId();
            $date = $e->getDBegin();
            $Hbegin = $e->getHBegin();
            $Hend = $e->getHEnd();


            $currentEvents[$date][] = array("start" => $Hbegin, "end" => $Hend, "id" => $id);
        }

        foreach ($dates as $date => $events) {

            foreach ($events as $e) {


                $isAvailable = true;

//s'il existe un événement pour cette date, on teste la disponibilité de la plage horaire
                if (isset($currentEvents[$date])) {

                    $isAvailable = $this->isAvailable($currentEvents[$date], $e, $maxEvents);
                }
                if ($isAvailable) {
                    $verifiedEvents["available"][$date] = $e;
                } else {
                    $verifiedEvents["unavailable"][$date] = $e;
                }
            }
        }

        return $verifiedEvents;
    }

    private function isAvailable($currentEvents, $newEvent, $maxEvents) {

        $nbEvents = 0;
        $canInsert = true;


        for ($i = 0; $i < count($currentEvents) && $canInsert; $i++) {
            $start = strtotime($currentEvents[$i]['start']);
            $end = strtotime($currentEvents[$i]['end']);
            $id = $currentEvents[$i]['id'];

            $e_start = strtotime($newEvent['start']);
            $e_end = strtotime($newEvent['end']);
            $e_id = $newEvent['id'];

            $canInsert = ($e_end <= $start || $e_start >= $end);


//if (!$canInsert && $id != $e_id) {
            if (!$canInsert) {
                $nbEvents++;
                $canInsert = $nbEvents < $maxEvents;
            }
        }


        return $canInsert;
    }

}

?>
