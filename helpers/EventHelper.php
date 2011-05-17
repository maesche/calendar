<?php

class EventHelper {

    private $event;

    function __construct($event = null) {
        $this->event = $event;
    }

    //public function updateEvent($id, $recurrence_id, $room_id, $uid, $title, $description, $date, $start_time, $end_time, $repeat_mode, $repeat_end) {
    public function update() {
        $success = $this->deleteEvent($id, $recurrence_id);
        $success = $this->addEvent($room_id, $uid, $title, $description, $date, $start_time, $end_time, $repeat_mode, $repeat_end);
        return $success;
    }

    // public function addEvent($room_id, $uid, $title, $description, $date, $start_time, $end_time, $repeat_mode, $repeat_end) {
    public function add() {
        $success = true;

        $sql_repeat = "";


        $sql = "INSERT INTO events (rooms_id, uid, edate, start_time, end_time, title, description, recurrence_id) ";
        $sql .= "VALUES ";

        if ($repeat_mode != "n") {

            $repeat_id = $uid . time();

            $i = 0;

            $d = new DateCalc($date, $repeat_end, $repeat_mode);

            $sql .= "";

            (array) $a = $d->repeatDate();

            foreach ($a as $v) {

                $sql .= "(";
                $sql .= "'" . $room_id . "'";
                $sql .= ", ";
                $sql .= "'" . $uid . "'";
                $sql .= ", ";
                $sql .= "'" . $v . "'";
                $sql .= ", ";
                $sql .= "'" . $start_time . "'";
                $sql .= ", ";
                $sql .= "'" . $end_time . "'";
                $sql .= ", ";
                $sql .= "'" . $title . "'";
                $sql .= ", ";
                $sql .= "'" . $description . "'";
                $sql .= ", ";
                $sql .= "'" . $repeat_id . "'";
                $sql .= ")";

                if ($i < (count($a) - 1)) {
                    $sql .= ", ";
                }
                $i++;
            }


            $sql_repeat = "INSERT INTO recurrences (id, mode, start, end) ";
            $sql_repeat .= "VALUES ('$repeat_id', '$repeat_mode', '$date', '$repeat_end')";
        } else {
            $sql .= "('$room_id',
            '$uid',
                '$date',
                '$start_time',
                '$end_time',
                '$title',
                '$description',
                    NULL)";
        }

        $connection = null;
        $query = null;
        $result = null;

        //echo $sql;
        try {
            $connection = Db::open();
            mysql_query("SET NAMES 'utf8'");

            if ($repeat_mode != "n") {
                mysql_query($sql_repeat);
            }
            mysql_query($sql);

            Db::close();
        } catch (Exception $e) {
            ErrorHandler::throwException($e);
            $success = false;
        }
        return $success;
    }

    //public static function deleteEvent($id, $recurrence_id) {
    public function delete() {
        $success = true;
        $connection = null;
        $query = null;
        $result = null;

        try {
            $connection = Db::open();
            mysql_query("SET NAMES 'utf8'");

            $sql = "DELETE FROM events WHERE id = $id";

            mysql_query($sql);


            if ($recurrence_id != "") {
                $sql = "DELETE FROM recurrences WHERE id = '$recurrence_id'";
                mysql_query($sql);
            }

            Db::close();
        } catch (Exception $e) {
            ErrorHandler::throwException($e);
            $success = false;
        }
        return $success;
    }

    /*public function getEventDataArray($room, $year, $month, $week) {
        $connection = null;
        $query = null;
        $result = null;
        $eventdata = null;

        for ($i = 0; $i <= 32; $i++) {
            $eventdata[$i] = null;
        }

        $m = (string) $this->month;

        if ($month < 10) {
            $month = "0" . $month;
        }

        try {
            $connection = Db::open();
            mysql_query("SET NAMES 'utf8'");
            $query = "SELECT id, title, start_time, end_time, description, " .
                    "DATE_FORMAT(edate, '%e') AS d,  " .
                    "TIME_FORMAT(start_time, '%H:%i') AS stime, " .
                    "TIME_FORMAT(end_time, '%H:%i') AS etime " .
                    "FROM events " .
                    "WHERE DATE_FORMAT(edate, '%m') = $month " .
                    "AND DATE_FORMAT(edate, '%Y') = $year " .
                    "AND rooms_id=$room " .
                    "ORDER BY start_time";

            $result = mysql_query($query);

            while ($return = mysql_fetch_assoc($result)) {
                $eventdata[$return["d"]]["id"][] = $return["id"];

                if (strlen($return["title"]) > $this->title_char_limit)
                    $eventdata[$return["d"]]["title"][] = substr(stripslashes($return["title"]), 0, $this->title_char_limit) . "...";
                else
                    $eventdata[$return["d"]]["title"][] = stripslashes($return["title"]);

                if (strlen($return["description"]) > $this->description_char_limit) {
                    $eventdata[$return["d"]]["description"][] = substr(stripslashes($return["description"]), 0, $this->title_char_limit) . "...";
                } else {
                    $eventdata[$return["d"]]["description"][] = stripslashes($return["description"]);
                }

                if (!($return["start_time"] == "55:55:55" && $return["end_time"] == "55:55:55")) {
                    if ($return["start_time"] == "55:55:55")
                        $starttime = "- -";
                    else
                        $starttime = $return["stime"];

                    if ($return["end_time"] == "55:55:55")
                        $endtime = "- -";
                    else
                        $endtime = $return["etime"];

                    $timestr = "$starttime - $endtime";
                } else {
                    $timestr = "<br>";
                }

                $eventdata[$return["d"]]["timestr"][] = $timestr;
            }

            mysql_free_result($result);
            Db::close();
        } catch (Exception $e) {
            ErrorHandler::throwException($e);
        }

        return $eventdata;
    }

*/
    //public static function timeAvailable($currentEvents, $newEvents, $maxEvents) {
    public function isAvailable() {

        $nbEvents = 1;
        $canInsert = true;

        $currentEvents = array(array('start' => '11:30:00', 'end' => '12:00:00'),
            array('start' => '12:00:00', 'end' => '13:00:00'));
        $newEvents = array(array('start' => '13:00:00', 'end' => '13:30:00'));

        for ($i = 0; $i < count($currentEvents) && $canInsert; $i++) {
            $start = strtotime($currentEvents[$i]['start']);
            $end = strtotime($currentEvents[$i]['end']);

            for ($j = 0; $j < count($newEvents) && $canInsert; $j++) {
                $e_start = strtotime($event[$j]['start']);
                $e_end = strtotime($event[$j]['end']);

                $canInsert = $e_end <= $start || $e_start >= $end;

                if (!$canInsert) {
                    $canInsert = $nbEvents < $maxEvents;
                    $nbEvents++;
                }
            }
        }

        return $canInsert;
    }

}

?>
