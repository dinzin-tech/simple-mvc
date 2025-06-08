<?php
	
	namespace Core;

    use PDO;
    use PDOException;
	
	class Migrations_old
	{
		private $db;
		
		public function __construct()
		{
			$this->db = Database::getInstance();
		}
		
		public function migrate($migrationFile)
		{
			$sql     = file_get_contents($migrationFile);
			$queries = explode(';', $sql);
			
			foreach ($queries as $query) {
				$query = trim($query);
				if (empty($query)) {
					continue;
				}
				
				try {
					$this->db->exec($query);
					echo "Query executed: $query\n";
				} catch (PDOException $e) {
					echo "Error executing query: " . $e->getMessage() . "\n";
					return false;
				}
			}
			
			echo "Migration completed successfully.\n";
			return true;
		}
		
	}