<?php

/**
 * Emulates a PEAR database connection which is what the Auth_OpenID
 * library expects to use for its database storage.
 */
class IceBB_OpenID_DBConnection extends Auth_OpenID_DatabaseConnection
{
	function IceBB_OpenID_DBConnection(&$datab)
	{
		$this->db           = &$datab;
	}

	function setFetchMode($mode)
	{
		// Not implemented; returned results will emulate PEAR's
		// DB_FETCHMODE_ASSOC fetch mode.
	}

	function autoCommit($mode)
	{
		// Not implemented.
	}

	function query($sql,$params=array())
	{
		return $this->db->query(vsprintf($sql,$params));
	}

	function getOne($sql,$params=array())
	{
		$result			= $this->db->query(vsprintf($sql,$params));
		if($result === false)
		{
		    return false;
		}
		else {
		    $row		= $this->db->fetch_row($result);
		    $keys		= array_keys($row);
		    return $row[$keys[0]];
		}
	}

	function getRow($sql,$params=array())
	{
		$result			= $this->db->query(vsprintf($sql,$params));
		if($result === false)
		{
		    return false;
		}
		else {
		    return $this->db->fetch_row($result);
		}
	}

	function getAll($sql,$params=array())
	{
		$result			= $this->db->query(vsprintf($sql,$params));
		if($result === false)
		{
		    return false;
		}
		else {
		    $result_rows = array();
		    for ($i = 0; $i < $this->db->get_num_rows($result); $i++) {
		        $result_rows[] = $this->db->fetch_row($result);
		    }
		    return $result_rows;
		}
	}
}

/**
 * IceBB-compatible store implementation - relies on IceBB_OpenID_DBConnection
 */
class IceBB_DB_Store extends Auth_OpenID_MySQLStore
{
	var $conn;

	function IceBB_DB_Store()
	{
		global $db;
		
		$conn			    = new IceBB_OpenID_DBConnection($db);
		parent::Auth_OpenID_MySQLStore(
			$conn,
			'icebb_openid_associations',
			'icebb_openid_nonces'
		);
		$this->conn			= &$conn;
		$this->createTables();
	}
	
	function tableExists($table_name)
	{
		//$q					= @mysql_query($sql, $this->conn->db_link);
		return true;

		//return $this->isError($q);
	}

    /**
     * Returns true if the specified value is considered an error
     * value.  Values returned from database calls will be passed to
     * this function to make decisions.
     */
    function isError($value)
    {
        return $value === false;
    }

	/**
	 * This function is responsible for encoding binary data to make
	 * it safe for use in SQL.
	 */
	function blobEncode($blob)
	{
		return base64_encode($blob);
	}

	/**
	 * Given encoded binary data, this function is responsible for
	 * decoding it from its encoded representation.  Some backends
	 * will not return encoded data, like this one, so no conversion
	 * is necessary.
	 */
	function blobDecode($blob)
	{
		return base64_decode($blob);
	}

    function setSQL()
    {
        parent::setSQL();

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (\n".
            "  server_url BLOB,\n".
            "  handle VARCHAR(255),\n".
            "  secret VARCHAR(2047),\n".
            "  issued INTEGER,\n".
            "  lifetime INTEGER,\n".
            "  assoc_type VARCHAR(64),\n".
            "  PRIMARY KEY (server_url(255), handle)\n".
            ") TYPE=InnoDB";

        $this->sql['create_auth'] =
            "INSERT INTO %s VALUES ('auth_key', '%%s')";

        $this->sql['get_auth'] =
            "SELECT value FROM %s WHERE setting = 'auth_key'";

        $this->sql['set_assoc'] =
            "REPLACE INTO %s VALUES ('%%s', '%%s', '%%s', %%d, %%d, '%%s')";

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = '%%s'";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = '%%s' AND handle = '%%s'";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = '%%s' AND handle = '%%s'";

        $this->sql['add_nonce'] =
            "REPLACE INTO %s (nonce, expires) VALUES ('%%s', %%d)";

        $this->sql['get_nonce'] =
            "SELECT * FROM %s WHERE nonce = '%%s'";

        $this->sql['remove_nonce'] =
            "DELETE FROM %s WHERE nonce = '%%s'";
    }
}
?>
