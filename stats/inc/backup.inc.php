<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');
if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode='';

function backup() {
global $style,$string,$pref,$message,$error,$_FILES,$HTTP_POST_FILES,$mode,$option,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['backup_title'];
//
$return='';
if($mode!='restore')
  {
  // VISUALIZZO IL FORM
  $return=
  "<script>\n".
  "function setCheckboxes(the_form, do_check)\n".
  "  {\n".
  "    var elts=document.forms[the_form].elements['selected_tbl[]'];\n".
  "    var elts_cnt=elts.length;\n".
  "    for (var i=0; i < elts_cnt; i++) {\n".
  "      elts[i].checked=do_check;\n".
  "      }\n".
  "    return true;\n".
  "  }\n".
  "</script>\n".
  '<br><form name="dump_form" action="inc/backup.php" method="post">'.
  "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\">".
  "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[backup_backup]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor]>".
  (extension_loaded("zlib") ? "<center><span class=\"tabletextA\">$string[backup_cmp]</span>&nbsp<select name=\"compress\"><option value=\"1\" selected>$pref[si]</option><option value=\"0\">$pref[no]</option></select></center>" : '').
  "<br>".
  "<center><table border=\"0\" width=\"100%\" align=\"center\"><tr>";
  $col_step=1;
  $result=sql_query("SHOW TABLE STATUS like '$option[prefix]_%'");
  while($row=mysql_fetch_row($result))
    {
    if($col_step===4) { $return.='</tr><tr>'; $col_step=1; }
    $return.="<td><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"$row[0]\" class=\"checkbox\" checked><span class=\"tabletextA\">$row[0]</span></td>";
    ++$col_step;
    }
  for($i=$col_step;$i<4;++$i) $return.='<td></td>';
  $return.='</tr></table></center><br>'.
  "<center><span class=\"tabletextA\"><a href=\"admin.php?action=backup\" onclick=\"setCheckboxes('dump_form', true); return false;\">$string[backup_selall]</a> / <a href=\"admin.php?action=backup\" onclick=\"setCheckboxes('dump_form', false); return false;\">$string[backup_desall]</a></span></center><br>".
  "<input type=\"hidden\" name=\"operation\" value=\"backup\">".
  "<center><input type=\"submit\" value=\"$string[backup_backup_go]\"></center></form></td><tr></table><BR>".
  "<br><form enctype=\"multipart/form-data\" action=\"admin.php?action=backup&mode=restore\" method=\"POST\">".
  "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
  "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[backup_restore]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[backup_restore_desc]</span>&nbsp<input type=\"file\" name=\"backup_php_stats\"><BR><BR>".
  "<center><input type=\"submit\" value=\"$string[backup_restore_go]\"></center></form></td><tr></table><BR>";
  }
else
  {
  // RIPRISTINO IL DATABASE
  set_time_limit(1200);
  if(!isset($_FILES)) $_FILES=$HTTP_POST_FILES;
  $halt=0;
  if(isset($_FILES['backup_php_stats']))
    {
       $backup_php_stats_name=$_FILES['backup_php_stats']['name'];
    $backup_php_stats_tmpname=$_FILES['backup_php_stats']['tmp_name'];
       $backup_php_stats_type=$_FILES['backup_php_stats']['type'];
       $backup_php_stats_size=$_FILES['backup_php_stats']['size'];
    if($backup_php_stats_name == "")
      {
            $return.=info_box($string['information'],$error['upload_01']);
      $halt=1;
      }
    }
    else
    {
    $return.=info_box($string['error'],$error['upload_01']);
    $halt=1;
    }
  if($halt==0)
  {
  if(is_uploaded_file($backup_php_stats_tmpname))
    {
    if(preg_match("/^(text\/[a-zA-Z]+)|(application\/(x\-)?gzip(\-compressed)?)|(application\/octet-stream)$/is", $backup_php_stats_type))
      {
      if(preg_match("/\.gz$/is",$backup_php_stats_name))
        {
        $do_gzip_compress=FALSE;
        $phpver=phpversion();
        if($phpver >= "4.0") if(extension_loaded("zlib")) $do_gzip_compress=TRUE;
          if($do_gzip_compress)
            {
            $gz_ptr=gzopen($backup_php_stats_tmpname, 'rb');
            $sql_data="";
            while(!gzeof($gz_ptr))
                {
                $sql_data.=gzgets($gz_ptr,100000);
                }
            }
            else
                    {
                $return.=info_box($string['error'],$error['upload_04']);
            $halt=1;
                }
          }
          else $sql_data=fread(fopen($backup_php_stats_tmpname, 'r'), filesize($backup_php_stats_tmpname));
        }
      else
      {
      $return.=info_box($string['error'],$error['upload_03']);
          $halt=1;
          }
    if($halt==0)
      {
      $sql=$sql_data;
          $dump_code=md5("code:$option[phpstats_ver]"); //hash per riconoscimento
          $num=strpos($sql,"# Dump code: $dump_code");
          if($num===FALSE)
            $return.=info_box($string['error'],$string['backup_restore_diffver']);
            else
            {
            $ok=exec_sql_lines($sql);
            if($ok==1)
        $return.=info_box($string['information'],$string['backup_restore_success']);
            else
            $return.=info_box($string['error'],$string['backup_restore_failure']);
            }
          }
    }
    else $return.=info_box($string['error'],$error['upload_02']);
  }
}
return($return);
}


