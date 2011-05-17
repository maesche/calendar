<?php

include_once("model/class/Db.php");
include_once("model/class/Building.php");

class BuildingHandler {

    public function getBuildings($id = null) {
        $db = new Db();
        $buildings = array();

        $sql_select = "SELECT
                    building_id AS id,
                    name
                FROM buildings";
        $sql_orderby = " ORDER BY name";

        $sql = $sql_select . $sql_orderby;

        $return = $db->select($sql);

        foreach ($return as $ret) {
            $building = new Building($ret["id"], $ret["name"]);
            $buildings[] = $building;
        }

        return $buildings;
    }

}

?>
