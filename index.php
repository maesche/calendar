<?php
 error_reporting(E_ALL); 
 ini_set("display_errors", 1); 

/**
 * Contrôleur de l'application
 * @author Stefan Meier
 *
 * Version 20100817
 *
 */
date_default_timezone_set('Europe/Zurich');
session_start();
/* Toutes les réponses sont envoyées en UTF-8 */
header('Content-type: text/html; charset=UTF-8');

/* Si la session n'a pas encore été configurée, config/init.php sera chargé */

include 'config/init.php';


require("views/main.php");
?>