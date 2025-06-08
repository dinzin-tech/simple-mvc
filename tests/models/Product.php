<?php

use Core\Attributes\Column;
use Core\Model;

class Product extends Model
{

    public function __construct()
    {        
        $this->table = 'products';
        parent::__construct($this->table);
    }

    // #[Column(type: 'int', primaryKey: true, autoIncrement: true)]
    // public int $id;

    #[Column(type: 'string', length: 100, nullable: false, unique: true)]
    public string $sku;

    #[Column(type: 'float', precision: 10, scale: 2)]
    public float $price;

    #[Column(type: 'enum', enum: ['available', 'out_of_stock', 'archived'], default: 'available')]
    public string $status;

    #[Column(type: 'int', foreignKeyTable: 'categories', foreignKeyColumn: 'id')]
    public int $category_id;

    #[Column(type: 'datetime', default: 'CURRENT_TIMESTAMP', nullable: false)]
    public \DateTime $created_at;

    #[Column(type: 'json')]
    public array $metadata;
}
