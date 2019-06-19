<?php
/**
 * Funzioni per la gestione dell'autenticazione degli utenti
 */

/**
 * Ritorna true/false in base allo stato corrente dell'utente, $force_update
 * permette di aggiornare la password dalla cache.
 */
function user_is_logged_in($force_update = false)
{
	static $password_hash;
	
	if (null === $password_hash || $force_update) {
		$result = sql_query("SELECT `value` FROM {prefix}_config WHERE `name`='admin_pass'");
		list($password_hash) = mysql_fetch_row($result);
	}
	
	return !empty($_COOKIE['pass_cookie']) && (sha1($_COOKIE['pass_cookie']) == $password_hash);
}

/**
 * Esegue il login dell'utente controllando l'indice 'pass' dell'array superglobale
 * POST. Ritorna true/false in base al successo dell'operazione.
 */
function user_login($force_update = false, $password = null)
{
	static $password_hash;
	
	if (null === $password_hash || $force_update) {
		$result = sql_query("SELECT `value` FROM {prefix}_config WHERE `name`='admin_pass'");
		list($password_hash) = mysql_fetch_row($result);
	}
	
	if (null === $password) {
		$password = !empty($_POST['pass']) ? $_POST['pass'] : null;
	}
	
	$password = sha1($password);
	
	if (sha1($password) == $password_hash) {
		setcookie('pass_cookie', $password, time() + 60*60*24*365); // 1 anno		
		return true;
	}
	
	return false;
}

/**
 * Gestisce il logout dell'utente. Ritorna false come valore da assegnare alla
 * variabile $is_loged_in, usata nel resto dello script (BC).
 */
function user_logout()
{
	setcookie('pass_cookie','',time(),'/'); // Per risolvere un bug con NS devo cancellare il cookie con il parametro "/" - I clean cookie with "/" for NS bug
	setcookie('pass_cookie','',time());
	
	// Valore di ritorno per la variabile $is_loged_in
	return false;
}

/**
 * Cambia la password corrente dell'utente preoccupandosi di effettuare eventuali
 * hash e di aggiornare le informazioni nel cookie.
 */
function user_change_password($new_password, $login = false)
{
	$new_password_cookie_hash = sha1($new_password);
	$new_password_db_hash = sha1($new_password_cookie_hash);
	
	$result = sql_query("UPDATE {prefix}_config SET `value`='$new_password_db_hash' WHERE `name`='admin_pass'");
	
	if ($result && $login) {
		setcookie('pass_cookie', $new_password_cookie_hash, time() + 60*60*24*365); // 1 anno
	}
	
	return $result;
}
