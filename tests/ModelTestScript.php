<?php

// load the environment variables
require_once __DIR__ . '/../vendor/autoload.php';

use Core\Model;

$user = new class extends Model {
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public string $username;
    public string $email;

};

