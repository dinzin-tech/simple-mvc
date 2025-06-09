<?php

    namespace Core;

    use ReflectionClass;
    use ReflectionProperty;

    class Migration {
        private static string $migrationsPath = BASE_PATH_IN_COMMANDS . '/migrations';

        /*public static function run(): void {
            self::ensureMigrationsTableExists();
            $executed = self::getExecutedMigrations();

            $pendingFiles = array_diff(
                scandir(self::$migrationsPath),
                ['.', '..'],
                $executed
            );

            $db = Database::getInstance();

            foreach ($pendingFiles as $file) {
                if (!str_ends_with($file, '.sql')) continue;

                $sql = file_get_contents(self::$migrationsPath . '/' . $file);
                echo "-- Executing: $file\n";
                $db->query($sql);
                $db->query("INSERT INTO migrations (filename) VALUES (:filename)", ['filename' => $file]);
                sleep(1); // Simulate processing time
                echo "-- Migration $file executed successfully.\n";
                
            }
        }*/

        public static function run(): void
        {
            self::ensureMigrationsTableExists();
            $executed = self::getExecutedMigrations();

            $allFiles = array_filter(scandir(self::$migrationsPath), fn($f) => str_ends_with($f, '.php'));
            $pending = array_diff($allFiles, $executed);

            if (empty($pending)) {
                echo "-- No pending migrations.\n";
                return;
            }

            foreach ($pending as $file) {
                $class = self::requireMigrationClass($file);

                if (!is_subclass_of($class, MigrationBase::class)) {
                    echo "-- Skipping $file (not a valid migration class).\n";
                    continue;
                }

                echo "-- Running migration: $file\n";
                (new $class())->up();
                self::recordMigration($file);
                echo "-- Completed: $file\n";
                sleep(1);
            }
        }

        public static function rollback(): void
        {
            self::ensureMigrationsTableExists();

            $lastRun = Database::getInstance()->fetch("SELECT * FROM migrations ORDER BY id DESC LIMIT 1");

            if (!$lastRun) {
                echo "-- No migrations to rollback.\n";
                return;
            }

            $file = $lastRun['filename'];
            $class = self::requireMigrationClass($file);

            echo "-- Rolling back: $file\n";
            (new $class())->down();
            sleep(1); // Simulate processing time
            self::removeMigration($file);
            echo "-- Rolled back: $file\n";
        }

        public static function status(): void
        {
            self::ensureMigrationsTableExists();

            $executed = self::getExecutedMigrations();
            $allFiles = array_filter(scandir(self::$migrationsPath), fn($f) => str_ends_with($f, '.php'));

            echo "=== Migration Status ===\n";

            foreach ($allFiles as $file) {
                echo in_array($file, $executed) ? "[✔] $file\n" : "[ ] $file\n";
            }
        }

        private static function requireMigrationClass(string $file): string
        {
            $path = self::$migrationsPath . '/' . $file;
            require_once $path;

            $className = pathinfo($file, PATHINFO_FILENAME);
            $fqcn = "Migrations\\$className";

            if (!class_exists($fqcn)) {
                throw new \Exception("Migration class $fqcn not found in $file");
            }

            return $fqcn;
        }

        private static function ensureMigrationsTableExists(): void
        {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            SQL;
            Database::getInstance()->query($sql);
        }

        private static function getExecutedMigrations(): array
        {
            $rows = Database::getInstance()->fetchAll("SELECT filename FROM migrations");
            return array_column($rows, 'filename');
        }

        private static function recordMigration(string $filename): void
        {
            Database::getInstance()->query(
                "INSERT INTO migrations (filename) VALUES (:filename)",
                ['filename' => $filename]
            );
        }

        private static function removeMigration(string $filename): void
        {
            Database::getInstance()->query(
                "DELETE FROM migrations WHERE filename = :filename",
                ['filename' => $filename]
            );
        }

        /*public static function generateMigrations(): void {

            echo "-- Scanning models for migration generation...\n";
            $models = self::getModels();
            sleep(1); // Simulate processing time
            if (empty($models)) {
                echo "-- No models found. Please create models in the App/Models directory.\n";
                return;
            }
            echo "-- Found " . count($models) . " models. Generating migration files...\n";

            foreach ($models as $modelClass) {
                if (!class_exists($modelClass)) {
                    echo "-- Model class $modelClass does not exist. Skipping...\n";
                    continue;
                }
                echo "- Processing model: $modelClass\n";
                $sqls = self::generateMigrationSql($modelClass);
                foreach ($sqls as $description => $sql) {
                    $filename = date('Y_m_d_His') . '_' . $description . '.sql';
                    file_put_contents(self::$migrationsPath . '/' . $filename, $sql);
                    echo "-- Generated migration: $filename\n";
                    sleep(1);
                }
            }
        }*/

        public static function generateMigrations(): void {
            echo "-- Scanning models for migration generation...\n";
            $models = self::getModels();
            sleep(1);

            if (empty($models)) {
                echo "-- No models found. Please create models in the App/Models directory.\n";
                return;
            }

            echo "-- Found " . count($models) . " models. Generating migration files...\n";

            foreach ($models as $modelClass) {
                if (!class_exists($modelClass)) {
                    echo "-- Model class $modelClass does not exist. Skipping...\n";
                    continue;
                }

                echo "- Processing model: $modelClass\n";

                $sqls = self::generateMigrationSql($modelClass);
                sleep(1); // Simulate processing time

                foreach ($sqls as $description => [$upSql, $downSql]) {
                    $migrationClassName = $description .'_'. date('YmdHis');
                    $filename = $migrationClassName . '.php';
                    $filepath = self::$migrationsPath . '/' . $filename;

                    $content = <<<PHP
<?php

namespace Migrations;

use Core\MigrationBase;

class $migrationClassName extends MigrationBase {
    public function up(): void {
        \$this->exec(<<<SQL
$upSql
SQL
        );
    }

    public function down(): void {
        \$this->exec(<<<SQL
$downSql
SQL
        );
    }
};
PHP;

                    file_put_contents($filepath, $content);
                    echo "-- Generated migration: $filename\n";
                    sleep(1);
                }
            }
        }


        private static function getModels(): array {
            $modelsDir = BASE_PATH_IN_COMMANDS . '/App/Models/';
            $files = scandir($modelsDir);
            $models = [];

            foreach ($files as $file) {
                if (str_ends_with($file, '.php')) {
                    $models[] = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
                }
            }

            return $models;
        }

        /*private static function generateMigrationSql(string $modelClass): array {
            $instance = new $modelClass();
            $table = $instance->getTable() ?? strtolower((new ReflectionClass($modelClass))->getShortName()) . 's';
            $db = Database::getInstance();

            echo "-- Generating SQL for model: $modelClass, table: $table\n";

            $escapedTable = $db->getConnection()->quote($table);

            $existing = $db->fetch("SHOW TABLES LIKE $escapedTable");
            $sqls = [];

            echo "-- Checking if table $table exists...\n";
            $existing = !empty($existing);
            sleep(1); // Simulate processing time
            echo $existing ? "-- Table $table exists.\n" : "-- Table $table does not exist.\n";

            if (!$existing) {
                // Generate CREATE TABLE
                $columns = ["`id` INT AUTO_INCREMENT PRIMARY KEY"];
                foreach ((new ReflectionClass($modelClass))->getProperties() as $prop) {
                    if ($prop->getName() === 'id' || $prop->getDeclaringClass()->getName() === 'Core\Model' || $prop->isStatic()) continue;
                    [$col, $type] = self::mapPropertyToColumn($prop);
                    $columns[] = "`$col` $type";
                }
                $sqls["create_{$table}_table"] = "CREATE TABLE `$table` (" . implode(", ", $columns) . ");";
            } else {
                // Generate ALTER TABLE
                $existingCols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
                $existingNames = array_column($existingCols, 'Field');

                $modelProps = (new ReflectionClass($modelClass))->getProperties();
                $modelMap = [];
                foreach ($modelProps as $prop) {
                    if ($prop->getName() === 'id' || $prop->getDeclaringClass()->getName() === 'Core\Model' || $prop->isStatic()) continue;
                    [$col, $type] = self::mapPropertyToColumn($prop);
                    $modelMap[$col] = $type;
                }

                $modelNames = array_keys($modelMap);

                foreach (array_diff($modelNames, $existingNames) as $addCol) {
                    $sqls["alter_{$table}_add_{$addCol}_column"] = "ALTER TABLE `$table` ADD `$addCol` {$modelMap[$addCol]};";
                }

                foreach (array_diff($existingNames, $modelNames, ['id']) as $dropCol) {
                    $sqls["alter_{$table}_drop_{$dropCol}_column"] = "ALTER TABLE `$table` DROP COLUMN `$dropCol`;";
                }
            }

            echo "-- Generated " . count($sqls) . " SQL statements for model: $modelClass\n";
            echo "-- SQL statements: " . implode(", ", array_keys($sqls)) . "\n";

            return $sqls;
        }*/

        private static function generateMigrationSql(string $modelClass): array {
            $instance = new $modelClass();
            $table = $instance->getTable() ?? strtolower((new ReflectionClass($modelClass))->getShortName()) . 's';
            $db = Database::getInstance();

            echo "-- Generating SQL for model: $modelClass, table: $table\n";

            $escapedTable = $db->getConnection()->quote($table);
            $exists = $db->fetch("SHOW TABLES LIKE $escapedTable");
            $sqls = [];

            if (!$exists) {
                // -- Create table
                $columns = ["`id` INT AUTO_INCREMENT PRIMARY KEY"];
                $indexes = [];
                $foreignKeys = [];

                foreach ((new ReflectionClass($modelClass))->getProperties() as $prop) {
                    if ($prop->getName() === 'id' || $prop->getDeclaringClass()->getName() === 'Core\Model' || $prop->isStatic()) continue;

                    [$col, $typeDef, $indexDefs, $fk] = self::mapPropertyToColumn($prop);

                    $columns[] = "`$col` $typeDef";
                    $indexes = array_merge($indexes, $indexDefs);
                    if ($fk) $foreignKeys[] = $fk;
                }

                $upSql = "CREATE TABLE `$table` (\n  " . implode(",\n  ", array_merge($columns, $indexes, $foreignKeys)) . "\n) ENGINE=InnoDB;";
                $downSql = "DROP TABLE IF EXISTS `$table`;";

                $sqls["create_{$table}_table"] = [$upSql, $downSql];
            } else {
                // -- Alter table
                $existingCols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
                $existingNames = array_column($existingCols, 'Field');

                $modelProps = (new ReflectionClass($modelClass))->getProperties();
                $modelMap = []; // col => [definition, index, foreign]

                foreach ($modelProps as $prop) {
                    if ($prop->getName() === 'id' || $prop->getDeclaringClass()->getName() === 'Core\Model' || $prop->isStatic()) continue;
                    [$col, $typeDef, $indexDefs, $fk] = self::mapPropertyToColumn($prop);
                    $modelMap[$col] = [$typeDef, $indexDefs, $fk];
                }

                $modelNames = array_keys($modelMap);

                foreach (array_diff($modelNames, $existingNames) as $addCol) {
                    [$type, $indexes, $fk] = $modelMap[$addCol];

                    $up = "ALTER TABLE `$table` ADD `$addCol` $type;";
                    $down = "ALTER TABLE `$table` DROP COLUMN `$addCol`;";

                    $sqls["alter_{$table}_add_{$addCol}_column"] = [$up, $down];

                    // Indexes
                    foreach ($indexes as $idxSql) {
                        $sqls["alter_{$table}_add_index_{$addCol}"] = [
                            "ALTER TABLE `$table` ADD $idxSql;",
                            "ALTER TABLE `$table` DROP INDEX `" . self::extractIndexName($idxSql) . "`;"
                        ];
                    }

                    // Foreign key
                    if ($fk) {
                        $fkName = self::extractConstraintName($fk);
                        $sqls["alter_{$table}_add_fk_{$addCol}"] = [
                            "ALTER TABLE `$table` ADD $fk;",
                            "ALTER TABLE `$table` DROP FOREIGN KEY `$fkName`;"
                        ];
                    }
                }

                foreach (array_diff($existingNames, $modelNames, ['id']) as $dropCol) {
                    $sqls["alter_{$table}_drop_{$dropCol}_column"] = [
                        "ALTER TABLE `$table` DROP COLUMN `$dropCol`;",
                        "-- No rollback SQL (column was removed manually)"
                    ];
                }
            }

            echo "-- Generated " . count($sqls) . " SQL statements for model: $modelClass\n";
            return $sqls;
        }

        private static function extractIndexName(string $indexSql): string {
            if (preg_match('/`(.+?)`/', $indexSql, $matches)) {
                return $matches[1];
            }
            return '';
        }

        private static function extractConstraintName(string $fkSql): string {
            if (preg_match('/CONSTRAINT\s+`(.+?)`/', $fkSql, $matches)) {
                return $matches[1];
            }
            return '';
        }

        /*private static function mapPropertyToColumn(ReflectionProperty $property): array {
            
            $name = $property->getName();
            $type = $property->getType();
            $nullable = 'NULL';
            $sqlType = 'TEXT';

            if ($type) {
                $typeName = $type->getName();
                $nullable = $type->allowsNull() ? 'NULL' : 'NOT NULL';

                switch ($typeName) {
                    case 'int': $sqlType = 'INT'; break;
                    case 'float': $sqlType = 'FLOAT'; break;
                    case 'bool': $sqlType = 'TINYINT(1)'; break;
                    case 'string': $sqlType = 'VARCHAR(255)'; break;
                    case \DateTime::class: $sqlType = 'DATETIME'; break;
                    default: $sqlType = 'TEXT';
                }
            }

            return [$name, "$sqlType $nullable"];
        }*/

        private static function mapPropertyToColumn(ReflectionProperty $property): array {
            $name = $property->getName();
            $type = 'VARCHAR(255)';
            $nullable = 'NULL';
            $default = '';
            $extra = '';
            $indexes = [];

            $attributes = $property->getAttributes(\Core\Attributes\Column::class);
            if (empty($attributes)) return [$name, "$type $nullable", [], null];

            $attr = $attributes[0]->newInstance();

            // Custom column name
            $colName = $attr->name ?: $name;

            // Use custom SQL if provided
            if ($attr->customSqlType) {
                $definition = "$attr->customSqlType";
                if (!$attr->nullable) $definition .= " NOT NULL";
                return [$colName, $definition, [], null];
            }

            // Determine SQL type
            switch ($attr->type) {
                case 'int':
                    $type = 'INT';
                    if ($attr->autoIncrement) $extra .= ' AUTO_INCREMENT';
                    break;
                case 'float':
                case 'double':
                    $type = "DECIMAL({$attr->precision},{$attr->scale})";
                    break;
                case 'bool':
                    $type = 'TINYINT(1)';
                    break;
                case 'string':
                    $type = "VARCHAR({$attr->length})";
                    break;
                case 'text':
                    $type = 'TEXT';
                    break;
                case 'json':
                    $type = 'JSON';
                    break;
                case 'datetime':
                case 'timestamp':
                    $type = 'DATETIME';
                    break;
                case 'enum':
                    $enumVals = array_map(fn($v) => "'$v'", $attr->enum);
                    $type = 'ENUM(' . implode(',', $enumVals) . ')';
                    break;
                case 'set':
                    $setVals = array_map(fn($v) => "'$v'", $attr->enum);
                    $type = 'SET(' . implode(',', $setVals) . ')';
                    break;
            }

            // NULL or NOT NULL
            $nullable = $attr->nullable ? 'NULL' : 'NOT NULL';

            // Default value
            if ($attr->default !== null) {
                if (is_string($attr->default) && !str_starts_with($attr->default, 'CURRENT_')) {
                    $default = " DEFAULT '{$attr->default}'";
                } else {
                    $default = " DEFAULT {$attr->default}";
                }
            }

            // Primary Key
            if ($attr->primaryKey) {
                $extra .= ' PRIMARY KEY';
            }

            // Unique / Indexes
            if ($attr->unique) {
                $indexes[] = "UNIQUE KEY `{$colName}_unique` (`$colName`)";
            }
            if ($attr->index) {
                $indexes[] = "INDEX `{$colName}_index` (`$colName`)";
            }

            // Foreign Key
            $foreignKey = null;
            if ($attr->foreignKeyTable) {
                $fkName = "fk_{$colName}_{$attr->foreignKeyTable}";
                $foreignKey = "CONSTRAINT `$fkName` FOREIGN KEY (`$colName`) REFERENCES `{$attr->foreignKeyTable}`(`{$attr->foreignKeyColumn}`) ON DELETE CASCADE";
            }

            $definition = "$type $nullable$default$extra";
            return [$colName, $definition, $indexes, $foreignKey];
        }


    }
