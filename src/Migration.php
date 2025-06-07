<?php

    namespace Core;

    use ReflectionClass;
    use ReflectionProperty;

    class Migration {
        private static string $migrationsPath = BASE_PATH_IN_COMMANDS . '/migrations';

        public static function run(): void {
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
                echo "Executing: $file\n";
                $db->query($sql);
                $db->query("INSERT INTO migrations (filename) VALUES (:filename)", ['filename' => $file]);
            }
        }

        private static function ensureMigrationsTableExists(): void {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            SQL;

            Database::getInstance()->query($sql);
        }

        private static function getExecutedMigrations(): array {
            $rows = Database::getInstance()->fetchAll("SELECT filename FROM migrations");
            return array_column($rows, 'filename');
        }

        public static function generateMigrations(): void {

            echo "Scanning models for migration generation...\n";
            $models = self::getModels();
            sleep(1); // Simulate processing time
            if (empty($models)) {
                echo "No models found. Please create models in the App/Models directory.\n";
                return;
            }
            echo "Found " . count($models) . " models. Generating migration files...\n";

            foreach ($models as $modelClass) {
                if (!class_exists($modelClass)) {
                    echo "Model class $modelClass does not exist. Skipping...\n";
                    continue;
                }
                echo "Processing model: $modelClass\n";
                $sqls = self::generateMigrationSql($modelClass);
                foreach ($sqls as $description => $sql) {
                    $filename = date('Y_m_d_His') . '_' . $description . '.sql';
                    file_put_contents(self::$migrationsPath . '/' . $filename, $sql);
                    echo "Generated migration: $filename\n";
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

        private static function generateMigrationSql(string $modelClass): array {
            $instance = new $modelClass();
            $table = $instance->getTable() ?? strtolower((new ReflectionClass($modelClass))->getShortName()) . 's';
            $db = Database::getInstance();

            echo "Generating SQL for model: $modelClass, table: $table\n";

            $existing = $db->fetch("SHOW TABLES LIKE ?", ['table' => $table]);
            $sqls = [];

            if (!$existing) {
                // Generate CREATE TABLE
                $columns = ["`id` INT AUTO_INCREMENT PRIMARY KEY"];
                foreach ((new ReflectionClass($modelClass))->getProperties() as $prop) {
                    if ($prop->getName() === 'id' || $prop->isStatic()) continue;
                    [$col, $type] = self::mapPropertyToColumn($prop);
                    $columns[] = "`$col` $type";
                }
                $sqls["create_{$table}_table"] = "CREATE TABLE `$table` (" . implode(", ", $columns) . ");";
            } else {
                // Generate ALTER TABLE if needed
                $existingCols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
                $existingNames = array_column($existingCols, 'Field');

                $modelProps = (new ReflectionClass($modelClass))->getProperties();
                $modelMap = [];
                foreach ($modelProps as $prop) {
                    if ($prop->getName() === 'id' || $prop->isStatic()) continue;
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

            echo "Generated " . count($sqls) . " SQL statements for model: $modelClass\n";
            echo "SQL statements: " . implode(", ", array_keys($sqls)) . "\n";
            // print_r($sqls);
            // exit(0);

            return $sqls;
        }

        private static function mapPropertyToColumn(ReflectionProperty $property): array {
            echo "Mapping property: " . $property->getName() . "\n";
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
        }
    }
