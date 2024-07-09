<?php
    class map_Class
    {
        private $table_name = 'map';
        private $dbconn = null;
				private $owner_id = null;

        function __construct($dbconn, $owner_id) {
            $this->dbconn = $dbconn;
						$this->owner_id = $owner_id;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,description,owner_id) "."VALUES('".
             $this->cleanData($data['name'])."','".
						 $this->cleanData($data['description'])."',".$this->owner_id.") RETURNING id";
						 
						 $result = pg_query($this->dbconn, $sql);
						 if(!$result){
 							return 0;
 						}
 						
 						$row = pg_fetch_object($result);
 						pg_free_result($result);

            if($row) {
								# insert into access groups
								$values = array();
								foreach($data['accgrps'] as $group_id){
									array_push($values, "(".$group_id.",".$row->id.")");
								}

								$sql = "insert into public.map_access (access_group_id,map_id) values ".implode(',', $values);
								$ret = pg_query($this->dbconn, $sql);

                return $row->id;
            }
            return 0;

            //return pg_affected_rows(pg_query($this->dbconn, $sql));
        }

        function getRows(){
					$sql ="select * from public." .$this->table_name;
					if($this->owner_id != SUPER_ADMIN_ID){
						$sql .= " WHERE owner_id = ".$this->owner_id;
					}
					$sql .= " ORDER BY id DESC";
          return pg_query($this->dbconn, $sql);
        }

        function getById($id){
            $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }
				
				function getBy($k, $v){
						$sql ="select * from public." .$this->table_name . " where ".$k."='".$v."'";
            return pg_query($this->dbconn, $sql);
        }

				function getAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.map_access where map_id='".intval($id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						return $rv;
				}
				
				function chainExec($sqls){
					for($i=0; $i < count($sqls); $i++){
						$result = pg_query($this->dbconn, $sqls[$i]);
						if(!$result) {
							return false;
						}
						
						$success = (pg_affected_rows($result) >= 0);
						pg_free_result($result);
						if(!$success){
							return false;
						}
					}
					return true;
				}
				
       function delete($id)
       {
						$sqls = array(
							"delete from public.map_access where map_id='".intval($id)."'",
							"delete from public.map_pglink where map_id='".intval($id)."'",
							"delete from public.map_gslink where map_id='".intval($id)."'",
            	"delete from public." .$this->table_name . " where id='".intval($id)."'");
            return $this->chainExec($sqls);
       }

       function update($data)
       {
				 	# insert access groups
					$values = array();
					foreach($data['accgrps'] as $group_id){
						array_push($values, "(".$group_id.",".$data['id'].")");
					}
					
					$pgl = array(); $gsl = array();
					foreach($data as $k => $v){
						if(str_starts_with($k, 'data_type')){
							$dsi = substr($k, 9);
							
										if($data['data_type'.$dsi] == 'pg'){	array_push($pgl, $data['pglink_id'.$dsi]);
							}else if($data['data_type'.$dsi] == 'gs'){	array_push($gsl, $data['gslink_id'.$dsi]);
							}
						}
					}
					
					$sqls = array(
          	"update public.".$this->table_name." set name='".
									$this->cleanData($data['name'])."', description='".
									$this->cleanData($data['description'])."' where id = '".intval($data['id'])."' ",
						"delete from public.map_access where map_id=".$data['id'],
						"insert into public.map_access (access_group_id,map_id) values ".implode(',', $values),
						"delete from public.map_pglink where map_id=".$data['id'],
						"delete from public.map_gslink where map_id=".$data['id']
					);
					
					if(count($pgl) > 0){
						$values = array();
						foreach(array_unique($pgl) as $pglink_id){
							array_push($values, "(".$data['id'].",".$pglink_id.")");
						}
						array_push($sqls, "insert into public.map_pglink (map_id,pglink_id) values ".implode(',', $values));
					}

					if(count($gsl) > 0){
						$values = array();
						foreach(array_unique($gsl) as $gslink_id){
							array_push($values, "(".$data['id'].",".$sglink_id.")");
						}
						array_push($sqls, "insert into public.map_gslink (map_id,gslink_id) values ".implode(',', $values));
					}
						
					
					return $this->chainExec($sqls);
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
