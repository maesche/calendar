<?php
require_once('application/GlobalRegistry.php');
require_once('application/LanguageLinker.php');

session_start();

$globalRegistry = $_SESSION["GlobalRegistry"];
$languageLinker = $globalRegistry->languageLinker;

$keys = array(
"calendar-message-confirm-repeat-update" => $languageLinker->resourceBundle->get("calendar-message-confirm-repeat-update"),
"calendar-message-confirm-repeat-delete" => $languageLinker->resourceBundle->get("calendar-message-confirm-repeat-delete"),
"calendar-event-delete" => $languageLinker->resourceBundle->get("calendar-event-delete"),
"calendar-event-save" => $languageLinker->resourceBundle->get("calendar-event-save"),
"calendar-event-cancel" => $languageLinker->resourceBundle->get("calendar-event-cancel")
)

?>
var resourceBundle = new Array();
<?php 
foreach ($keys as $key => $value) {
	echo "resourceBundle[\"$key\"] = \"$value\";\n";
}
?>