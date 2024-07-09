<?php
    class access_group_Class
    {
        private $table_name = 'access_groups';
        private $dbconn = null;
				private $owner_id = null;

        function __construct($dbconn, $owner_id) {
            $this->dbconn = $dbconn;
						$this->owner_id = $owner_id;
        }

        function create($data)
        {
            $sql = "INSERT INTO PUBLIC." .$this->table_name." (name,owner_id) ".
							"VALUES('".$this->cleanData($data['name'])."',".$this->owner_id.") RETURNING id";
						$result = pg_query($this->dbconn, $sql);
						if(!$result){
							return 0;
						}
						
            $row = pg_fetch_object($result);
						pg_free_result($result);
						
            if($row) {
							# insert report access
							$values = array();
							foreach($data['userids'] as $user_id){
								array_push($values, "(".$user_id.",".$row->id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$result = pg_query($this->dbconn, $sql);
							if(!$result){
								return 0;
							}
							pg_free_result($result);

              return $row->id;
            }
            return 0;
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

						$sql = "select id,name from public.".$this->table_name;
						if($this->owner_id != SUPER_ADMIN_ID){
							$sql .= " WHERE owner_id = ".$this->owner_id;
						}
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						pg_free_result($result);
            return $rv;
        }

				function getGroupUsers($gids){
						$rv = array();

						$sql = "select id,name from public.user WHERE id in (select user_id from public.user_access where access_group_id in (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }
				
				function getGroupMapGroups($gids){
						$rv = array();

						$sql = "select id,name from public.map WHERE id in (SELECT map_id from public.map_access where access_group_id IN (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }
				
				function getByUserId($user_id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.user_access where user_id='".intval($user_id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }
				
        function getGroupById($id){
            $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }
				
				function getGroupByName($name){
            $sql ="select * from public." .$this->table_name . " where name='".$name."'";
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
							return false;
						}
						$row = pg_fetch_assoc($result);
						pg_free_result($result);
						return $row;
        }

       function delete($id){

				 $sql ="delete from public.user_access where access_group_id='".intval($id)."'";
				 if($this->owner_id != SUPER_ADMIN_ID){
					 $sql .= " AND owner_id = ".$this->owner_id;
				 }
				 
				 $result = pg_query($this->dbconn, $sql);
				 if(pg_affected_rows($result) >= 0){
					 pg_free_result($result);
					 
					 $sql ="delete from public.map_access where access_group_id='".intval($id)."'";
					 if($this->owner_id != SUPER_ADMIN_ID){
						 $sql .= " AND owner_id = ".$this->owner_id;
					 }
					 
					 $result = pg_query($this->dbconn, $sql);
					 if(pg_affected_rows($result) >= 0){
						 pg_free_result($result);
					 
						$sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
						$result = pg_query($this->dbconn, $sql);
						$rv = (pg_affected_rows($result) >= 0);
						pg_free_result($result);
						
						return $rv;
					}
				}
				return false;
       }

       function update($data=array()) {
          $sql = "update public.access_groups set name='".$this->cleanData($data['name'])."' where id = '".intval($data['id'])."' ";
          $rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){

						$sql ="delete from public.user_access where access_group_id='".intval($data['id'])."'";
	 				 	$rv = pg_query($this->dbconn, $sql);

						# insert report access
						$values = array();

						foreach($data['userids'] as $user_id){
							array_push($values, "(".$user_id.",".$data['id'].")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
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
	}
