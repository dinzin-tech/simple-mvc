<?php

namespace Core;

abstract class MigrationBase
{
    abstract public function up(): void;
    abstract public function down(): void;
}
