<?php
/*
$modulo[n]

0		=>'Dettagli',
1		=>'Info sul client (Browser,OS,Risoluzione,Colori)',
1_m		=>'Attiva suddivisione mensile',
2		=>'Lingua del browser',
3		=>'Pagine visitate, tempo di permanenza e utenti on-line',
3_m		=>'Memorizza il record massimo degli utenti on-line',
4		=>'Referer e Motori di ricerca',
4_m		=>'Attiva suddivisione mensile',
5		=>'Visite orarie',
5_m		=>'Attiva suddivisione mensile',
6		=>'Visite giornaliere/mensili',
7		=>'Paesi di provenienza',
8		=>'Downloads',
9		=>'Clicks',
10		=>'IP',
11		=>'Attiva statistiche Motori di ricerca',
*/

// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

      if(isset($_POST['whatview'])) $whatview=$_POST['whatview']; else $whatview='';
       if(isset($_POST['scstyle'])) $scstyle=$_POST['scstyle']; else $scstyle='';
      if(isset($_POST['scdigits'])) $scdigits=$_POST['scdigits']; else $scdigits='';
       if(isset($_GET['newstyle'])) $newstyle=addslashes($_GET['newstyle']); else $newstyle='';

function preferenze(){
global $db,$is_loged_in,$opzioni,$error,$style,$modulo,$string,$pref,$varie,$option,$option_new,$php_stats_esclusion,$whatview,$newstyle,$scstyle,$scdigits,$phpstats_title;
$return='';
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$pref['opzioni'];
//
if($opzioni=='manualsendreport'){
        include('inc/report.inc.php');
        $ok=report(TRUE);

        if($ok){
                $body="$pref[reportsent]<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['information'],$body);
        }
        else{
                $body="$pref[sendreporterror]<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['error'],$body);
        }
}
else if($opzioni=='applica'){
        // MOTIVI DI SICUREZZA
        foreach($option_new as $key => $value) {
        	if (array_key_exists($key, $option) || in_array($key, array('pass_confirm'))) {
	        	$option_new_2[$key]=addslashes($value);
	        }
        }
        $ok=FALSE;
        $return.="<br><center>";
        do{
                if($option_new_2['admin_pass']!='' && $option_new_2['pass_confirm']!='' && $option_new_2['admin_pass']!=$option_new_2['pass_confirm']) { $errore=$error['pref_01']; break;}
                if($option_new_2['admin_pass']=='') $option_new_2['admin_pass']=false;
                if(checktext($option_new_2['prune_0_value']))  { $errore=$error['pref_02']; break; }
                if(checktext($option_new_2['prune_1_value']))  { $errore=$error['pref_03']; break; }
                if(checktext($option_new_2['prune_2_value']))  { $errore=$error['pref_03']; break; }
                if(checktext($option_new_2['prune_3_value']))  { $errore=$error['pref_03']; break; }
                if(checktext($option_new_2['prune_4_value']))  { $errore=$error['pref_03']; break; }
                if(checktext($option_new_2['prune_5_value']))  { $errore=$error['pref_03']; break; }
                if(checktext($option_new_2['auto_opt_every'])) { $errore=$error['pref_07']; break; }
                if(checktext($option_new_2['starthits']))      { $errore=$error['pref_04']; break; }
                if(checktext($option_new_2['startvisits']))    { $errore=$error['pref_05']; break; }
                if(checktext($option_new_2['ip_timeout']))     { $errore=$error['pref_06']; break; }
                if(checktext($option_new_2['page_timeout']))   { $errore=$error['pref_06']; break; }
                $ok=TRUE;
        }while(false);
        if($ok){
                // Controllo la lista degli indirizzi email elimino le righe vuote.
                $tmpUserMail=explode("\n",$option_new_2['user_mail']);
                unset($tmpArray);
                for($i=0,$tot=count($tmpUserMail);$i<$tot;++$i){
                        $tmp=trim($tmpUserMail[$i]);
                        if($tmp==='') continue;
                        $tmpArray[]=$tmp;
                }
                $option_new_2['user_mail']=implode("\n",$tmpArray);
                unset($tmpArray,$tmpUserMail);
            // Controllo la lista degli url elimino / e le righe vuote.
                $tmpServerUrl=explode("\n",$option_new_2['server_url']);
                unset($tmpArray);
                for($i=0,$tot=count($tmpServerUrl);$i<$tot;++$i){
                        $tmp=trim($tmpServerUrl[$i]);
                        if($tmp==='') continue;
                        if(substr($tmp,-1)==='/') $tmp=substr($tmp,0,-1);
                        $tmpArray[]=$tmp;
                }
                $option_new_2['server_url']=implode("\n",$tmpArray);
                unset($tmpArray,$tmpServerUrl);

                // Calcolo la stringa di identificazione dei moduli
                $option_new_2['moduli']='';
                for($i=0;$i<11;++$i){
                        $x="moduli_$i";
                        $y="moduli_m_$i";
                        if(!isset($option[$y])) $option[$y]=0;
                        if(isset($option[$x]) && $option[$x]==1) $value=($option[$y]==1 ? 2 : 1);
                        else $value=0;
                        $option_new_2['moduli'].=$value."|";
                }
                for($i=0;$i<6;++$i) $option_new_2['prune_'.$i.'_on']=(isset($option_new_2['prune_'.$i.'_on']) && $option_new_2['prune_'.$i.'_on']==1 ? 1 : 0);

                if(!isset($option_new_2['report_w_on'])) $option_new_2['report_w_on']=0;
                if(!isset($option_new_2['auto_optimize'])) $option_new_2['auto_optimize']=0;

                // Limito l'ip timeout
/***
PERMETTO DI INSERIRE IL VALORE 0 UTILE PER MOTIVI DI DEBUG
                if($option_new_2['ip_timeout']<1) $option_new_2['ip_timeout']=1;
***/
                if($option_new_2['ip_timeout']>24) $option_new_2['ip_timeout']=24;

                // Limito il page timeout
                if($option_new_2['page_timeout']<60) $option_new_2['page_timeout']=60;
                if($option_new_2['page_timeout']>3600) $option_new_2['page_timeout']=3600;

                // APPORTO LE MODIFICHE
                $totale=0;
                while(list($key,$value)=each($option_new_2)){
                	if ($key == 'admin_pass') {
                		if ($value) {
	                		user_change_password($value, true);
	                	}
                	} else {
						sql_query("UPDATE $option[prefix]_config SET value='$value' WHERE name='$key'");
                    }
					$totale+=mysql_affected_rows();
                }

                // CALCOLO IL PROSSIMO REPORT
                list($date_m,$date_d,$date_w,$date_Y)=explode('-',date('m-d-w-Y'));

//              $next=mktime(0,0,0,$date_m,$date_d-$date_w+$option_new_2['report_w_day']+7,$date_Y);
				/*** Ogni volta che si salvavano le impostazioni, il report via email veniva spostato alla settimana successiva.
				ora sfrutto la strtotime() con la dicitura ad es. 'next Sunday'. */
				$wdays = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
				$next = strtotime('next '.$wdays[ $option_new_2['report_w_day'] ]);
				unset($wdays);
				/*** */

/***                $oggi=time()-$option_new_2['timezone']; */
                $oggi=time()-($option_new_2['timezone']*3600);
                if($next-$oggi>604800) $next=$next-604800;

				//echo 'next='.date('d-m-Y H:i', $next);
                sql_query("UPDATE $option[prefix]_config SET value='$next' WHERE name='instat_report_w'");
                $totale+=mysql_affected_rows();

                // VERIFICO CHE SIA AVVENUTO ALMENO UN UPDATE
                if($totale<1){
                        $body="$pref[not_done]";
                        if(mysql_error()!='') $body.="<br><br>".str_replace('%error%',mysql_error(),$error[error_decl]);
                        $return.=info_box($string['error'],$body);
                }
                else {
                        $createDone=create_option_file();
                        $return.=($createDone ? info_box($string['information'],$pref['done']) : info_box($string['error'],$body));
                     }
        }
        else{
                $body="$errore<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['error'],$body);
        }
}
else{
        //foreach($option as $key => $value) $option[$key]=stripslashes($value);

        $return.=
        "<br>\n<form action=\"admin.php?action=preferenze&opzioni=applica\" method=\"post\">".
        "\n<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "\n\t<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$pref[opzioni]</span></td></tr>".
        "</select></td></tr>".
        // STATS ABILITATE
        "\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[stats_disabled]</span></td><td bgcolor=$style[table_bgcolor]><select name=\"option_new[stats_disabled]\">".
        "\n\t\t<option value=\"1\"".($option['stats_disabled']==1 ? ' selected' : '').">$pref[stats_disabled_yes]</option>".
        "\n\t\t<option value=\"0\"".($option['stats_disabled']!=1 ? ' selected' : '').">$pref[stats_disabled_no]</option>".
        // SCELTA LINGUA
        "\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[lang]</span></td><td bgcolor=$style[table_bgcolor]><select name=\"option_new[language]\">";
        // Inizio lettura directory LINGUE
        $location='lang/';
        $hook=opendir($location);
        while(($file=readdir($hook)) !== false){
                if($file != '.' && $file != '..'){
                        $path=$location . '/' . $file;
                        if(is_dir($path)) $elenco0[]=$file;
                }
        }
        closedir($hook);
        natsort($elenco0);
        // Fine lettura directory LANG
        while(list($key, $val)=each($elenco0)){
                $val=chop($val);
                // Leggo il nome della lingua
                $language_name=file("lang/$val/lang.name");
                $return.="\n\t\t<option value=\"$val\"".($option['language']=="$val" ? ' selected' : '').">$language_name[0]</option>";
        }
        $return.="</select></td></tr>";

        // QUANTE CIFRE MINIME METTO?
        $return.="\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[cifre_1]</span></td><td bgcolor=$style[table_bgcolor]><select name=\"option_new[cifre]\">";
        $cifrelist=Array('1','2','3','4','5','6','7','8');
        while(list($key,$val)=each($cifrelist)) $return.="<option value=\"$val\"".($option['cifre']==$val ? ' selected' : '').">$val</option>";
        $return.="</select><span class=\"tabletextA\"> $pref[cifre_2]</span></td></tr>";

        // SCELTA STILE CONTATORE
        $return.="\n\t<tr><td align=\"right\" valign=\"middle\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[style_1]</span></td><td align=\"left\" valign=\"middle\" bgcolor=$style[table_bgcolor]>";
        $val=($newstyle!='' ? $newstyle : $option['stile']);
        if($val=='0') $return.="<span class=\"tabletextA\">".$pref['style_2']."</span>";
        else for($i=0; $i<10; $i=$i+1) $return.="<IMG SRC=\"stili/$val/$i.gif\" align=\"middle\">";
        $return.=
        "<INPUT TYPE=\"hidden\" name=\"option_new[stile]\" value=\"".$val."\">".
        "\n\n<script>".
        "\nfunction view_styles(url) {".
        "\n\tstili=window.open(url,'stili','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=350,HEIGHT=600,LEFT=0,TOP=0');".
        "\n\t}".
        "\n</script>".
        "\n<span class=\"tabletextA\"><a href=\"javascript:view_styles('inc/popup_stili.inc.php?currentstyle=$val');\">".$pref['style_edit']."</a></span>".
        "</td></tr>".
        // INDIRIZZO E-MAIL
        "\n\t<tr><td width=\"30%\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[user_mail]</span></td><td bgcolor=$style[table_bgcolor]><textarea name=\"option_new[user_mail]\" cols=\"60\" rows=\"2\">$option[user_mail]</textarea></td></tr>".
        // PASSWORD
        "\n\t<tr><td width=\"30%\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[pass_1]</span></td><td bgcolor=$style[table_bgcolor]><input type=\"password\" name=\"option_new[admin_pass]\"><span class=\"tabletextA\"> $pref[pass_2]</span></td></tr>".
        // CONFERMA PASSWORD
        "\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[pass_3]</span></td><td bgcolor=$style[table_bgcolor]><input type=\"password\" name=\"option_new[pass_confirm]\"></td></tr>".
        // PROTEGGI LE STATISTICHE
        "\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[use_pass]</span></td><td bgcolor=$style[table_bgcolor]><select name=\"option_new[use_pass]\">".
        "<option value=1".($option['use_pass']==1 ? ' selected' : '').">$pref[si]</option>".
        "<option value=0".($option['use_pass']==0 ? ' selected' : '').">$pref[no]</option></select>".
        "&nbsp;&nbsp;<span class=\"tabletextA\"><a href=\"admin.php?action=unlock_pages\">$pref[unlocked_pages]</a></td></tr>".
        // TIMEZONE
        "\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[zone_1]</span></td><td bgcolor=$style[table_bgcolor]><select name=\"option_new[timezone]\">\"";
        $timelist=Array('-12','-11','-10','-9','-8','-7','-6','-5','-4','-3','-2','-1','0','+1','+2','+3','+4','+5','+6','+7','+8','+9','+10','+11','+12');
        while(list($key,$val)=each($timelist)) $return.="\n\t\t<option value=\"$val\"".($option['timezone']==$val ? ' selected' : '').">$val</option>";
        $return.="</select><span class=\"tabletextA\">$pref[zone_2]</span></td></tr>";

        // NOME DEL SITO
        $nomesito=str_replace('"',"'",stripcslashes($option['nomesito']));
        $return.="\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[site_name]</span></td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option_new[nomesito]\" value=\"$nomesito\" maxlength=\"200\" size=\"50\"></td></tr>";

        //Server Url
        $return.="\n\t<tr><td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[site_url]</span></td><td bgcolor=$style[table_bgcolor]><textarea name=\"option_new[server_url]\" cols=\"60\" rows=\"3\">$option[server_url]</textarea></td></tr>";

        // TEMPLATES
        $location='templates/';
        $hook=opendir($location);
        while(($file=readdir($hook))!==false){
                if($file!='.' && $file!='..'){
                        $path=$location.'/'.$file;
                        if(is_dir($path)) $elenco2[]=$file;
                }
        }
        closedir($hook);
        natsort($elenco2);
        // Fine lettura directory TEMPLATES
        $return.="\n\t<tr><td valign=\"top\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[template]</span></td><td bgcolor=$style[table_bgcolor]>";
        while(list($key,$val)=each($elenco2)){
                $val=chop($val);
                $return.="\n\t\t<input type=\"radio\" name=\"option_new[template]\" value=\"$val\"".($val==$option['template'] ? ' checked' : '')." class=\"radio\"> <span class=\"tabletextA\"><b>$val</b> </span><br>";
        }
        $return.="</td></tr>";

        // Accessi e visite di partenza
        $return.="\n\t<tr><td bgcolor=$style[table_bgcolor] valign=\"top\" align=\"right\"><span class=\"tabletextA\">$pref[starthits]</span></td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option_new[starthits]\" value=\"$option[starthits]\" maxlength=\"8\" size=\"8\"></td></tr>".
        "\n\t<tr><td bgcolor=$style[table_bgcolor] valign=\"top\" align=\"right\"><span class=\"tabletextA\">$pref[startvisits]</span></td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option_new[startvisits]\" value=\"$option[startvisits]\" maxlength=\"8\" size=\"8\"></td></tr>";

        // Gestione moduli
        $return.="\n\t<tr><td valign=\"top\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[moduli_desc]</span></td><td bgcolor=$style[table_bgcolor]>";
        for($i=0;$i<11;++$i){
                $x="moduli_$i";
                $return.="\n\t\t<input type=\"checkbox\" name=\"option[moduli_$i]\" value=\"1\" class=\"checkbox\"".(@$modulo[$i]>0 ? ' checked' : '')."><span class=\"tabletextA\">".$pref[$x]."</span><br>";
                if(($i==1) || ($i==3) || ($i==4) || ($i==5)){
                        $x="moduli_".$i."_m";
                        $return.="<img src=\"templates/$option[template]/images/arrow_dx_dw.gif\"><input type=\"checkbox\" name=\"option[moduli_m_$i]\" value=\"1\" class=\"checkbox\"".($modulo[$i]=='2' ? ' checked' : '')."><span class=\"tabletextA\">".$pref[$x]."</span><br>";
                }
        }
        $return.="</td></tr>";

        // PRUNING
        $return.=
        "\n\t<tr><td valign=\"top\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[pruning]</span></td>".
        "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_0_on]\" value=\"1\" class=\"checkbox\"".($option['prune_0_on']==1 ? ' checked' : '').">".
        str_replace('%value%',"<input name=\"option_new[prune_0_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_0_value]\">",$pref['prune_0']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_1_on]\" value=\"1\" class=\"checkbox\"".($option['prune_1_on']==1 ? ' checked' : '').">".
        str_replace(Array('%value%','%table_prefix%'),Array("<input name=\"option_new[prune_1_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_1_value]\">",$option['prefix']),$pref['prune_1']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_2_on]\" value=\"1\" class=\"checkbox\"".($option['prune_2_on']==1 ? ' checked' : '').">".
        str_replace(Array('%value%','%table_prefix%'),Array("<input name=\"option_new[prune_2_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_2_value]\">",$option['prefix']),$pref['prune_2']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_3_on]\" value=\"1\" class=\"checkbox\"".($option['prune_3_on']==1 ? ' checked' : '').">".
        str_replace(Array('%value%','%table_prefix%'),Array("<input name=\"option_new[prune_3_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_3_value]\">",$option['prefix']),$pref['prune_3']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_4_on]\" value=\"1\" class=\"checkbox\"".($option['prune_4_on']==1 ? ' checked' : '').">".
        str_replace(Array('%value%','%table_prefix%'),Array("<input name=\"option_new[prune_4_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_4_value]\">",$option['prefix']),$pref['prune_4']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[prune_5_on]\" value=\"1\" class=\"checkbox\"".($option['prune_5_on']==1 ? ' checked' : '').">".
        str_replace(Array('%value%','%table_prefix%'),Array("<input name=\"option_new[prune_5_value]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[prune_5_value]\">",$option['prefix']),$pref['prune_5']).
        "<br>".
        "\n\t\t<input type=\"checkbox\" name=\"option_new[auto_optimize]\" value=\"1\" class=\"checkbox\"".($option['auto_optimize']==1 ? ' checked' : '').">".
        str_replace('%HITS%',"<input name=\"option_new[auto_opt_every]\" type=\"text\" size=\"5\" maxlength=\"5\" value=\"$option[auto_opt_every]\">",$pref['auto_optimize']).
        "</span></td></tr>";

        // REPORT VIA MAIL
        $return.=
        "\n\t<tr><td valign=\"top\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[report_title]</span></td>".
        "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".
        "<input type=\"checkbox\" name=\"option_new[report_w_on]\" value=\"1\" class=\"checkbox\"".($option['report_w_on']==1 ? ' checked' : '').">";

        $tmp="<select name=\"option_new[report_w_day]\">";
        for($i=0,$tot=count($varie['days']);$i<$tot;++$i) $tmp.="<option value=\"$i\"".($option['report_w_day']=="$i" ? ' selected' : '').">".$varie['days'][$i]."</option>";
        $tmp.="</select>";

        $return.=
        str_replace('%day%',$tmp,$pref['report_desc']).
        "&nbsp;&nbsp;<a href=\"admin.php?action=preferenze&opzioni=manualsendreport\"><img src=\"templates/$option[template]/images/icon_sendmail.gif\" alt=\"\" border=0><b>$pref[sendreport]</b></a></span></td></tr>";

        // TIMEOUT
        $return.=
        "\n\t<tr><td valign=\"top\" align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[timeout]</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".
        str_replace('%value%',"<input name=\"option_new[ip_timeout]\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"$option[ip_timeout]\">",$pref['ip_timeout']).
        "<br>".
        str_replace("%value%","<input name=\"option_new[page_timeout]\" type=\"text\" size=\"4\" maxlength=\"4\" value=\"$option[page_timeout]\">",$pref['page_timeout']).
        "</span></td></tr>";

        // SUBMIT
        $return.=
        "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$pref[salva]\" class=\"submit_button\"></center></td></tr>".
        "\n</table>\n</form>";

        ////////////////////////////////
        // GENERA CODICE MONITORAGGIO //
        ////////////////////////////////
        if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'],'/cgi-bin/')===FALSE) $path_include=dirname($_SERVER['SCRIPT_FILENAME']).'/';
        else{
            $path_include=(!isset($_SERVER['DOCUMENT_ROOT']) ? dirname($_SERVER['PATH_TRANSLATED']) : $_SERVER['DOCUMENT_ROOT'].str_replace('\\','/',dirname($_SERVER['PHP_SELF']))).'/';
            $path_include=str_replace(Array('//','\\\\'),'/',$path_include);
            }
        if(strpos($path_include,':')===1) $path_include=substr($path_include,2);
        $return.=
        "\n<br>".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$pref[main_codescript]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center>$pref[html_page_code]<br>".
        "<textarea style=\"border-width:2px; border-color:green; resize:none;\" name=\"mainscript\" cols=\"80%\" rows=\"4\" readonly>".
        "<script type=\"text/javascript\" src=\"$option[script_url]/php-stats.js.php\"></script>\n<noscript><img src=\"$option[script_url]/php-stats.php\" border=\"0\" alt=\"\"></noscript>".
        "</textarea><br><br>$pref[php_page_code]<br>".
        "<textarea style=\"border-width:2px; border-color:red; resize:none;\" name=\"mainscript\" cols=\"80%\" rows=\"4\" readonly>".
        "&lt;?php\ndefine('__PHP_STATS_PATH__','$path_include');\ninclude(__PHP_STATS_PATH__.'php-stats.redir.php');\n?&gt;".
        "</textarea></center></span><br>".
        "</td></tr></table><BR>";

        ///////////////////
        // GENERA CODICE //
        ///////////////////
        $return.=
        "\n<br>\n<form action=\"admin.php?action=preferenze#scriptgenerator\" method=\"post\">".
        "<a name=\"scriptgenerator\"></a><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$pref[codescript]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><center>".
        "\n\n\t<table border=\"0\" $style[table_header] width=\"100%\" align=\"center\">".
        "\n\t\t<tr><td align=\"right\"><span class=\"tabletextA\">$pref[cs_mode]</span></td>".
        "<td><select name=\"whatview\">";
        for($i=0;$i<7;++$i)$return.="<option value=\"$i\"".($whatview==$i ? ' selected' : '').">".$pref["cs_view_$i"]."</option>";
        $return.=
        "</select></td></tr>".
        "\n\t\t<tr><td align=\"right\"><span class=\"tabletextA\">$pref[cs_style]</span></td>".
        "<td><select name=\"scstyle\">".
        "<option value=\"\"".($scstyle=='' ? ' selected' : '').">$pref[cs_style_defalut]</option>". // Stile di default
        "<option value=\"0\"".($scstyle=='0' ? ' selected' : '').">0</option>"; // Stile testuale
        $location="stili/";
        $hook=opendir($location);
        while(($file=readdir($hook))!==false){
                if($file!='.' && $file!='..'){
                        $path=$location.'/'.$file;
                        if(is_dir($path)) $elenco[]=$file;
                }
        }
        closedir($hook);
        natsort($elenco);;
        while(list($key,$val)=each($elenco)){
                $val=chop($val);
                $return.="<option value=\"$val\"".($scstyle==$val ? ' selected' : '').">$val</option>";
        }
        $return.=
        "</select></td></tr>".
        "\n\t\t<tr><td align=\"right\"><span class=\"tabletextA\">$pref[cs_digits]</class></td>".
        "<td><select name=\"scdigits\">".
        "<option value=\"\">$pref[cs_style_defalut]</option>";
        for($i=1;$i<9;++$i) $return.="<option value=\"$i\"".($scdigits==$i ? ' selected' : '').">$i</option>";
        $return.=
        "</td></tr>".
        "</table>".
        "<center><textarea style=\"resize: none;\" name=\"scriptcode\" cols=\"80%\" rows=\"3\" readonly>";
        $code="<script type=\"text/javascript\" src=\"$option[script_url]/view_stats.js.php";
        if($whatview!='') $code.="?mode=$whatview";
        if($scstyle!=''){
                $code.=($whatview=='' ? '?' : '&amp;');
                $code.="style=$scstyle";
        }
        if($scdigits>0 && $scdigits!=''){
                $code.=($scstyle=='' && $whatview=='' ? '?' : '&amp;');
                $code.="digits=$scdigits";
        }
        $code.="\"></script>";
        $return.=$code."</textarea></center><br>";
        $return.="<center><input type=\"submit\" value=\"$pref[submitcode]\" class=\"submit_button\"></center><br>";
        $code=stripslashes($code);
        $return.="<span class=\"tabletextA\">".$pref['preview_code']." ".$code."</span><br><br>";
        $return.="</form></td></tr></table>";
/*
        ///////////////////////////////
        // REFRESH MOTORI DI RICERCA //
        ///////////////////////////////
        $return.=
        "\n<br><br>\n<form action=\"admin.php?action=refresh\" method=\"post\">".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$pref[refresh]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$pref[refresh_desc]</span><br>".
        "<input type=\"Submit\" value=\"$pref[refresh_go]\" class=\"submit_button\"></center></td></tr>".
        "</form></table>";
*/
}
return($return);
}
?>
