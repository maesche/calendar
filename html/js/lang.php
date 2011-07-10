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
"calendar-event-cancel" => $languageLinker->resourceBundle->get("calendar-event-cancel"),
"application-title" => $languageLinker->resourceBundle->get("application-title"),
"calendar-information" => $languageLinker->resourceBundle->get("calendar-information"),
"calendar-choice" => $languageLinker->resourceBundle->get("calendar-choice"),
"calendar-goTo" => $languageLinker->resourceBundle->get("calendar-goTo")
)

?>

<?php 
foreach ($keys as $key => $value) {
	echo "resourceBundle[\"$key\"] = \"$value\";\n";
}

for($i = 1; $i <= 12; $i++) {
	echo "resourceBundle[\"month-$i-full\"] = \"{$languageLinker->resourceBundle->get("month-$i-full")}\";\n";
	echo "resourceBundle[\"month-$i-short\"] = \"{$languageLinker->resourceBundle->get("month-$i-short")}\";\n";
}

for ($i = 1; $i<= 7; $i++) {
	echo "resourceBundle[\"day-$i-full\"] = \"{$languageLinker->resourceBundle->get("day-$i-full")}\";\n";
	echo "resourceBundle[\"day-$i-short\"] = \"{$languageLinker->resourceBundle->get("day-$i-short")}\";\n";
}
?>