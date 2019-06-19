<?php
    /*
     * Copyright (C) 2018 Luca Liscio
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU Affero General Public License for more details.
     *
     * You should have received a copy of the GNU Affero General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/agpl-3.0.html>.
     */

    /**
     *  Wrapper per le funzioni dell'interfaccia mysql e delle espressioni regolari 
     *  di php non piu presenti in php7.
     * 
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version v 1.4 2018/07/02 22:30:00
     *  @copyright Copyright 2018 Luca Liscio 
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *   
     *  @filesource
     */
    
    
    //Variabili globali
    $dblink;
    $resultSet;


    if(!function_exists("mysql_connect")){

        // Connessione al db ------------------------------------------------------- 

        function mysql_connect($host, $uname, $passwd){
            global $dblink;
            $dblink = mysqli_connect($host, $uname, $passwd);
            return $dblink;
        }

        function mysql_pconnect($host, $uname, $passwd){
            global $dblink;
            $phost = "p:".$host;
            $dblink = mysqli_connect($phost, $uname, $passwd);
            return $dblink;
        }

        function mysql_select_db($dbname){
            global $dblink;
            $result = mysqli_select_db($dblink, $dbname);
            return $result;
        }


        // Consultazione del DB ------------------------------------------------------

        function mysql_query($query) {
            global $dblink, $resultSet;
            $resultSet = mysqli_query($dblink, $query);
            return $resultSet;
        }

        function mysql_num_rows($resSet){
            return mysqli_num_rows($resSet);
        }

        function mysql_fetch_row($resSet){
            return mysqli_fetch_row($resSet);
        }

        function mysql_fetch_array($resSet){
            return mysqli_fetch_array($resSet);
        }

        function mysql_fetch_assoc($resSet){
            return mysqli_fetch_assoc($resSet);
        }

        function mysql_close(){
            global $dblink;
            return mysqli_close($dblink);
        }

        function mysql_affected_rows(){
            global $dblink;
            return mysqli_affected_rows($dblink);
        }

        function mysql_free_result($resSet){
            mysqli_free_result($resSet);
        }

        function mysql_result ($result , $row , $field){
            $fetch;

            mysqli_data_seek($result, $row);
            if(!empty($field)) {
                while($finfo = mysqli_fetch_field($result)) {
                    if( $field == $finfo->name ) {
                        $f = mysqli_fetch_assoc( $result );
                        $fetch =  $f[ $field ];
                    }
                }
            } else {
                $f = mysqli_fetch_array( $result );
                $fetch = $f[0];
            }

            return $fetch;
        }
    
        function mysql_real_escape_string ($unescaped_string){
            global $dblink;
            return mysqli_real_escape_string ($dblink, $unescaped_string);
        }


        // Informazioni sul dbms ----------------------------------------------------------------

        function mysql_get_server_info(){
            global $dblink;
            return mysqli_get_server_info($dblink);
        }


        // Rilevamneto delgi errori --------------------------------------------------------------

        function mysql_errno() {
            global $dblink;
            return mysqli_errno($dblink);
        }

        function mysql_error() {
            global $dblink;
            return mysqli_error($dblink);
        }

        //Espressioni regolari -------------------------------------------------------------------

        function ereg_replace($pattern, $replacement, $string){
            $newpattern = '/'.$pattern.'/';
            return preg_replace($newpattern, $replacement, $string);
        }

        function ereg($pattern, $string){
            $newpattern = '/'.$pattern.'/m';
            return preg_match_all($newpattern, $string, $matches, PREG_SET_ORDER, 0);
        }  

        function eregi($pattern, $string){
            $newpattern = '/'.$pattern.'/i';
            return preg_match_all($newpattern, $string, $matches, PREG_SET_ORDER, 0);
        }  

    }

    //Funzione per ottenere l'ip reale del client
    function getUserIP()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }
