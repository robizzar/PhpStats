<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */

// SECURITY ISSUES
define('IN_PHPSTATS', true);

// Inizializzazione delle variabili
           $short_url=1;  // Mostra url corti quando possibile - Show short url when it's possible
         $is_loged_in=0;  // Non loggato - Not logged in
             $refresh=0;  // Nessun refresh della pagina se non diversamente specificato - No refresh page if not specified
          $update_msg=0;  // Nessun update disponibile - No update avaible
$error['debug_level']=0;  // Debug si attiva da solo in caso di errore - Debug activated only in case of error
              $style='';  // In caso di register globals=on - For register globals=on
          $cache_recn=0;  // Flag riconoscimento cache - Flag for recognition cache
$php_stats_esclusion='';  // Esclusione vuota fino a prova contraria - Empty exclusion until contrary test
      $protect_action=Array('login','esclusioni','preferenze','refresh','backup','resett','downadmin','clicksadmin','optimize_tables','viewerrorlog','modify_config'); // Azioni che richiedono il login
    $norefresh_action=Array('login','logout','esclusioni','preferenze','refresh','backup','resett','downadmin','clicksadmin','optimize_tables','viewerrorlog','modify_config'); // Azioni che non hanno refresh in visualizzazione
      $cache_recn_arr=Array('main','os_browser','reso','systems','daily','weekly','monthly','calendar','compare','country','bw_lang','trend','ip');  // Azioni che usano la cache
           $page_list=Array('main','details','os_browser','reso','systems','pages','percorsi','time_pages','referer','engines','query','searched_words','hourly','daily','weekly','monthly','calendar','compare','ip','country','bw_lang','links','downloads','clicks','trend');


$GLOBALS['totalqueries']=0; // Contatore Query - Counter Queries


// Richiamo variabili esterne - Call external vars
                         if(!isset($_GET)) $_GET=$HTTP_GET_VARS;
                      if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
                      if(!isset($_SERVER)) $_SERVER=$HTTP_SERVER_VARS;

                if(isset($_GET['action'])) $action=$_GET['action']; else $action='main';
               if(isset($_GET['opzioni'])) $opzioni=$_GET['opzioni']; else $opzioni='';

if(isset($_COOKIE['php_stats_esclusion'])) $php_stats_esclusion=$_COOKIE['php_stats_esclusion'];
    if(isset($_COOKIE['php_stats_cache'])) $php_stats_cache=$_COOKIE['php_stats_cache']; else $php_stats_cache=0;

               if(isset($_POST['option'])) $tmpOption=$_POST['option'];
           if(isset($_POST['option_new'])) $option_new=$_POST['option_new'];

       if(isset($_SERVER['QUERY_STRING'])) $QUERY_STRING=trim(addslashes($_SERVER['QUERY_STRING'])); else $QUERY_STRING='';
           if(isset($_SERVER['PHP_SELF'])) $PHP_SELF=addslashes($_SERVER['PHP_SELF']); else $PHP_SELF='admin.php';

if (!get_magic_quotes_gpc()) {
	$_POST 		= addslashes_deep($_POST);
	$_GET 		= addslashes_deep($_GET);
	$_COOKIE 	= addslashes_deep($_COOKIE);
	$_REQUEST 	= addslashes_deep($_REQUEST);
}

function addslashes_deep($value)
{
    $value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    return $value;
}

if (!include('option/php-stats-options.php'))
    die("<b>ERRORE</b>: File di config non accessibile.");
if (!include('inc/main_func.inc.php'))
    die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if (!include('inc/admin_func.inc.php'))
    die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');
if (!include('inc/user_func.inc.php'))
    die('<b>ERRORE</b>: File user_func.inc.php non accessibile.');

// Connessione a MySQL e selezione database
db_connect();


if (isset($_POST['option'])) {
    foreach ($tmpOption as $key => $value) {
        switch ($key) {
            case 'admin_pass':
                $option[$key] = '';
                break;
            case 'language':
            case 'template':
            case 'prefix':
                $option[$key] = preg_replace('@\W@', '', $value);
                break;
            default:
                $option[$key] = $value;
        }
    }
}
if ($option['prefix'] == '')
    $option['prefix'] = 'php_stats';

//$result=sql_query("SELECT name,value FROM $option[prefix]_config WHERE name LIKE 'inadm_%'".($NowritableServer===0 ? " OR name LIKE 'instat_%'" : ''));
$result       = sql_query("SELECT name,value FROM $option[prefix]_config WHERE name LIKE 'inadm_%' OR name LIKE 'instat_%'");
$number_value = 5; //($NowritableServer===0 ? 5 : 3);
if (mysql_num_rows($result) != $number_value)
    die("<b>ERRORE</b>: Anomalia nella tabella $option[prefix]_config, dati di configurazione in numero non corretto.");
while ($row = mysql_fetch_row($result))
    $option[$row[0]] = $row[1];

