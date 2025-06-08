<?php

namespace Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column {
    public function __construct(
        public string $type = 'string',
        public int $length = 255,
        public int $precision = 10,
        public int $scale = 2,
        public bool $nullable = true,
        public mixed $default = null,
        public bool $primaryKey = false,
        public bool $autoIncrement = false,
        public bool $unique = false,
        public bool $index = false,
        public array $enum = [],
        public string $foreignKeyTable = '',
        public string $foreignKeyColumn = 'id',
        public string $name = '',
        public string $customSqlType = ''
    ) {}
}