<?php

/**
 *  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|0.1.9.2|___/ |_/_/ \_\_| |___/
 *
 * Author:     Roberto Valsania - Webmaster76
 *
 * Staff:      Matrix - Massimiliano Coppola
 *             Viewsource
 *             PaoDJ - Paolo Antonio Tremadio
 *             Fabry - Fabrizio Tomasoni
 *             theCAS - Carlo Alberto Siti
 *
 * Version:    0.1.9.2
 *
 * Site:       http://php-stats.com/
 *             http://phpstats.net/
 *
 **/

// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

/////////////////////////////////////////////
// Preparazione varibili HTML del template //
/////////////////////////////////////////////
$option['nomesito']=stripcslashes($option['nomesito']);
$meta='<META NAME="ROBOTS" CONTENT="NONE">';
$phpstats_title="Php-Stats - $phpstats_title";


//////////////////////////////////
// Generazione HTML da template //
//////////////////////////////////
eval("\$template=\"".gettemplate("$template_path/details.tpl")."\";");


?>