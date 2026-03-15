<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Core\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    protected $dbMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(\Core\Database::class);
    }

    public function testToSqlBasicSelect(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->table('users')->select('id', 'name', 'email')->toSql();
        $this->assertEquals('SELECT id, name, email FROM users', $sql);
    }

    public function testToSqlWhereClause(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->table('users')
                  ->where('status', 'active')
                  ->where('age', '>', 18)
                  ->toSql();
        
        $this->assertEquals('SELECT * FROM users WHERE status = :status_0 AND age > :age_1', $sql);
    }

    public function testToSqlOrWhereClause(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->table('posts')
                  ->where('published', 1)
                  ->orWhere('author_id', 5)
                  ->toSql();
        
        $this->assertEquals('SELECT * FROM posts WHERE published = :published_0 OR author_id = :author_id_1', $sql);
    }

    public function testToSqlOrderByAndLimit(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->table('logs')
                  ->orderBy('created_at', 'DESC')
                  ->limit(10, 20)
                  ->toSql();
        
        $this->assertEquals('SELECT * FROM logs ORDER BY created_at DESC LIMIT 10 OFFSET 20', $sql);
    }

    public function testToSqlJoins(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->table('users')
                  ->select('users.id', 'profiles.bio')
                  ->join('profiles', 'users.id', '=', 'profiles.user_id')
                  ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
                  ->toSql();
        
        $this->assertEquals('SELECT users.id, profiles.bio FROM users INNER JOIN profiles ON users.id = profiles.user_id LEFT JOIN posts ON users.id = posts.user_id', $sql);
    }

    public function testRawSqlExecution(): void
    {
        $qb = new QueryBuilder($this->dbMock);
        $sql = $qb->raw('SELECT * FROM special_view WHERE active = :active', ['active' => 1])->toSql();
        
        $this->assertEquals('SELECT * FROM special_view WHERE active = :active', $sql);
    }
}
