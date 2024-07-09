<?php
    class user_Class
    {
        private $table_name = 'user';
        private $dbconn = null;
				private $owner_id = null;
				
				public static function randomPassword() {
				    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
				    $pass = array(); //remember to declare $pass as an array
				    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
				    for ($i = 0; $i < 10; $i++) {
				        $n = rand(0, $alphaLength);
				        $pass[] = $alphabet[$n];
				    }
				    return implode($pass); //turn the array into a string
				}
				
        function __construct($dbconn, $owner_id) {
            $this->dbconn = $dbconn;
						$this->owner_id = $owner_id;
        }

        function create($data, $isHashed = false)
        {		
						 if(!$isHashed){
						 	$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
						 }
						
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,email,password,ftp_user,pg_password,accesslevel,owner_id) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['email'])."','".
             									$data['password']."','".
						 $this->cleanData($data['ftp_user'])."','".
						 $this->cleanData($data['pg_password'])."','".
             $this->cleanData($data['accesslevel'])."',".
						 $this->owner_id.") RETURNING id";

            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {

							# insert user groups
							$values = array();
							foreach($data['groups'] as $group_id){
								array_push($values, "(".$row->id.",".$group_id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$ret = pg_query($this->dbconn, $sql);

              return $row->id;
            }
            return 0;

            //return pg_affected_rows(pg_query($this->dbconn, $sql));
        }

        function getRows()
        {
            $sql ="select * from public." .$this->table_name;
						if($this->owner_id != SUPER_ADMIN_ID){
							$sql .= " WHERE owner_id = ".$this->owner_id;
						}
						$sql .= " ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }

				function getRowsArr(){
						$rv = array();
						$result = $this->getRows();
						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						pg_free_result($result);
            return $rv;
        }

        function getById($id){

            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

				function loginCheck($pwd, $email){

	        $sql ="select * from public.user where email = '".$this->cleanData($email)."'";
	        $result = pg_query($this->dbconn,$sql);
					$row = pg_fetch_object($result);
					if(!$result || pg_num_rows($result) == 0){
 					 return null;
 				 }
					pg_free_result($result);
					
					if (password_verify($pwd, $row->password)) {
						return $row;
					}
	        return null;
				}

				function getByEmail($email){

            $sql ="select * from public.".$this->table_name." where email='".$email."'";
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
							return false;
						}
						
						$row = pg_fetch_object($result);
						pg_free_result($result);
            return $row;
        }
				
       function delete($id)
       {
					 $sql ="delete from public.user_access where user_id='".intval($id)."'";
					 $result = pg_query($this->dbconn, $sql);
					 if(!$result){
						 return false;
					 }
					 pg_free_result($result);

            $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
 						 return false;
 					 }
 					 pg_free_result($result);
					 
					 return true;
       }

       function update($data=array())
       {

          $id = intval($data['id']);
				 	$row = pg_fetch_object($this->getById($id));
					
          $sql = "update public.user set name='".
          				$this->cleanData($data['name'])."'";
					
					if($row->password != $data['password']){	# if password is changed
						$hashpassword = password_hash($data['password'], PASSWORD_DEFAULT);
          	$sql .= ", password='".$hashpassword."'";
					}
					
					if(isset($data['ftp_user'])){
						$sql .= ", ftp_user = '".$this->cleanData($data['ftp_user'])."'";
					}
					
          $sql .= ", accesslevel='".$this->cleanData($data['accesslevel']).
								 	"' where id = '".$id."'";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old groups
						$sql = "delete from public.user_access where user_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert user groups
						$values = array();
						foreach($data['groups'] as $group_id){
							array_push($values, "(".$data['id'].",".$group_id.")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}

					return $rv;
       }
			 
			 function isOwnedByUs($id){
				 
				 if($this->owner_id == SUPER_ADMIN_ID){	// if Super Admin
					 return true;
				 }
				 
				 $sql = "select * from public.".$this->table_name." where id=".$id." and owner_id=".$this->owner_id;
				 $result = pg_query($this->dbconn, $sql);
			 if(!$result){
				 return false;
			 }
			 $rv = (pg_num_rows($result) > 0);
			 pg_free_result($result);
			 return $rv;
			 }
			 
       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
			 
			 static public function create_ftp_user($ftp_user, $email, $hashed_pwd){
		 		$descriptorspec = array(
		 			0 => array("pipe", "r"),
		 		  1 => array("pipe", "w"),
		 		  2 => array("pipe", "w")
		 		);

		 		$process = proc_open('sudo /usr/local/bin/create_ftp_user.sh', $descriptorspec, $pipes, null, null);
		 		
		 		if (is_resource($process)) {
		 			
		 		    fwrite($pipes[0], $ftp_user."\n".$hashed_pwd."\n");
		 		    fclose($pipes[0]);

		 		    //echo stream_get_contents($pipes[1]);
		 		    fclose($pipes[1]);
		 				fclose($pipes[2]);

		 		    // It is important that you close any pipes before calling proc_close in order to avoid a deadlock
		 		    $return_value = proc_close($process);
		 		}
		 	}
			
			static public function update_ftp_user($ftp_user, $hashed_pwd){
			 $descriptorspec = array(
				 0 => array("pipe", "r"),
				 1 => array("pipe", "w"),
				 2 => array("pipe", "w")
			 );

			 $process = proc_open('sudo /usr/local/bin/update_ftp_user.sh', $descriptorspec, $pipes, null, null);
			 
			 if (is_resource($process)) {
				 
					 fwrite($pipes[0], $ftp_user."\n".$hashed_pwd."\n");
					 fclose($pipes[0]);

					 //echo stream_get_contents($pipes[1]);
					 fclose($pipes[1]);
					 fclose($pipes[2]);

					 // It is important that you close any pipes before calling proc_close in order to avoid a deadlock
					 $return_value = proc_close($process);
			 }
		 }
	}
