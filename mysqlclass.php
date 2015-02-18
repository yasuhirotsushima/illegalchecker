<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mysqlclass
 *
 * @author Yasuhiro Tsushima
 */

class mysqlclass {
    //put your code here
	public 	$lnk;			// Database Link Connection
	public 	$errno;			// Error Status
	public  $result;			// Query Result Object
	public  $rcnt;			// Effective Data Rows After Query;
        public  $error;                 // Error Messages
        public  $charset = "utf8";      // Default charactor set ( UTF-8 )

        //----------  Constructor  ------------
	public function mysqlclass($DBHOST, $DBUSER, $DBPASS, $DBNAME)
	{
		//echo "CONNECTING Server ".$DBHOST." USER ".$DBUSER." PASSWORD ".$DBPASS." Database ".$DBNAME."<br>\n";
		$this->errno = 0;
		if ($con = mysql_connect($DBHOST, $DBUSER, $DBPASS)){
			$this->lnk = $con;
			if (mysql_select_db($DBNAME, $con)){
				mysql_set_charset($this->charset, $this->lnk);		// for PHP5.2.3, MySQL5.0.7 or later
				//$que = "SET CHARACTER SET ".$this->charset;
				//$this->query($que);					// for PHP and MySQL except avobe
			}else{
				$this->errno = 2;		// DB Select ERROR
                                $this->error = "Failed to connect Database ".$DBNAME;
			}
		}else{
			$this->lnk = null;
			$this->errno = 1;		// CONNECT ERROR
                        $this->error = mysql_error();   
			echo "CONNECT DB ERROR :".$this->error."<br>\n";
		}
	}


	//----------  Cast Query  ----------
	public function query($que)
	{
            $this->result = mysql_query($que, $this->lnk);
            if (!$this->result){
		$this->errno = 3;		// QUERY ERROR
                $this->error = "Query error. please check your SQL.";
                return;
            }
            if ( stristr($que, "SELECT") ){
                if ( !is_bool($this->result) ){
                    $this->rcnt = mysql_num_rows($this->result);
                } else {
                    $this->rcnt = 0;
                }
            }else{
		$this->rcnt = mysql_affected_rows($this->lnk);
            }
            return $this->result;
	}

	public function num_rows($result)
        {
            return $this->getselectrownum( $result );
        }

	public function getselectrownum($result)
	{
		if (isset($result)){
			return mysql_num_rows($result);
		}else{
			return mysql_num_rows($this->result);
		}
	}


        public function fetch_array( $result )
        {
            return $this->getrow( $result );
        }
        
	public function getrow($result)
	{
		if (!isset($result)){
			$row = FALSE;
		}else{
			$row = mysql_fetch_array($this->result);
		}		
		return $row;
	}


	function deleterec($table, $id)
	{
		$que = "DELETE FROM ".$table." WHERE id=".$id;
		 if ( !$this->query($que) ){
                     return FALSE;
                 }else{
                     return TRUE;
                 }
	}

        // Create SQL sentense for INSERT 
	function make_insert_query($table, $vallist)
	{
            $que = "INSERT $table (";
            $keys = "";
            $lst = $this->query("SELECT * FROM ".$table );
            //$lst = mysql_query("SELECT * FROM ".$table, $this->lnk );
            for( $i=0, $cnt = mysql_num_fields($lst); $i<$cnt; $i++){
                    if ($vallist[$i] == ""){
                            $vallist[$i] = "NULL";
                    }
                    if ( $keys != "" ){
                            $keys .= ",";
                            $vals .= ",";
                    }
                    $keys .= mysql_field_name($lst, $i);
                    $type = mysql_field_type($lst, $i);
                    $val = mysql_real_escape_string($vallist[$i]);

                    switch($type){
                            case "string" :
                            case "datetime" :
                            case "blob" :
                            case "text" :
                                    if ($val == 'NULL'){
                                            $val = "";
                                    }
                                    $val = "'".$val."'";
                                    break;
                            default :
                                    break;
                    }
                    $vals .= $val;
            }
            $que .= $keys.") VALUES (".$vals.")";

            return $que;
	}


	function make_update_query($table, $vallist, $keyname, $keyvalue)
	{
		if ( $table == "" || $keyname == "" || $keyvalue == ""){
			$this->errno = 4;
			return "";
		}
		$where = " WHERE $keyname=$keyvalue";
		$keys = "";
		$selectque = "SELECT * FROM ".$table.$where;
		$lst = $this->query($selectque);
		$cnt = mysql_num_fields($lst);
		for( $i=0; $i<$cnt; $i++){
			if ($vallist[$i] == ""){
				continue;
			}
			$val = mysql_real_escape_string($vallist[$i]);
			if ($keys != ""){
				$keys .= ",";
			}

			$typename = mysql_field_type($lst, $i);
			switch($typename){
				case "string" :
				case "date" :
				case "datetime" :
				case "blob" :
				case "text" :
					$val = "'".$val."'";
					break;
				default :
					break;
			}
			$keys .= mysql_field_name($lst, $i)."=".$val;
		}
		$que = "UPDATE $table SET ".$keys.$where; 
		return $que;
	}

	

	//*****  GET COLUMN INFOMATION FROM TABLE  *****
	function get_columns_list($tablename)
	{
		$que = "SHOW COLUMNS FROM ".$tablename;
		$result = mysql_query($que, $this->lnk);
		$ret = array();
		$i = 0;

		while( $row = mysql_fetch_array($result)){
			$ret[$i]['Field'] = $row['Field'];
			$ret[$i]['Type'] = $row['Type'];
			$ret[$i]['Null'] = $row['Null'];
			$ret[$i]['Key'] = $row['Key'];
			$ret[$i]['Default'] = $row['Default'];
		}
		return $ret;
	}
        
        function echotest()
        {
            echo "これはテストのメソッドです。<br />\n";
        }

	//----------  Database Close  ----------
	function close()
	{
		mysql_close($this->lnk);
	}
}
