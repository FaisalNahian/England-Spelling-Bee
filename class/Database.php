<?php
/* Database Interface
Functions:
	__construct()
		Desc: Constructor, connects to DB
		Vars: 
	query($aQuery)
		Desc: executes query
		Vars: $aQuery - SQL string
*/

class Database
{
	// Database details
	private $dbserver = "localhost";
	private $dbname = "bbad";
	private $dbuser = "root";
	private $dbpassword = "root";
	
	function __construct()
	{
		// When object created, initialise connection; this way, connection 
		// will automatically close  when object destroyed
		mysql_connect($this->dbserver, $this->dbuser, $this->dbpassword);
		mysql_select_db($this->dbname);
	}

	public function query($aQuery)
	{
		return mysql_query($aQuery);
	}

}
?>