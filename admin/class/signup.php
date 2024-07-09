<?php
    class signup_Class
    {
        private $table_name = 'signup';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {		 
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,email,password,verify) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['email'])."','".
             									$data['password']."','".
						 $this->cleanData($data['verify'])."'".
             ") RETURNING id";

						$result = pg_query($this->dbconn, $sql);
						if(!$result){
							return 0;
						}
						
            $row = pg_fetch_object($result);
						pg_free_result($result);
            if($row) {
              return $row->id;
            }
            return 0;
        }

        function getRows(){
          $sql ="select * from public." .$this->table_name." ORDER BY id DESC";
          return pg_query($this->dbconn, $sql);
        }

        function getById($id){
          $sql ="select * from public." .$this->table_name . "
          where id='".intval($id)."'";
          return pg_query($this->dbconn, $sql);
        }

       function delete($id){
      		$sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
          $result = pg_query($this->dbconn, $sql);
					if($result){
						$rv = (pg_affected_rows($result) > 0);
						pg_free_result($result);
						return $rv;
					}else{
						return false;
					}
       }
			 
			 function verify($id, $verify){
				 $sql ="select * from public.".$this->table_name." where id='".intval($id)."' AND verify='".$verify."'";
				 return pg_query($this->dbconn, $sql);
			 }

       function cleanData($val){
         return pg_escape_string($this->dbconn, $val);
       }
	}