<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;
if(isset($_GET['sort'])) $sort=addslashes($_GET['sort']); else $sort='data'; // Default sort
if(isset($_GET['order'])) $order=addslashes($_GET['order']); else $order=0; // Default order
if(isset($_GET['from'])) $from=addslashes($_GET['from']); else $from='';
if(isset($_GET['to'])) $to=addslashes($_GET['to']); else $to = '';

function ip()
{
    global $db, $string, $error, $style, $option, $varie, $start, $sort, $order, $phpstats_title, $pref, $from, $to;
    if ($from == '')
        $from = '0.0.0.1';
    if ($to == '')
        $to = '255.255.255.255';
    $from        = gethostbyname($from); //supporto per la ricerca su hostname
    $to          = gethostbyname($to); //supporto per la ricerca su hostname
    $from_long   = sprintf('%u', ip2long($from)) - 0;
    $to_long     = sprintf('%u', ip2long($to)) - 0;
    $check_range = ($from_long <= $to_long ? true : false);
    
    // Titolo pagina (riportata anche nell'admin)
    $phpstats_title = $string['ip_title'];
    $return         = "";
    // ORDINAMENTO MENU e QUERY
    $tables         = Array(
        'ip' => 'ip',
        'data' => 'date',
        'accessi' => 'hits',
        'visite' => 'visits'
    );
    $modes          = Array(
        '0' => 'DESC',
        '1' => 'ASC'
    );
    $q_sort         = (isset($tables[$sort]) ? "$tables[$sort]" : 'hits');
    $q_order        = (isset($modes[$order]) ? "$modes[$order]" : 'DESC');
    $q_append       = "$q_sort $q_order";
    $rec_pag        = 50; // risultati visualizzati per pagina
    $return .= "\n<script>\n" . "function whois(url) {\n" . "\ttest=window.open(url,'nome','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=450,HEIGHT=600,LEFT=0,TOP=0');\n" . "}\n" . "function tracking(url) {\n" . "\ttest2=window.open(url,'nome_2','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=640,HEIGHT=400,LEFT=0,TOP=0');\n" . "}\n" . "</script>\n";
    $query_tot  = sql_query("SELECT * FROM $option[prefix]_ip where ip >= $from_long && ip <= $to_long");
    $num_totale = mysql_num_rows($query_tot);
    if ($num_totale > 0) {
        $numero_pagine   = ceil($num_totale / $rec_pag);
        $pagina_corrente = ceil(($start / $rec_pag) + 1);
        // Titolo
        $return .= "<span class=\"pagetitle\">$phpstats_title<br></span>";
        //
        if ($numero_pagine > 1) {
            $tmp = str_replace("%current%", $pagina_corrente, $varie['pag_x_y']);
            $tmp = str_replace("%total%", $numero_pagine, $tmp);
            $return .= "<div align=\"right\"><span class=\"testo\">$tmp&nbsp;&nbsp;</span></div>";
        }
        $return .= "<br><table border=\"0\" $style[table_header] width=\"550\" align=\"center\" class=\"tableborder\"><tr>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . draw_table_title($string['ip'], "ip", "admin.php?action=ip&amp;from=$from&amp;to=$to", $tables, $q_sort, $q_order) . draw_table_title($string['ip_last_visit'], "data", "admin.php?action=ip&amp;from=$from&amp;to=$to", $tables, $q_sort, $q_order) . draw_table_title($string['ip_hits'], "accessi", "admin.php?action=ip&amp;from=$from&amp;to=$to", $tables, $q_sort, $q_order) . draw_table_title($string['ip_visite'], "visite", "admin.php?action=ip&amp;from=$from&amp;to=$to", $tables, $q_sort, $q_order) . draw_table_title($string['ip_tracking']) . "</tr>";
        
        $result  = sql_query("SELECT * FROM $option[prefix]_ip where ip >= $from_long && ip <= $to_long ORDER BY $q_append LIMIT $start,$rec_pag");
        $current = $start;
        while ($row = mysql_fetch_array($result)) {
            ++$current;
            $row[0] = long2ip($row[0]);
            $return .= "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td width=\"5\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$current</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">";
            $return .= ($option['ext_whois'] == '' ? "<a href=\"javascript:whois('whois.php?IP=$row[0]');\">$row[0]</a>" : "<a href=\"" . str_replace("%IP%", $row[0], $option['ext_whois']) . "\" target=\"_BLANK\">$row[0]</a>");
            $return .= "</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">" . formatdate($row[1]) . " - " . formattime($row[1]) . "</span></td>" . "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[2]</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[3]</span></td><td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"javascript:tracking('tracking.php?what=ip&page=$row[0]');\"><img src=\"templates/$option[template]/images/icon_tracking.gif\" border=0></a></td></tr>";
        }
        $return .= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
        
        if ($numero_pagine > 1) {
            $return .= "<tr><td bgcolor=$style[table_bgcolor] colspan=\"6\" height=\"20\" nowrap>" . pag_bar("admin.php?action=ip&sort=$sort&order=$order&amp;from=$from&amp;to=$to", $pagina_corrente, $numero_pagine, $rec_pag);
        }
        $return .= "</table>"; //.
    } else {
        $return .= "<span class=\"pagetitle\">$phpstats_title<br></span><br>" . "<table border=\"0\" $style[table_header] width=\"500\" align=\"center\">" . "<tr><td bgcolor=$style[table_title_bgcolor]><center><span class=\"tabletitle\">$string[cercaip_result] $from a $to </span></center></td></tr>" . "<tr bgcolor=\"#B3C0D7\"><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">" . ($check_range === true ? '' : "$string[cercaip_check]<br>") . "$string[cercaip_noresult]</span></center></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr></table>";
    }
    
    // CERCAIP by Dapuzz
    if ($from == '0.0.0.1')
        $from = '';
    if ($to == '255.255.255.255')
        $to = '';
    $return .= "<br><form name=\"cercaip\" action=\"admin.php?action=ip\" method=\"get\">" . "<table border=\"0\" $style[table_header] width=\"416\" align=\"center\">" . "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[cercaip_title]</span></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[cercaip_filtra_from]</span>&nbsp<input type=\"hidden\" name=\"action\" value=\"ip\"><input type=\"text\" name=\"from\" value=\"$from\"><span class=\"tabletextA\"> $string[cercaip_filtra_to]</span>&nbsp<input type=\"text\" name=\"to\" value=\"$to\"></center></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor]><center><input type=\"submit\" value=\"$string[cercaip_submit]\"></center></td></tr>" . "</table>" . "</form>";
    return ($return);
}
?>
