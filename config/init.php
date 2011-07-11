<?php
date_default_timezone_set('Europe/Berlin');
/**
 * Le fichier initialise tous les parametres importants de l'application
 * @author Stefan Meier
 *
 * Version 20100817
 *
 */

/********************************
 * Configuration système
 *******************************/
 /* SESSION-VARIABLES */

$_SESSION['APPLICATION_PATH'] = "/Volumes/FILES/smeier/Sites/calendar/";

$_SESSION['REMOTE_USER'] = null;
if (isset($_SERVER['HTTP_SHIB_PERSON_UID']) && isset($_SERVER['HTTP_SHIB_SWISSEP_HOMEORGANIZATION'])) {
    $_SESSION['REMOTE_USER'] = $_SERVER['HTTP_SHIB_PERSON_UID'] . "@" . $_SERVER['HTTP_SHIB_SWISSEP_HOMEORGANIZATION'];
}
else {
	$_SESSION['REMOTE_USER'] = "develop";
}

 /* DATABASE-CONFIG */
$_SESSION['DB_HOST'] = "localhost";
$_SESSION['DB_NAME'] = "calendar";
$_SESSION['DB_USER'] = "calendar";
$_SESSION['DB_PASSWORD'] = "cal4admin";

$_SESSION['DB_LOGGING'] = 1;


/* CONFIG CALENDAR */
//Nombre d'événéments affichés par jour (dans vue mensuelle)
$_SESSION['MAX_TITLES_DISPLAYED'] = 10;
//Nombre de caractères limite pour un titre d'événement
$_SESSION['TITLE_CHAR_LIMIT'] = 30;

?>