function remove_remarks($sql) {
        $i=0;
        while($i < strlen($sql)) {
             if($sql[$i] == '#' and ($i==0 or $sql[$i-1] == "\n")) {
               $j=1;
               while($sql[$i+$j] != "\n") {
                    ++$j;
                    if($j+$i > strlen($sql)) break;
                    }
               $sql=substr($sql,0,$i) . substr($sql,$i+$j);
                }
                ++$i;
        }
        return($sql);
}

function split_sql_file($sql, $delimiter) {
        $sql=trim($sql);
        $char='';
        $last_char='';
        $ret=array();
        $in_string=true;
        for($i=0; $i<strlen($sql); ++$i) {
           $char=$sql[$i];

           if($char == $delimiter && !$in_string) {
              $ret[]=substr($sql, 0, $i);
              $sql=substr($sql, $i + 1);
              $i=0;
              $last_char='';
              }

           if($last_char == $in_string && $char == ')')  $in_string=false;
           if($char == $in_string && $last_char != "\\") $in_string=false;
           elseif(!$in_string && ($char == "\"" || $char == "'") && ($last_char != "\\")) $in_string=$char;
           $last_char=$char;
           }

        if(!empty($sql)) $ret[]=$sql;
        return($ret);
}


function exec_sql_lines($sql_query, $old_string='', $new_string='') {
        $error_lev=0;
        $sql_query=isset($sql_query) ? $sql_query : "";
        if(!empty($sql_file) && $sql_file != "none") {
           if(get_magic_quotes_runtime() == 1) $sql_query=stripslashes($sql_query);
              if($old_string != '') $sql_query=ereg_replace($old_string,$new_string,$sql_query);
          }
        $sql_query=trim($sql_query);

        if($sql_query!='') {
          $sql_query =remove_remarks($sql_query);
          $pieces=split_sql_file($sql_query,';');
          $cnt_pieces =count($pieces);
                /* run multiple queries */
          for ($i=0; $i<$cnt_pieces; ++$i) {
              $sql=trim($pieces[$i]);
              if(!empty($sql) and $sql[0] != '#')
                {
                $result=sql_query($sql);
                if($result==false)
                  {
                  echo"<font color=\"#FF0000\">Error executing: <b>$sql</b><br>Error string: <b>".mysql_error()."</b></font><br><br>";
                  $error_lev=1;
                  }
                }
              }
          }
        if($error_lev==0) return true;
        else return false;
}
?>
