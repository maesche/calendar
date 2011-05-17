<?php
include_once("model/class/Building.php");
include_once("model/BuildingHandler.php");
include_once("model/RoomHandler.php");
session_start();

$currentBuilding = null;
$currentRoom = null;
$rooms = null;

if (isset($_SESSION['CURRENT_BUILDING'])) {
    $currentBuilding = $_SESSION['CURRENT_BUILDING'];
    $roomHandler = new RoomHandler();
    $rooms = $roomHandler->getRooms($currentBuilding);
    if (isset($_SESSION['CURRENT_ROOM'])) {
        $currentRoom = $_SESSION['CURRENT_ROOM'];
    } else {

    }
} else {
    $buildingId = 27;

    if (isset($_GET['id']) && $_GET['id'] != 'undefined') {
        $buildingId = (int) $_GET['id'];
    }
    $buildingHandler = new BuildingHandler();
    $buildings = $buildingHandler->getBuildings();
    $currentBuilding = new Building($buildingId);
    $_SESSION['CURRENT_BUILDING'] = $currentBuilding;
}

$currentBuilding = null;
$currentRoom = null;

if (isset($_SESSION['CURRENT_BUILDING'])) {
    $currentBuilding = $_SESSION['CURRENT_BUILDING'];
}

if (isset($_SESSION['CURRENT_ROOM'])) {
    $currentRoom = $_SESSION['CURRENT_ROOM'];
}


$roomHandler = new RoomHandler();
$rooms = $roomHandler->getRooms($currentBuilding);
?>
<script type="text/javascript">
    $(document).ready(function() {

        $("#year, #month, #room, #buildings").change(function() {
            year = $("#year").val();
            month = $("#month").val();

            room = $("#room").val();
            building = $("#buildings").val();

            $.cookie("year", year);
            $.cookie("month", month);
            $.cookie("building", building);
            //buildings();

            if(room != 'default') {
                $.cookie("room", room);

                calendar();
            }

        });

        $("#buildings").change(function() {
            buildings();
        });
        /*$("#buildings, #room").change(function() {
            month = new Date().getMonth() + 1;
            year = new Date().getFullYear();
            $.cookie("year", year);
            $.cookie("month", month);
        });*/


    });
</script>
<form name="roomChanger" id="roomChanger" action="">
<?php
echo "<select name=\"room\" class=\"query_style\" id=\"room\">\n";
echo "<option value=\"default\">---------------------------</option>";
foreach ($rooms as $r) {

    echo "<option value=\"{$r->getId()} \">{$r->getLocal()} - {$r->getName()} - {$r->getCategory()}</option>";
}

echo "</select>\n";
?>
</form>
