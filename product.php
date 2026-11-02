<?php
session_start();

class Product {
    private $id;
    private $name;
    private $category;
    private $description;
    private $price;
    private $stock;
    private $isNew;

    public function __construct($id, $name, $category, $description, $price, $stock, $isNew = false) {
        $this->id = max(1, intval($id));
        $this->name = $name;
        $this->category = $category;
        $this->description = $description;
        $this->price = max(0, floatval($price));
        $this->stock = max(0, intval($stock));
        $this->isNew = (bool)$isNew;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCategory() { return $this->category; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getFormattedPrice() { return '$' . number_format($this->price, 2); }
    public function getStock() { return $this->stock; }
    public function isNew() { return $this->isNew; }

    public function setStock($stock) { $this->stock = max(0, intval($stock)); }
    public function setPrice($price) { $this->price = max(0, floatval($price)); }
    public function setIsNew($isNew) { $this->isNew = (bool)$isNew; }

    public static function getDefaultCategories() {
        return [
            'Writing Tools',
            'Paper Products',
            'Organization Items',
            'Art Gears'
        ];
    }
}

if (!isset($_SESSION['categories'])) {
    $_SESSION['categories'] = Product::getDefaultCategories();
}

if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = [
    ];
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>