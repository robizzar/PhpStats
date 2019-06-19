<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['opzioni'])) $opzioni=$_GET['opzioni']; else $opzioni='';
if(isset($_POST['option_new'])) $option_new=$_POST['option_new']; else $option_new='';

function esclusioni() {
global $db,$is_loged_in,$opzioni,$error,$style,$string,$pref,$option,$option_new,$php_stats_esclusion,$phpstats_title;

$return='';
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['esclude_title'];

$option_new=trim($option_new);
$modifywhat='';

if(substr($opzioni,0,3)==='exc'){
        // APPORTO LE MODIFICHE
        if($opzioni==='excfol'){
                $modifywhat='exc_fol';
                $ok=TRUE;
                if($option_new!==''){
                        $exfol=explode("\n",$option_new);
                        for($i=0,$tot=count($exfol);$i<$tot;++$i){
                                $fol=addslashes(strtolower(trim($exfol[$i])));
                                if($fol=='') continue; //scarto riga vuota
                                $exfol[$i]=$fol;
                        }
                }//qui non mi vengono in mente possibili errori
                $option_new=(count($exfol)==0 ? '' : implode("\n",$exfol));
        }
        elseif($opzioni==='excsip'){
                $modifywhat='exc_sip';
                $ok=TRUE;
                if($option_new!==''){
                        $excsips=explode("\n",$option_new);
                        for($i=0,$tot=count($excsips);$i<$tot;++$i){
                                if(strpos($excsips[$i],'-')===FALSE){//ip
                                        $ip=trim($excsips[$i]);
                                        if($ip=='') continue; //scarto riga vuota
                                        if(!ereg('^([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])$',$ip)){
                                                $errore=$error['exc_01'];//errore: ip non valido
                                                $ok=FALSE;
                                                break;
                                        }
                                        $ip=str_pad(sprintf('%u',ip2long($ip)),10,'0',STR_PAD_LEFT);
                                        $excsips[$i]=$ip.$ip;
                                }
                                else{//range
                                        $tmp=trim($excsips[$i]);
                                        if($tmp=='') continue; //scarto riga vuota
                                        list($ip1,$ip2)=explode('-',$tmp);
                                        $ip1=trim($ip1);
                                        if(!ereg('^([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])$',$ip1)){
                                                $errore=$error['exc_01'];//errore ip non valido
                                                $ok=FALSE;
                                                break;
                                        }
                                        $ip2=trim($ip2);
                                        if(!ereg('^([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])$',$ip2)){
                                                $errore=$error['exc_01'];//errore ip non valido
                                                $ok=FALSE;
                                                break;
                                        }
                                        $ip1=sprintf('%u',ip2long($ip1))-0;
                                        $ip2=sprintf('%u',ip2long($ip2))-0;

                                        if($ip2<$ip1){ //ip finale minore di quello iniziale
                                                $errore=$error['exc_02'];//error range errato
                                                $ok=FALSE;
                                                break;
                                        }
                                        $ip1=str_pad($ip1,10,'0',STR_PAD_LEFT);
                                        $ip2=str_pad($ip2,10,'0',STR_PAD_LEFT);
                                        $excsips[$i]=$ip1.$ip2;
                                }
                        }
                        if($ok) $option_new=(count($excsips)==0 ? '' : implode("\n",$excsips));
                }
        }
        elseif($opzioni==='excdip'){
                $modifywhat='exc_dip';
                $ok=TRUE;
/*                if($option_new!==''){
                        $excdips=explode("\n",$option_new);
                        for($i=0,$tot=count($excdips);$i<$tot;++$i){
                                $tmp=trim($excdips[$i]);
                                if($tmp=='') continue; //scarto riga vuota
                                if(substr_count($tmp,'|')!=1){//deve esserci un solo |
                                        $errore=$error['exc_03'];//errore formato errato
                                        $ok=FALSE;
                                        break;
                                }
                                list($id,$ip)=explode('|',$tmp);
                                $id=trim($id);
                                if(!ereg('^([0-9]+)$',$id)){//controllo che siano stati inseriti solo dei numeri
                                        $errore=$error['exc_04'];//errore id errato
                                        $ok=FALSE;
                                        break;
                                }
                                $id-=0;
                                if($id<0 || $id>99){//l'indice deve essere compresotra 0 e 99
                                        $errore=$error['exc_05'];//errore id fuori dai limiti
                                        $ok=FALSE;
                                        break;
                                }
                                $ip=trim($ip);
                                if(!ereg('^([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])$',$ip)){
                                        $errore=$error['exc_01'];//errore ip non valido
                                        $ok=FALSE;
                                        break;
                                }
                                $id=str_pad($id,2,'0',STR_PAD_LEFT);
                                $ip=str_pad(sprintf('%u',ip2long($ip)),10,'0',STR_PAD_LEFT);
                                $excdips[$i]=$id.$ip;
                        }
                        if($ok) $option_new=(count($excdips)==0 ? '' : implode("\n",$excdips));
                }
*/
        }
        if($ok){
                $totale=0;
                sql_query("UPDATE $option[prefix]_config SET value='$option_new' WHERE name='$modifywhat'");
                $totale+=mysql_affected_rows();
                // VERIFICO CHE SIA AVVENUTO ALMENO UN UPDATE
                if($totale<1){
                        $body="$pref[not_done]";
                        if(mysql_error()!='') $body.="<br><br>".str_replace('%error%',mysql_error(),$error['error_decl']);
                        $return.=info_box($string['error'],$body);
                }
                else {
                     $createDone=create_option_file();
                     if ($createDone) $return.=info_box($string['information'],$pref['done']);
                     else {
                          $body="$errore<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>"; //$errore
                          $return.=info_box($string['error'],$body);
                          }
             }
        }
        else{
                $body="$errore<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>"; //$errore
                $return.=info_box($string['error'],$body);
        }
}
else{
        if($option['exc_sip']!==''){
                $excsips=explode("\n",$option['exc_sip']);
                for($i=0,$tot=count($excsips);$i<$tot;++$i){
                        $from=long2ip(substr($excsips[$i],0,10)-0);
                        $to=long2ip(substr($excsips[$i],10)-0);
                        $excsips[$i]=($from==$to ? $from : $from.'-'.$to);
                }
                $option['exc_sip']=implode("\n",$excsips);
        }
/*
        if($option['exc_dip']!==''){
                $excdips=explode("\n",$option['exc_dip']);
                for($i=0,$tot=count($excdips);$i<$tot;++$i){
                        $id=substr($excdips[$i],0,2);
                        $ip=long2ip(substr($excdips[$i],2)-0);
                        $excdips[$i]=$id.'|'.$ip;
                }
                $option['exc_dip']=implode("\n",$excdips);
        }
*/
        // Titolo
        $return.=
        "<span class=\"pagetitle\">$phpstats_title<br><br></span>".
        "<table border=\"0\" $style[table_header] width=\"90%\"  align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[esclude_co_subtitle]</span></td></tr>";
        //
        if(strpos($php_stats_esclusion,"|$option[script_url]|")!==FALSE){
                $status=$string['esclude_status_on'];
                $click_value=$string['esclude_inc'];
                $php_stats_esclusion=0;
        }
        else{
                $status=$string['esclude_status_of'];
                $click_value=$string['esclude_esc'];
                $php_stats_esclusion=1;
        }
        $return.=
        "<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><br><center><span class=\"tabletextA\">".
        $status.
        "<form action=\"admin.php?action=esclusioni&opzioni=change\" method=\"post\">".
        "<input type=\"hidden\" name=\"option_new\" value=\"$php_stats_esclusion\">".
        "<input type=\"Submit\" value=\"$click_value\"></center>".
        "</form></td></tr>".
        "</table>";
        //Exclused Url
        $return.=
        "\n<br><br>".
        "\n<form action=\"admin.php?action=esclusioni&opzioni=excfol\" method=\"post\">".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[esclude_fol_subtitle]</span></td></tr>".
        "\n\t<tr><td align=\"right\" width=100% bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[esclude_fol]</span></td><td bgcolor=$style[table_bgcolor]><textarea name=\"option_new\" cols=\"60\" rows=\"5\">$option[exc_fol]</textarea></td></tr>".
        "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$string[esclude_salva]\"></center></td></tr>".
        "\n</table>\n</form>";
        //Exclused SIP
        $return.=
        "\n<br><br>".
        "\n<form action=\"admin.php?action=esclusioni&opzioni=excsip\" method=\"post\">".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[esclude_ips_subtitle]</span></td></tr>".
        "\n\t<tr><td align=\"right\" width=100% bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[esclude_ips]</span></td><td bgcolor=$style[table_bgcolor]><textarea name=\"option_new\" cols=\"60\" rows=\"5\">$option[exc_sip]</textarea></td></tr>".
        "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$string[esclude_salva]\"></center></td></tr>".
        "\n</table>\n</form>";
        //Exclused DIP
        $return.=
        "\n<br><br>".
        "\n<form action=\"admin.php?action=esclusioni&opzioni=excdip\" method=\"post\">".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[esclude_ipd_subtitle]</span></td></tr>".
        "\n\t<tr><td align=\"right\" width=100% bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[esclude_ipd]</span></td><td bgcolor=$style[table_bgcolor]><textarea name=\"option_new\" cols=\"60\" rows=\"5\">$option[exc_dip]</textarea></td></tr>".
        "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$string[esclude_salva]\"></center></td></tr>".
        "\n</table>\n</form>";
}
return($return);
}
?>