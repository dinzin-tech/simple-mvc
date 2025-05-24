<?php
	
	namespace Core;
	
	use PDO;
	
	class Model
	{
		protected $db;
		protected $table;
		
		public function __construct($table)
		{
			$this->db    = Database::getInstance();
			$this->table = $table;
		}
		
		public function getAll()
		{
			$sql  = "SELECT * FROM " . $this->table;
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		
		public function getById($id)
		{
			$sql  = "SELECT * FROM " . $this->table . " WHERE id = :id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		
		public function findBy(array $conditions)
		{
			$sql         = "SELECT * FROM " . $this->table;
			$whereClause = [];
			$params      = [];
			
			foreach ($conditions as $key => $value) {
				$whereClause[] = "$key = ?";
				$params[]      = $value;
			}
			
			if (!empty($whereClause)) {
				$sql .= " WHERE " . implode(' AND ', $whereClause);
			}
			
			$stmt = $this->db->prepare($sql);
			$stmt->execute($params);
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		
		public function create($data)
		{
			$columns      = implode(", ", array_keys($data));
			$placeholders = ":" . implode(", :", array_keys($data));
			$sql          = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
			$stmt         = $this->db->prepare($sql);
			$stmt->execute($data);
			return $this->db->lastInsertId();
		}
		
		public function update($id, $data)
		{
			$sql           = "UPDATE " . $this->table . " SET ";
			$update_fields = [];
			$params        = [];
			
			foreach ($data as $key => $value) {
				$update_fields[] = "$key = :$key";
				$params[$key]    = $value;
			}
			
			$sql          .= implode(", ", $update_fields) . " WHERE id = :id";
			$params['id'] = $id;
			
			$stmt = $this->db->prepare($sql);
			return $stmt->execute($params);
		}
		
		public function delete($id)
		{
			$sql  = "DELETE FROM " . $this->table . " WHERE id = ?";
			$stmt = $this->db->prepare($sql);
			return $stmt->execute([$id]);
		}
	}