<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */

define('IN_PHPSTATS',true);

function php_stats_recognize_php()
{
	// VARIABILI ESTERNE
	  if(isset($_COOKIE['php_stats_esclusion'])) $php_stats_esclusion=$_COOKIE['php_stats_esclusion']; else $php_stats_esclusion='';
	         if(isset($_SERVER['HTTP_REFERER'])) $HTTP_REFERER=$_SERVER['HTTP_REFERER'];
	          if(isset($_SERVER['REMOTE_ADDR']))
	          {
          			$ip=(isset($_SERVER['HTTP_PC_REMOTE_ADDR']) ? $_SERVER['HTTP_PC_REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR']);
          			$hostname = gethostbyaddr($ip);	/*** L'HOSTNAME LO PRENDO QUI UNA VOLTA PER TUTTE ***/
          	  }
    require('option/php-stats-options.php');
    
    // Controllo esclusione tramite cookie prima per evitare operazioni inutili
    if(strpos($php_stats_esclusion,"|$option[script_url]|")!==FALSE)
    	return(0);
    
    if($option['stats_disabled'])
    	return(0); // Statistiche attive?
    
    $GLOBALS['php_stats_script_url']=$option['script_url'];
    $GLOBALS['php_stats_full_recn']=1;
    $GLOBALS['bcap_auto_update']=$option['bcap_auto_update'];	/*** Dall'interno della classe di browscap posso accedere solo a variabili superglobal ***/
    
    require(__PHP_STATS_PATH__.'inc/main_func_class.inc.php');
    
    $append = 'LIMIT 1';
    
    if(!isset($option['prefix']))
    	$option['prefix']='php_stats';
    
    // Inizializzo l'oggetto
    $php_stats_rec = new php_stats_recFunction;
    $php_stats_rec->php_stats_setvariables($option);
    $php_stats_rec->php_stats_setvariables2($countServerUrl,$serverUrl,$date,$append,$modulo,$secondi,$mese_oggi,$data_oggi);
    $php_stats_rec->php_stats_db_connect();
    
    
    $HTTP_USER_AGENT=$loaded=$lang='?';
    
    $ip=$nip=sprintf('%u',ip2long($ip))-0;
    

	// ESCLUSIONE SIP (IP statico)
	for($i=0;$i<$countExcSip;++$i)
  	{
  		$from=substr($excsips[$i],0,10);
  		$to=substr($excsips[$i],10);
  		if($from<=$nip && $nip<=$to)
  		{
/*			if($option['logerrors'])
				file_put_contents(__PHP_STATS_PATH__.'php-stats.log', date('d/m/y H:i')." ** IP exclusion: ".long2ip($ip)."\n", FILE_APPEND);*/
        	$php_stats_rec->php_stats_logerrors(date('d/m/y H:i')." ** IP exclusion: ".long2ip($ip));

  			return(0);
  		}
  	}

	/*** ESCLUSIONE DIP (IP dinamico) by robiz
	Esclude gruppi di host; sarà confrontata la stringa a partire da destra, quindi è possibile escludere
	un intero dominio inserendo ad esempio ".google.com".
	***/
	for($i=0;$i<$countExcDip;++$i)
	{  
		$excdips[$i] = trim($excdips[$i]);	/* Elimina eventuali spazi o \n di troppo */
		$my_len = strlen( $excdips[$i] );
		if ( $excdips[$i] == substr($hostname, 0-$my_len, $my_len) )
		{
/*			if($option['logerrors'])
				file_put_contents(__PHP_STATS_PATH__.'php-stats.log', date('d/m/y H:i')." ** Hostname exclusion: $hostname\n", FILE_APPEND);*/
        	$php_stats_rec->php_stats_logerrors(date('d/m/y H:i')." ** Hostname exclusion: $hostname");
			return(0);
		}
	/* Confronto stringa esatta invece di parte dell'hostname come sopra
		if ($hostname == $excdips[$i])
			return(0); */
	}

	/***
	QUESTA PARTE DI CODICE E' CONTENUTA SIA IN PHP-STATS.PHP (CODICE HTML PER IL MONITORAGGIO)
	SIA IN PHP-STATS.REDIR.PHP (CODICE PHP PER IL MONITORAGGIO)
	***/
	require(__PHP_STATS_PATH__.'/browscap/Browscap.php');
	$bcap = new Browscap(__PHP_STATS_PATH__.'browscap/');	// Creates a new Browscap object (loads or creates the cache)
	$my_bcap = $bcap->getBrowser();							// Gets information about the current browser's user agent

	if ($my_bcap->Browser == 'Default Browser')			// Se non viene riconosciuto (ad es. il browser è troppo e non ancora incluso nel database), visualizza '?'
	{
		$my_bcap->Browser = '?';
		$my_bcap->Version = '';
	}
	$GLOBALS['my_bcap'] = $my_bcap; 
	/*** ***/
	if ($modulo[11] == 0 && $my_bcap->Crawler == true)		// Se è un motore di ricerca e le statistiche non sono attive, esce
	{
//       	$php_stats_rec->php_stats_logerrors(date('d/m/y H:i')." ** skipping engine: ($hostname)");
		return(0);
	}	

	// PREPARARO VARIABILI
	if($loaded==='?')
	{
      	if(!isset($_SERVER['REQUEST_URI']))
      	{
        	if(isset($_SERVER['QUERY_STRING']))
        		$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        	else
        		$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
      }
  		if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']))
  			$loaded=htmlspecialchars(addslashes('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
  		else
  		{
  			$GLOBALS['php_stats_appendVarJs'].='&amp;loaded=1';
  			$GLOBALS['php_stats_sendVarJs']=1;
  		}

		if($loaded!='?')
  		{
  			if($option['www_trunc'])
  			{
  				if(strtolower(substr($loaded,0,11))=='http://www.')
  					$loaded='http://'.substr($loaded,11);
  			}
  			$loadedLC=strtolower($loaded);

    
    		// ESCLUSIONE CARTELLE e URL
    		if ($option['exc_fol']!=='')
    		{
    			for($i=0;$i<$countExcFol;++$i)
    	  		{
    	  			if(strpos($loadedLC,$excf[$i])!==FALSE)
    	  			{
/*						if($option['logerrors'])
							file_put_contents(__PHP_STATS_PATH__.'php-stats.log', date('d/m/y H:i')." ** Folder exclusion: $loadedLC\n", FILE_APPEND);*/
			        	$php_stats_rec->php_stats_logerrors(date('d/m/y H:i')." ** Folder exclusion: $loadedLC");
    	  				return(0);
    	  			}
    	  		}
    		}
  			$tmp='/'.strtolower(basename($loaded));
  			if (in_array($tmp, $default_pages))
  				$loaded=substr($loaded,0,-strlen($tmp));
  			$loaded=php_stats_filter_urlvar($loaded,'sid'); // ELIMINO VARIABILI SPECIFICHE NELLE PAGINE VISITATE (esempio il session-id)
  		}
	}

	if($loaded!='?' && !ereg('^http://[[:alnum:]._-]{2,}',$loaded))
		$loaded='?';

	$date=time()-$option['timezone']*3600;
	list($date_Y,$date_m,$date_d,$date_G)=explode('-',date('Y-m-d-G',$date));
	$mese_oggi=$date_Y.'-'.$date_m; // Y-m
	$data_oggi=$mese_oggi.'-'.$date_d; // Y-m-d
	$ora=$date_G;

	$secondi=$date-3600*$option['ip_timeout']; // CALCOLO LA SCADENZA DELLA CACHE
	/////////////////////////////////////////////////////////////////////////////////////////////
	// VERIFICO SE L'IP E' PRESENTE NELLA CACHE: SE NECESSARIO LO INSERISCO OPPURE LO AGGIORNO //
	/////////////////////////////////////////////////////////////////////////////////////////////
	          $cache_cleared=0; // Flag -> La cache ha subito una pulizia
	              $do_update=0; // Flag -> Devo eseguire l'update della cache
	              $do_insert=0; // Flag -> Devo eseguire l'inserimento nella cache
	$reffer=$details_referer=''; // Setto il referer vuoto fino a prova contraria
	                 $domain='';

	// Riconoscimento immediato agent per evitare operazioni inutili con spider e per poterli raggruppare
	$nome_os=$nome_bw=$titlePage='?';
	if(isset($_SERVER['HTTP_USER_AGENT']) && $HTTP_USER_AGENT==='?')
	{
  		$tmp=htmlspecialchars(addslashes($_SERVER['HTTP_USER_AGENT']));
  		$HTTP_USER_AGENT=str_replace(' ','',$tmp);
  	}
	$spider_agent=$ip_agent_cached=$titleExist=false;

	$temp_bw=$php_stats_rec->php_stats_getbrowser($HTTP_USER_AGENT);
	$temp_os=$php_stats_rec->php_stats_getos($HTTP_USER_AGENT);
	$result=$php_stats_rec->php_stats_sql_query("SELECT data,lastpage,user_id,visitor_id,reso,colo,os,bw,tld,giorno,level FROM $option[prefix]_cache WHERE (user_id='$ip' AND bw='$temp_bw' AND os='$temp_os') LIMIT 1");

	if(mysql_affected_rows()>0)
		$ip_agent_cached=true;
	else
	{
    	if($HTTP_USER_AGENT!='?')
    	{
      		$nome_bw=$php_stats_rec->php_stats_getbrowser($HTTP_USER_AGENT);
       		$nome_os=chop($php_stats_rec->php_stats_getos($HTTP_USER_AGENT));
      	}
    	if($spider_agent===FALSE && ($nome_os=='?' || $nome_bw=='?'))
    	{
/***	    $spider_agent = TRUE;	***/	// Non riconosciuto: lo considero come motore di ricerca
    		list($nome_os,$nome_bw,$spider_agent)=$php_stats_rec->php_stats_getfromip($nip,$nome_os,$nome_bw);
    	}
    	if($spider_agent===true)
    	{
      		$result=$php_stats_rec->php_stats_sql_query("SELECT data,lastpage,user_id,visitor_id,reso,colo,os,bw,tld,giorno,level FROM $option[prefix]_cache WHERE os='$nome_os' AND bw='$nome_bw' LIMIT 1");
      		if(mysql_affected_rows()>0)
      			$ip_agent_cached=true;
      	}
   	}

	if($ip_agent_cached)
  	{
  		list($last_page_time,$last_page_url,$user_id,$visitor_id,$reso,$c,$nome_os,$nome_bw,$domain,$giorno,$level)=mysql_fetch_row($result);
  		$ip=$user_id;
  		if($spider_agent===false)
  		{
  			if(strpos(__RANGE_MACRO__,$nome_os))
  				$spider_agent=true;
  		}
  
  		// Aggiornamento tempo di permanenza dell'ultima pagina
  		if($modulo[3] && ($spider_agent===false))
    	{
    		$tmp=$date-$last_page_time;

/*** */		$last_page_url = mysql_real_escape_string($last_page_url);
    		if($tmp<$option['page_timeout'])
    			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_pages SET presence=presence+$tmp,tocount=tocount+1,date=$date WHERE data='$last_page_url' $append");
    	}
    	
  		// VERIFICO SCADENZA PAGINA IN CASO DI IP IDENTICI
  		if($last_page_time<$secondi)
    	{ // SCADUTO
        	$cache_cleared=$php_stats_rec->php_stats_do_clear($visitor_id,1); // PULIZIA TOTALE
        	$do_insert=1; // DEVO INSERIRE IL NUOVO VISITATORE
        }
  		else
    	{ // NON SCADUTO
        	if($data_oggi!=$giorno) // Controllo visite a cavallo di 2 giorni
          		$cache_cleared=$php_stats_rec->php_stats_do_clear($visitor_id,0); // PULIZIA PARZIALE, NON CANCELLO
        	$do_update=1; // Ma aggiorno sempre un dato non scaduto
    	}
    }
  	else
  		$do_insert=1; // Se non trovo l'IP nella cache inserisco.

	if($do_update) // AGGIORNAMENTO CACHE
    {
    	$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_cache SET data='$date',lastpage='$loaded',giorno='$data_oggi',hits=hits+1".($spider_agent ? '' : ',level=level+1')." WHERE user_id='$ip' $append");
    	$is_uniqe=0;

    	// Parte aggiunta per abilitare un Safe Mode nel caso in cui il javascript non viene caricato la prima volta
    	if(($level==1 && ($spider_agent===false)) && (($reso==='' || $reso=='?') && ($c==='' || $c=='?')))
      	{
      		$GLOBALS['php_stats_appendVarJs'].='&amp;colres=1';
      		$GLOBALS['php_stats_sendVarJs']=1;
      	}
    	++$level;
    	$update_hv='hits=hits+1'.($spider_agent ? ',no_count_hits=no_count_hits+1' : '');
	}

		// RICONOSCIMENTO CONTINUO REFERER
  		if(isset($HTTP_REFERER))
  			$reffer=htmlspecialchars(addslashes($HTTP_REFERER));
  		else
  		{
  			$GLOBALS['php_stats_appendVarJs'].='&amp;referer=1';
  			$GLOBALS['php_stats_sendVarJs']=1;
  		}
  	

	if($do_insert) // INSERIMENTO DATI IN CACHE
  	{
  		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($spider_agent===false))
  		{
  			$tmp=($lang==='?' ? htmlspecialchars(addslashes($_SERVER['HTTP_ACCEPT_LANGUAGE'])) : $lang);
  			$tmp=explode(',',$tmp);
  			$lang=strtolower($tmp[0]);
  		}

		if(($modulo[7] && ($option['ip-zone']==0)) || $option['log_host'])
			$host=$hostname;
		else
			$host='';

		if($spider_agent===FALSE && $modulo[7])
  		{
  			$ip_number=$ip;
  			if(
  			($ip_number>=3232235520 && $ip_number<=3232301055) || //192.168.0.0 ... 192.168.255.255
    		($ip_number>=167772160 && $ip_number<=184549375) || //10.0.0.0 ... 10.255.255.255
    		($ip_number>=2886729728 && $ip_number<=2887778303) || //172.16.0.0 ... 172.31.255.255
    		($ip_number>=0 && $ip_number<=16777215) || //0.0.0.0 ... 0.255.255.255
    		($ip_number>=4026531840 && $ip_number<=4294967295) || //240.0.0.0 ... 255.255.255.255
    		($ip_number==2130706433) //127.0.0.1
    		) $domain='lan';
  			else
  				switch($option['ip-zone'])
         		{
         		default: 	//tramite host
               		$domain='';
               		$tmp=explode('.',$host);
               		for($i=count($tmp)-1;$i>=0;--$i)
               		{
                  		if(!$tmp[$i])
                  			continue; //esistono domini come 'google.com.'
                  		$domain=$tmp[$i];
                  		break;
               		}
               		break;
         		case 1: 	//tramite ip2c MySQL
           	    	$result2=$php_stats_rec->php_stats_sql_query("SELECT tld FROM $option[prefix]_ip_zone WHERE $ip_number BETWEEN ip_from AND ip_to");
           	    	if(mysql_affected_rows()>0) list($domain)=mysql_fetch_row($result2);
           	    	else $domain='unknown';
           	    	break;
/*** DEPRECATED
         		case 2: 	//tramite ip2c file
           		    $domain=$php_stats_rec->php_stats_getIP($ip_number,23,__PHP_STATS_PATH__.'ip-to-country.db',__PHP_STATS_PATH__.'ip-to-country.idx',2);
           		    break; ***/
         		}
  		}

  		$visitor_id=md5(uniqid(rand(), true));
  		$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_cache (user_id,data,lastpage,visitor_id,hits,visits,reso,colo,os,bw,host,tld,lang,giorno,notbrowser,level) VALUES('$ip','$date','$loaded','$visitor_id','1','1','?','?','$nome_os','$nome_bw','$host','$domain','$lang','$data_oggi',".($spider_agent===FALSE ? '0' : '1').",'1')");
  		$is_uniqe=$level=1;
  		$update_hv='hits=hits+1,visits=visits+1'.($spider_agent ? ',no_count_hits=no_count_hits+1,no_count_visits=no_count_visits+1' : '');
  		$GLOBALS['php_stats_appendVarJs'].='&amp;colres=1';
  		$GLOBALS['php_stats_sendVarJs']=1;
  	}

	//////////////////////////////////////////////////////////
	// DATI NON SALVATI IN CACHE E CONTINUAMENTE AGGIORNATI //
	//////////////////////////////////////////////////////////
	// CONTATORI PRINCIPALI
	$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_counters SET $update_hv $append");

	// SCRIVO LA PAGINA VISUALIZZATA
	if($modulo[3])
	{
	  	$what='hits=hits+1'.($spider_agent ? ',no_count_hits=no_count_hits+1' : '');
	
		// CHIEDERE SE GLI SPIDER CONTRIBUISCONO AI PERCORSI
  		if($level<7 && ($spider_agent===false))
  			$what.=', lev_'.$level.'=lev_'.$level.'+1';
  		$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_pages SET $what,date='$date' WHERE data='$loaded' $append");
  		if(mysql_affected_rows()<1)
    	{
    		$lev_1=$lev_2=$lev_3=$lev_4=$lev_5=$lev_6=0;
    		if($level<7 && ($spider_agent===false))
    			eval('$lev_'.$level.'=1;');
    		$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_pages (data,hits,visits,no_count_hits,no_count_visits,presence,tocount,date,lev_1,lev_2,lev_3,lev_4,lev_5,lev_6,outs,titlePage) VALUES('$loaded','1','$is_uniqe',".($spider_agent ? "'1','$is_uniqe'" : "'0','0'").",'0','0','$date','$lev_1','$lev_2','$lev_3','$lev_4','$lev_5','$lev_6','0','?')");
    		$GLOBALS['php_stats_appendVarJs'].='&amp;titlepage=1';
    		$GLOBALS['php_stats_sendVarJs']=1;
    	}
  		else
    	{
    		$titleExist=true;
    		if($option['refresh_page_title'] && ($spider_agent===false))
    		{
      			$GLOBALS['php_stats_appendVarJs'].='&amp;titlepage=1';
      			$GLOBALS['php_stats_sendVarJs']=1;
      		}
    	}
   	if($option['prune_4_on'])
   		$php_stats_rec->php_stats_prune("$option[prefix]_pages",$option['prune_4_value']);
	}

	// VERIFICO REFFER
	if ($reffer!='')
   	{
   		$tmpreffer=$tmpreffer2=$reffer;
   		if($option['www_trunc'])
   		{
   			if(strtolower(substr($tmpreffer,0,11))=='http://www.')
   				$tmpreffer='http://'.substr($tmpreffer,11);
   		}
   		if($php_stats_rec->php_stats_is_internal($tmpreffer)===FALSE)
      		$reffer=php_stats_filter_urlvar($tmpreffer2,'sid'); // ELIMINO VARIABILI SPECIFICHE NEI REFERER (esempio il session-id)
   		else
   			$reffer='';
   	}

	if($reffer!='' && !ereg('^http://[[:alnum:]._-]{2,}',$reffer))
		$reffer='';

	// SCRIVO I MOTORI DI RICERCA, QUERY e REFERER
	if($modulo[4])
	{
  		if($reffer!='')
  		{
    		if(1)
      		{
      			if(substr($reffer,-1)==='/')
      				$reffer=substr($reffer,0,-1);
      			$engineResult=$php_stats_rec->php_stats_getengine($reffer);
      			if($engineResult!==FALSE)
        		{
        			list($nome_motore,$domain,$query,$resultPage)=$engineResult;
	/*** */			$details_referer=implode('|',$engineResult).'|'.urldecode($reffer);
        			
        			// MOTORI DI RICERCA E QUERY
	/*** */ 		$query = mysql_real_escape_string($query);
					if (!empty($query))		// A volte capitano query vuote o non decodificate: qui evita l'inserimento
					{
	        			$clause="data='$query' AND engine='$nome_motore' AND domain='$domain' AND page='$resultPage'";
	        			if($modulo[4]==2)
	        				$clause.=" AND mese='$mese_oggi'";
	        			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_query SET visits=visits+1, date='$date' WHERE $clause $append");
	        			if(mysql_affected_rows()<1)
	          			{
	          				$insert="(data,engine,domain,page,visits,date,mese) VALUES('$query','$nome_motore','$domain','$resultPage','1','$date','".($modulo[4]==2 ? "$mese_oggi" : '')."')";
	          				$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_query $insert");
	          				if($option['prune_3_on'])
	          					$php_stats_rec->php_stats_prune("$option[prefix]_query",$option['prune_3_value']);
	          			}
	          		}
        		}
        		else
        		{	// REFERERS
	/*** */			$details_referer = urldecode($reffer);
	/*** */			$reffer_dec = mysql_real_escape_string($details_referer);
        			$clause="data='$reffer_dec'";
        			if($modulo[4]==2)
        				$clause.=" AND mese='$mese_oggi'";
        			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_referer SET visits=visits+1,date='$date' WHERE $clause $append");
        			if(mysql_affected_rows()<1)
          			{
          				$insert="(data,visits,date,mese) VALUES('$reffer_dec','1','$date','".($modulo[4]==2 ? "$mese_oggi" : '')."')";
          				$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_referer $insert");
          			}
        			if($option['prune_5_on'])
        				$php_stats_rec->php_stats_prune("$option[prefix]_referer",$option['prune_5_value']);
        		}
      		}
  		}
	}

/***
SCRIVE NEI DETTAGLI IL NUMERO DI VISITE DEL VISITATORE E LA DATA DELL'ULTIMA VISITA
(IL TUTTO PRELEVATO DAI COOKIE)
***/
$phpstats_rettime = intval( $_GET['ps_rettime'] );
$phpstats_returns = intval( $_GET['ps_returns'] );
$phpstats_newret  = intval( $_GET['ps_newret'] );

if ($phpstats_newret == 1 && $spider_agent === false)
{
	$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_daily SET rets=rets+1 WHERE data='".date('y-m-d')."'");
	if(mysql_affected_rows()<1)
		$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_daily VALUES('".date('y-m-d')."','0','0','0','0','1')");
}

// SCRIVO I DETTAGLI
	if($modulo[0])
	{
  		if((!$option['refresh_page_title']) && $modulo[3] && $loaded!=='?' && $titleExist===true)
  		{
//file_put_contents('php-stats.log', "loaded: $loaded\n", FILE_APPEND);
    		$resultTitle=$php_stats_rec->php_stats_sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$loaded'");
    		list($titlePage)=mysql_fetch_row($resultTitle);
//file_put_contents('../stats/php-stats.log', "titlePage1: $titlePage\n", FILE_APPEND);
	/*** */	$titlePage=mysql_real_escape_string($titlePage);
    	}
if ($titlePage == '?' || $titlePage == '')		/*** Se titolo pagina mancante usa URL ***/
	$titlePage = $loaded;

		$details_referer = mysql_real_escape_string($details_referer);	/*** */
//		if($is_uniqe)
			$what="'$visitor_id','$ip','$host','$nome_os','$nome_bw','$lang','$date','$details_referer','$loaded','?','?','$titlePage','$domain','$phpstats_returns','$phpstats_rettime'";
//        else
//        	$what="'$visitor_id','$ip','','','','','$date','','$loaded','','','$titlePage',''";
  		$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_details (visitor_id,ip,host,os,bw,lang,date,referer,currentPage,reso,colo,titlePage,tld,rets,last_return) VALUES ($what)");
//file_put_contents('../stats/php-stats.log', "Details2: $titlePage\n", FILE_APPEND);

  		if($option['prune_0_on'])
  		{
  			$limit=$option['prune_0_value']*3600;
  			$secondi2=$date-$limit;
  			$php_stats_rec->php_stats_sql_query("DELETE FROM $option[prefix]_details WHERE date<$secondi2 LIMIT 2");
  		}
  		if($option['prune_1_on'])
  			$php_stats_rec->php_stats_prune_details("$option[prefix]_details",$option['prune_1_value']);
	}

	// INDIRIZZI IP
	if($modulo[10])
	{
		$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_ip SET hits=hits+1,visits=visits+$is_uniqe,date='$date' WHERE ip='$ip'");
		if(mysql_affected_rows()<1)
		{
			$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_ip VALUES('$ip','$date','1','1')");
		}
		if($option['prune_2_on'])
			$php_stats_rec->php_stats_prune_details("$option[prefix]_ip",$option['prune_2_value']);
	}

	// ACCESSI ORARI
	if($modulo[5])
	{
  		$clause="data='$ora'";
  		if($modulo[5]==2)
  			$clause.=" AND mese='$mese_oggi'";
  		$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_hourly SET $update_hv WHERE $clause $append");
  		if(mysql_affected_rows()<1)
    	{
    		$insert="(data,hits,visits,no_count_hits,no_count_visits,mese) VALUES('$ora','1','$is_uniqe',".($spider_agent ? "'1','$is_uniqe'" : "'0','0'").",'".($modulo[5]==2 ? "$mese_oggi" : '')."')";
    		$php_stats_rec->php_stats_sql_query("INSERT DELAYED INTO $option[prefix]_hourly $insert");
    	}
	}
	if (($modulo[3]==2) || ($option['report_w_on']))
   	{
    	$result=$php_stats_rec->php_stats_sql_query("SELECT name,value FROM $option[prefix]_config WHERE name LIKE 'instat_%'");
    	while($row=mysql_fetch_row($result))
    		$option2[$row[0]]=$row[1];

   		// Mi assicuro che il dato sia un integer
   		$option2['instat_report_w']=intval($option2['instat_report_w']);
   	}

	// MAX UTENTI ON-LINE
	if($modulo[3]==2)
	{
  		list($max_ol,$time_ol)=explode("|",$option2['instat_max_online']);
  		$max_ol=intval($max_ol); // IMPONGO LA CONVERSIONE AD INTERO
  		if($option['online_timeout']==0)
  			$tmp=$date-300;
  		else
  			$tmp=$date-$option['online_timeout']*60;
  		$online=0;
  		$result=$php_stats_rec->php_stats_sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$tmp AND notbrowser=0");
  		if(mysql_affected_rows()>0)
  			$online=mysql_num_rows($result);
  		if($online>$max_ol)
  		{
  			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_config SET value='$online|$date' WHERE name='instat_max_online'");
  		}
	}

	// Se non l'ho fatto prima, se necessario, pulisco un dato in cache
	if(!$cache_cleared)
	{
		$php_stats_rec->php_stats_do_clear();
		$cache_cleared=1;
	}

	// Mi assicuro che il dato sia un integer
	$option['instat_report_w']=intval($option2['instat_report_w']);

	// VERIFICO SE DEVO SPEDIRE L' E-MAIL CON IL PROMEMORIA DEGLI ACCESSI
	if($option['report_w_on'] && $date>$option2['instat_report_w'])
	{
   		include(__PHP_STATS_PATH__.'inc/report_class.inc.php');
   		$php_stats_report_send = new php_stats_reportFunction;
   		$php_stats_report_send->php_stats_report($option,$modulo,0);
   		unset($php_stats_report_send);
	}

	// OPTIMIZE TABLES
	if($option['auto_optimize'])
	{
  		if(!isset($hits))
  			list($hits)=mysql_fetch_row($php_stats_rec->php_stats_sql_query("SELECT hits FROM $option[prefix]_counters LIMIT 1"));
  		if(($hits % $option['auto_opt_every'])==0)
  		{
    		$query="OPTIMIZE TABLES $option[prefix]_cache";
    		if($option['prune_1_on'] || $option['prune_0_on'])
    			$query.=",$option[prefix]_details";
    		if($option['prune_2_on'])
    			$query.=",$option[prefix]_ip";
    		if($option['prune_4_on'])
    			$query.=",$option[prefix]_pages";
    		if($option['prune_3_on'])
    			$query.=",$option[prefix]_query";
    		if($option['prune_5_on'])
    			$query.=",$option[prefix]_referer";
    		$php_stats_rec->php_stats_sql_query($query);
  		}
  	}

	if($spider_agent)
	{
		$GLOBALS['php_stats_sendVarJs']=0;
		$GLOBALS['php_stats_appendVarJs']='';
	}

	


    /*** VERIFICA NUOVA VERSIONE ***/
   	$result = $php_stats_rec->php_stats_sql_query("SELECT * FROM $option[prefix]_config WHERE name='inadm_last_update'");

    if(mysql_affected_rows()>0) {
       	$arr = mysql_fetch_row($result);
       	$option['inadm_last_update'] = $arr[1];
	}
    	    	
	// Check nuove versioni (ogni 10 giorni)
   	if ( (time()-$option['inadm_last_update']) > (10*24*3600) )
   	{
   		$update = @file_get_contents('http://www.robertobizzarri.net/php-stats/phpstats_ver_check.php?url='.trim($option['script_url']).'&ver='.trim($option['phpstats_ver']).'&mon=php');
   		if (strpos($update, '<!-- New PHP-Stats Version -->') !== false)
   		{
		    if ($option['check_new_version'])
    		{
    			$update_available = true;
    			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_config SET value='1' WHERE name='inadm_upd_available'");
    
          		$site=explode("\n",$option['server_url']);
          		$site_url=str_replace(Array('http://','https://'),'',$site[0]);
        
          		$user_email=explode("\n",$option['user_mail']);
          		$user_email=$user_email[0];
        
                $headers =
                "From: Php-Stats\r\n".
        		"MIME-Version: 1.0\r\n".								// To send HTML mail, the Content-type header must be set
        		"Content-type: text/html; charset=iso-8859-1\r\n";
        		
				$message = 
				'Site: ' . $option['server_url'] . '<br><br>' .
				'A new version of PHP-Stats is available.<br><br>' .
				'<a href="http://www.robertobizzarri.net/php-stats/">Click here to visit the support site.</a><br>';
        		
				mail($user_email, 'PHP-Stats: new version', $message, $headers);
    		}
    	}
    	else
    	{
   			$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_config SET value='0' WHERE name='inadm_upd_available'");
   		}
   		$php_stats_rec->php_stats_sql_query("UPDATE $option[prefix]_config SET value='".time()."' WHERE name='inadm_last_update'");
    }


	if($GLOBALS['php_stats_sendVarJs']===1)
		$GLOBALS['php_stats_appendVarJs']="ip=$ip&amp;visitor_id=$visitor_id&amp;date=$date".$GLOBALS['php_stats_appendVarJs'];
	unset($php_stats_rec,$option);

	return(1);
}

$php_stats_ok=php_stats_recognize_php();
?>