if ($option['template'] == '')
    $option['template'] = 'default';
$template_path = 'templates/' . $option['template'];

/////////////////////////////////
// PULIZIA CACHE - CLEAN CACHE //
/////////////////////////////////
if (!$option['clear_cache']) { // Controllo se non si è forzato il riconoscimento continuo - Check for continuous forced recognition
    if (($php_stats_cache != '1') || (time() > ($option['inadm_lastcache_time'] + 1200))) {
        if (in_array($action, $cache_recn_arr))
            $cache_recn = 1;
        else
            $cache_recn = 0;
    }
}

// PULITURA DELLA CACHE SE LE STATISTICHE SONO PUBBLICHE O SONO AMMINISTRATORE - CLEAN CACHE IF THE STATS ARE PUBLIC OR I'M THE ADMINISTRATOR
if ($cache_recn == 1 && (user_is_logged_in() || (!$option['use_pass'] && in_array($action, $unlockedPages)))) {
    if (isset($_GET['do']))
        $do = $_GET['do'];
    else
        $do = 0;
    if ($do == 0) {
        // VISUALIZZO MESSAGGIO DI ATTESA - SHOW WAIT MESSAGE
        $url = $option['script_url'] . '/admin.php?do=1';
        if ($QUERY_STRING != '')
            $url .= '&redirect=' . $QUERY_STRING;
        if (!include("lang/$option[language]/cache_refr_lang.inc"))
            die("<b>ERRORE</b>: File $option[language]/cache_refr_lang.inc non accessibile.");
        $message = "<span class=\"testo\">$message1</span>";
    } else {
        // VISUALIZZO MESSAGGIO DI AVVENUTO RICONOSCIMENTO - SHOW CACHE RECOGNIZED MESSAGE
        setcookie('php_stats_cache', '1'); // Scade alla chiusura del browser - It expires at browser closing
        clear_cache();
        mysql_query("UNLOCK TABLES"); //NON NECESSARIO MA E' PER SICUREZZA - NOT NECESSARY BUT IT'S FOR SECURITY
        mysql_query("UPDATE $option[prefix]_config SET value='" . time() . "' WHERE name='inadm_lastcache_time'");
        if (isset($_GET['redirect']))
            $redirect = 'admin.php?' . $_GET['redirect'];
        else
            $redirect = 'admin.php';
        $url = $redirect;
        if (!include("lang/$option[language]/cache_refr_lang.inc"))
            die("<b>ERRORE</b>: File $option[language]/cache_refr_lang.inc non accessibile.");
        $message = "<span class=\"testo\">$message2</span>";
    }
    $template = "$template_path/cache_refresh.tpl";
    $template = implode('', file($template));
    $template = str_replace(Array(
        '%URL%',
        '%MESSAGE%'
    ), Array(
        $url,
        $message
    ), $template);
    // FINE RICONOSCIMENTO CACHE - END RECOGNITION CACHE
} else {
    
    ////////////////////////////////////////////
    // PAGINA DI AMMINISTRAZIONE - ADMIN PAGE //
    ////////////////////////////////////////////
    
    // Controllo password
    $is_loged_in = user_is_logged_in();
    
    if ($action == 'enter') {
        if (user_login()) {
            header('Location: admin.php?action=main');
        } else {
            $action = 'wrong_pass';
        }
    }
    
    if ($action == 'logout' || $action == 'login') {
        if ($action == 'logout') {
            $is_loged_in = user_logout();
        }
    }
    
    if ($option['use_pass'] && !in_array($action, $unlockedPages) AND !$is_loged_in AND $action != 'wrong_pass' AND $action != 'send_password') {
        $action = 'login';
    }
    
    // Controllo se l'azione richiede il login
    if (!$is_loged_in && in_array($action, $protect_action)) {
        $action = 'login';
    }
    
    if ($action == 'esclusioni' && $opzioni == 'change') {
        $php_stats_esclusion = str_replace("|$option[script_url]|", '', $php_stats_esclusion); // RIMUOVO PREVENTIVAMENTE L'URL IN OGNI CASO PER EVITARE DUPLICAZIONI - I PREVENTIVELY REMOVE THE URL TO AVOID DUPLICATIONS
        $php_stats_esclusion .= ($option_new == 1 ? "|$option[script_url]|" : '');
        setcookie('php_stats_esclusion', $php_stats_esclusion, (time() + 311040000), '/');
    }
    
    // ELABORAZIONE DATI NELLA CACHE -  CACHE DATA PROCESSING
    if (in_array($action, $cache_recn_arr)) {
        if ($option['clear_cache']) {
            clear_cache();
            $clear_tip = 0;
        } else
            $clear_tip = 1;
    } else
        $clear_tip = 0;
    
    // Memorizzo l'action per usi futuri - Save action
    $trad_action = $action;
    
    // Inclusioni secondarie: template della pagina e language pack. - Template and langauge pack inclusion
    if (!include("lang/$option[language]/main_lang.inc"))
        die("<b>ERRORE</b>: File $option[language]/main_lang.inc non accessibile."); // Language file
    $error['debug_level'] = 0; // Debug si attiva da solo in caso di errore - Debug activated only in case of error
    if (!include($template_path . '/def.php'))
        die("<b>ERRORE</b>: File $template_path/def.php non accessibile."); // Template defs
    if (!include("inc/$action.inc.php")) {
        $body           = "<img src=\"templates/$option[template]/images/icon_warning.gif\" align=\"middle\"><span class=\"tabletextB\">&nbsp;$error[critical_err]</span>";
        $action         = info_box("<b>$string[error]</b>", $body);
        $phpstats_title = $string['error_title'];
    } else
        $action = $action();
    
    // Visualizzo suggerimenti se necessario - Show tips if necessary
    if ($clear_tip == 1) {
        $cache_clear = sql_query("SELECT hits FROM $option[prefix]_cache WHERE hits>0");
        $num_cache   = mysql_num_rows($cache_clear);
        if ($num_cache > 0) {
            $tips   = "<br>\n<script>" . "\nfunction clearcache(url) {" . "\n\tclearcache=window.open(url,'clearcache','SCROLLBARS=0,STATUS=NO,TOOLBAR=NO,RESIZABLE=NO,LOCATION=NO,MENU=NO,WIDTH=250,HEIGHT=100,LEFT=0,TOP=0');" . "\n\t}" . "\n</script>" . "\n<table $style[table_header] width=\"95%\">" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "<tr bgcolor=\"$style[table_tips_bgcolor]\"><td width=\"95%\" bgcolor=\"$style[table_tips_bgcolor]\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_tips.gif\" align=\"absmiddle\" border=\"0\">" . str_replace('%NUMCACHE%', $num_cache, ($num_cache == 1 ? $string['tips_cache_refresh_1'] : $string['tips_cache_refresh_2'])) . "</span></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "</table>";
            $action = $action . $tips;
        }
    }
    
    // DEBUG MODE in caso di errori MySQL - DEBUG Mode in case MYSQL's errors
    if ($error['debug_level'])
        $action = info_box('<b>PHP-STATS AUTO DEBUG MODE</b>', $error['debug_level_error']);
    
    // Visualizzo il login se non si è loggati o il logout se lo si è. - Show login if not logged in or logout if logged in
    if ($is_loged_in) {
        $admin_menu['status']     = $admin_menu['logout'];
        $admin_menu['status_rev'] = 'logout';
    } else {
        $admin_menu['status']     = $admin_menu['login'];
        $admin_menu['status_rev'] = 'login';
    }
    
    if ($option['check_new_version']) {
        if ($option['inadm_upd_available']) {
            $tips   = "<br>\n<table $style[table_header] width=\"95%\">" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "<tr bgcolor=\"$style[table_tips_bgcolor]\"><td width=\"95%\" bgcolor=\"$style[table_tips_bgcolor]\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_tips.gif\" align=\"absmiddle\" border=\"0\">$string[tips_update_availb]</span></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "</table>";
            $action = $action . $tips;
        }
    }
    
    /*** Controllo validità dei file di browscap */
    if (filesize('browscap/browscap.ini') < 50000) {
        unlink('browscap/browscap.ini');
        unlink('browscap/cache.php');
        copy('browscap/browscap_bk.ini', 'browscap/browscap.ini');
        
        $tips   = "<br>\n<table $style[table_header] width=\"95%\">" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "<tr bgcolor=\"$style[table_tips_bgcolor]\"><td width=\"95%\" bgcolor=\"$style[table_tips_bgcolor]\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_tips.gif\" align=\"absmiddle\" border=\"0\">$string[tips_bcap_ini]</span></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "</table>";
        $action = $action . $tips;
    } else if (file_exists('browscap/cache.php') && filesize('browscap/cache.php') < 50000) {
        unlink('browscap/cache.php');
        
        $tips   = "<br>\n<table $style[table_header] width=\"95%\">" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "<tr bgcolor=\"$style[table_tips_bgcolor]\"><td width=\"95%\" bgcolor=\"$style[table_tips_bgcolor]\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_tips.gif\" align=\"absmiddle\" border=\"0\">$string[tips_bcap_cache]</span></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>" . "</table>";
        $action = $action . $tips;
    }
    /*** */
    
    // Fine delle elaborazioni primarie.
    $end_time = get_time();
    
    // Inclusione template-script esterno - Template inclusion
    include($template_path . '/template.php');
} // della scelta cache/admin

// Restituisco la pagina - Show page
echo $template;

// Chiusura connessione a MySQL se necessario - Close MYSQL connection if necessary.
if ($option['persistent_conn'] != 1)
    mysql_close();
?>

