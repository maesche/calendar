<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/Berlin');
include_once("helpers/System.php");
include_once("helpers/FormValidator.php");
include_once("model/class/Event.php");
include_once("model/EventHandler.php");
include_once("model/class/DateCalc.php");
session_start();

//include_once("model/EventLogger.php");

$success = false;
$return = array("success" => true);

$auth = System::authLevel();

$salle = 0;



/*
 * To do
 *
 * classe contrôleur
 * vérification modifyall sans passer par php
 * ne pas soumettre plus qu'une fois
 */

try {
    if ($auth > 0) {
        if (isset($_SESSION['CURRENT_ROOM'])) {
            $room = $_SESSION['CURRENT_ROOM'];
            $action = $_POST['action'];
            $currentUser = $_SESSION['REMOTE_USER'];

            $uid = $_POST['uid'];
            $name = $_POST['name'];
            $description = $_POST['description'];

            $edate = $_POST['edate'];
            $start_time = $_POST['start_hour'] . ":" . $_POST['start_min'] . ":00";
            $end_time = $_POST['end_hour'] . ":" . $_POST['end_min'] . ":00";
            $repeat = $_POST['repeat'];
            $repeat_end = $_POST['repeat_end'];
            $wholeDay = false;
            if (isset($_POST['whole_day'])) {
                $wholeDay = true;
            }
            $modifyAll = $_POST['modify-all'];


            if ($modifyAll == 'true') {
                $modifyAll = true;
            } else if ($modifyAll == 'false') {
                $modifyAll = false;
                $test = true;
            } else {
                $modifyAll = null;

            }


            if ($action == "delete") {
                if (isset($_POST['original_event_id'])) {
                    try {
                        $id = $_POST['original_event_id'];
                        $date_id = $_POST['original_date_id'];
                        $event = new Event($id, null, null, null, null, null, null, null, null, $date_id, null);
                        $eventHandler = new EventHandler();
                        if ($repeat == 'n') {
                            $eventHandler->delete($room, $event);
                        } else {
                            if (is_bool($modifyAll) === true) {
                                if ($modifyAll) {

                                    $eventHandler->delete($room, $event);
                                } else {
                                     echo "hallo";
                                    $eventHandler->delete($room, $event, true);
                                }
                            } else {

                                $return["success"] = false;
                                $return["modifyall"] = "modifyall";
                            }
                        }
                    } catch (Exception $e) {
                        $return["success"] = false;
                        $return["system"] = $e->getMessage();
                    }
                } else {
                    $return["success"] = false;
                }
            } else {
                if (FormValidator::date($edate)) {
                    if (FormValidator::text($name, true)) {
                        if ($uid == $currentUser) {
                            if (strtotime($end_time) > strtotime($start_time) || $wholeDay == true) {

                                $calculateDates = false;
                                //if we make changes to dates, we have to check the availability in an non concurrent environment.
                                $insertable = true;
                                $needExclusiveLock = false;

                                $tmp_name = $name;
                                $tmp_date = $edate;
                                $tmp_description = $description;
                                $tmp_start_time = $start_time;
                                $tmp_end_time = $end_time;
                                $tmp_repeat = $repeat;
                                $tmp_date_id = null;
                                $tmp_event_id = null;


                                if ($action == "edit") {
                                    $tmp_name = null;
                                    $tmp_date = null;
                                    $tmp_description = null;
                                    $tmp_start_time = null;
                                    $tmp_end_time = null;
                                    $tmp_repeat = null;
                                    $tmp_date_id = $_POST['original_date_id'];
                                    $tmp_event_id = $_POST['original_event_id'];

                                    $original_name = $_POST['original_name'];
                                    $original_date = $_POST['original_date'];
                                    $original_whole_day = $_POST['original_whole_day'];
                                    $original_start_time = $_POST['original_start_time'];
                                    $original_end_time = $_POST['original_end_time'];
                                    $original_repeat_mode = $_POST['original_repeat_mode'];
                                    $original_repeat_end = $_POST['original_repeat_end'];
                                    $original_description = $_POST['original_description'];
                                    $original_date_id = $_POST['original_date_id'];
                                    $original_event_id = $_POST['original_event_id'];

                                    if ($original_name != $name) {
                                        $tmp_name = $name;
                                    }
                                    if ($original_date != $edate) {
                                        $tmp_date = $edate;
                                        $tmp_date_id = $original_date_id;
                                    }
                                    if ($original_description != $description) {
                                        $tmp_description = $description;
                                    }
                                    if ($original_repeat_end != $repeat_end ||
                                            $original_repeat_mode != $repeat ||
                                            $original_start_time != $start_time ||
                                            $original_end_time != $end_time) {
                                        $calculateDates = true;
                                        $tmp_name = $name;
                                        $tmp_date = $edate;
                                        $tmp_description = $description;
                                        $tmp_repeat = $repeat;
                                    }

                                    $tmp_event_id = $original_event_id;
                                } else if ($action == "add") {
                                    $calculateDates = true;
                                    $tmp_name = $name;
                                    $tmp_date = $edate;
                                    $tmp_description = $description;
                                    $tmp_repeat = $repeat;
                                } else {
                                    $insertable = false;
                                    $return["success"] = false;
                                    $return["action"] = "action";
                                }
                                $events = null;


                                /*
                                 * For performance raisons, the date calculation and the availability check are separated
                                 */

                                $dates = null;
                                if ($insertable) {

                                    //calculate dates only if there was no error before and
                                    //if it is really necessary
                                    if ($calculateDates) {
                                        $needExclusiveLock = true;
                                        if ($repeat == "n") {
                                            $events[$edate][] = array("start" => $start_time, "end" => $end_time);
                                        } else if ($repeat == "a") {

                                        } else {
                                            $dateCalc = new DateCalc($edate, $repeat_end, $repeat);
                                            $dates = $dateCalc->repeatDate();
                                            foreach ($dates as $date) {
                                                $events[$date][] = array("start" => $start_time, "end" => $end_time);
                                            }
                                        }
                                    }

                                    $eventHandler = new EventHandler();
                                    $event = new Event($tmp_event_id, $currentUser, $tmp_name, $tmp_description, $tmp_date, $tmp_start_time, $tmp_date, $tmp_end_time, $tmp_repeat, $tmp_date_id, null);



                                    if ($needExclusiveLock) {
                                        /*
                                         * BEGIN CRITICAL PART
                                         *
                                         * In this part, only one thread can be handled at once. All other
                                         * threads will wait.
                                         */
                                        $file_handle = fopen('calendar' . $room->getId(). '.lock', 'w+');
                                        $finish = false;
                                        while (!$finish) {
                                            if (flock($file_handle, LOCK_EX)) {
                                                $verifiedEvents = $eventHandler->checkAvailability($room, $events);
                                                if (count($verifiedEvents["unavailable"]) > 0 && !isset($_GET['insert-available'])) {
                                                    $return["success"] = false;
                                                    $return["unavailable"] = $verifiedEvents["unavailable"];
                                                    $insertable = false;
                                                } else {
                                                    $dates = $verifiedEvents["available"];
                                                }
                                                if ($insertable) {
                                                    if ($action == "add") {
                                                        $eventHandler->add($room, $event, $dates);
                                                    } else {
                                                        if ($repeat == 'n') {
                                                            $eventHandler->delete($room, $event);
                                                            $eventHandler->add($room, $event, $dates);
                                                        } else {
                                                            if (is_bool($modifyAll) === true) {
                                                                if ($modifyAll) {
                                                                    $eventHandler->delete($room, $event);
                                                                    $eventHandler->add($room, $event, $dates);
                                                                } else {
                                                                    $eventHandler->delete($room, $event, true);
                                                                    $eventHandler->add($room, $event, $dates);
                                                                }
                                                            } else {
                                                                $return["success"] = false;
                                                                $return["modifyall"] = "modifyall";
                                                            }
                                                        }
                                                    }
                                                }

                                                $finish = true;
                                            }
                                        }
                                        fclose($file_handle);
                                        /*
                                         * END CRITICAL PART
                                         */
                                    } else {
                                        //the dates are not updated, we don't need any semaphore for this modification
                                        if ($repeat == 'n') {
                                            $eventHandler->delete($room, $event, true);
                                            $eventHandler->add($room, $event, $dates);
                                        } else {
                                            if (is_bool($modifyAll) === true) {
                                                if ($modifyAll) {
                                                    $eventHandler->update($room, $event);
                                                } else {
                                                    $eventHandler->delete($room, $event, true);
                                                    $eventHandler->add($room, $event, $dates);
                                                }
                                            } else {
                                                $return["success"] = false;
                                                $return["modifyall"] = "modifyall";
                                            }
                                        }
                                    }
                                }
                            } else {
                                $return["success"] = false;
                                $return["time"] = "time";
                            }
                        } else {
                            $return["success"] = false;
                            $return["auth"] = "user!=uid";
                        }
                    } else {
                        $return["success"] = false;
                        $return["eventname"] = "eventname";
                    }
                } else {
                    $return["success"] = false;
                    $return["dateformat"] = "dateformat";
                }
            }
        } else {
            $return["success"] = false;
            $return["room"] = "room";
        }
    } else {
        $return["success"] = false;
        $return["aut"] = "auth";
    }
} catch (Exception $e) {
    $return["success"] = false;
    $return["system"] = $e->getMessage();
}

echo json_encode($return);
?>