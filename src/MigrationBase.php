<?php

namespace Core;

abstract class MigrationBase
{
    abstract public function up(): void;
    abstract public function down(): void;
    protected function exec(string $sql): void
    {
        // Assuming you have a database connection available
        $db = \Core\Database::getInstance()->getConnection();
        
        if($db->query($sql) === false) {
            throw new \Exception("Migration failed: " . $db->errorInfo()[2]);
        }
    }
}
