<?php
require_once 'Product.php';

$products = array_map('unserialize', $_SESSION['products']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_product') {
            $maxId = 0;
            foreach ($products as $p) {
                if ($p->getId() > $maxId) {
                    $maxId = $p->getId();
                }
            }
            $newId = $maxId + 1;
            
            $category = trim($_POST['category']);
            $newCategory = trim($_POST['new_category'] ?? '');
            
            if ($category === '__NEW__' && !empty($newCategory)) {
                $category = $newCategory;
                if (!in_array($category, $_SESSION['categories'])) {
                    $_SESSION['categories'][] = $category;
                }
            } elseif ($category === '__NEW__' && empty($newCategory)) {
                $category = 'Uncategorized';
            }

            $price = max(0, floatval($_POST['price']));
            $stock = max(0, intval($_POST['stock']));
            $isNew = isset($_POST['is_new']) ? true : false;
            $newProduct = new Product($newId, $_POST['name'], $category, $_POST['description'], $price, $stock, $isNew);
            $_SESSION['products'][] = serialize($newProduct);
        } 
        elseif ($action === 'edit_product') {
            $id = max(1, intval($_POST['id']));
            $price = max(0, floatval($_POST['price']));
            $stock = max(0, intval($_POST['stock']));
            foreach ($_SESSION['products'] as $key => $pStr) {
                $p = unserialize($pStr);
                if ($p->getId() === $id) {
                    $p->setPrice($price);
                    $p->setStock($stock);
                    $p->setIsNew(isset($_POST['is_new']));
                    $_SESSION['products'][$key] = serialize($p);
                    break;
                }
            }
        } 
        elseif ($action === 'delete_product') {
            $id = max(1, intval($_POST['id']));
            foreach ($_SESSION['products'] as $key => $pStr) {
                $p = unserialize($pStr);
                if ($p->getId() === $id) {
                    unset($_SESSION['products'][$key]);
                    $_SESSION['products'] = array_values($_SESSION['products']);
                    break;
                }
            }
        } 
        elseif ($action === 'add_to_cart') {
            $id = max(1, intval($_POST['id']));
            $qty = max(1, intval($_POST['quantity']));
            foreach ($products as $p) {
                if ($p->getId() === $id) {
                    if ($qty > $p->getStock()) {
                        $qty = $p->getStock();
                    }
                    break;
                }
            }
            if ($qty > 0) {
                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] += $qty;
                } else {
                    $_SESSION['cart'][$id] = $qty;
                }
            }
        } 
        elseif ($action === 'clear_cart') {
            $_SESSION['cart'] = [];
        } 
        elseif ($action === 'confirm_cart') {
            $_SESSION['show_receipt'] = true;
            $_SESSION['receipt_data'] = [];
            foreach ($_SESSION['cart'] as $id => $qty) {
                foreach ($_SESSION['products'] as $key => $pStr) {
                    $p = unserialize($pStr);
                    if ($p->getId() === $id) {
                        $newStock = max(0, $p->getStock() - $qty);
                        $p->setStock($newStock);
                        $_SESSION['products'][$key] = serialize($p);
                        $_SESSION['receipt_data'][] = [
                            'id' => $p->getId(),
                            'name' => $p->getName(),
                            'category' => $p->getCategory(),
                            'description' => $p->getDescription(),
                            'price' => $p->getPrice(),
                            'quantity' => $qty,
                            'total' => $p->getPrice() * $qty
                        ];
                    }
                }
            }
            $_SESSION['cart'] = [];
        }
        header("Location: Index.php");
        exit;
    }
}

$products = array_map('unserialize', $_SESSION['products']);

$categoryFilter = $_GET['category'] ?? 'All';
$statusFilter = $_GET['status'] ?? 'All';

$filteredCatalog = array_filter($products, function($p) use ($categoryFilter, $statusFilter) {
    $catMatch = ($categoryFilter === 'All' || $p->getCategory() === $categoryFilter);
    $statusMatch = true;
    if ($statusFilter === 'New') {
        $statusMatch = $p->isNew();
    } elseif ($statusFilter === 'Standard') {
        $statusMatch = !$p->isNew();
    }
    return $catMatch && $statusMatch;
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>School Supplies Product Management and Pricing System</title>
</head>
<body>

    <h1>School Supplies Product Management and Pricing System</h1>

    <?php if (isset($_SESSION['show_receipt']) && $_SESSION['show_receipt']): ?>
        <h2>Receipt / Order Confirmation</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Calculated Price</th>
            </tr>
            <?php 
            $grandTotal = 0;
            foreach ($_SESSION['receipt_data'] as $item): 
                $grandTotal += $item['total'];
            ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['category']); ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td><?php echo '$' . number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo '$' . number_format($item['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="6"><strong>Grand Total</strong></td>
                <td><strong><?php echo '$' . number_format($grandTotal, 2); ?></strong></td>
            </tr>
        </table>
        <?php 
        unset($_SESSION['show_receipt']);
        unset($_SESSION['receipt_data']);
        ?>
        <hr>
    <?php endif; ?>

    <h2>Item Management</h2>

    <h3>Add New Item</h3>
    <form method="POST">
        <input type="hidden" name="action" value="add_product">
        
        <label>Category:</label>
        <select name="category" required>
            <?php foreach ($_SESSION['categories'] as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
            <option value="__NEW__">+ Add New Category</option>
        </select>
        
        <label>New Category Name (if selected besides):</label>
        <input type="text" name="new_category" placeholder="Enter new category name">

        <br><br>

        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Description:</label>
        <input type="text" name="description" required>

        <label>Price ($):</label>
        <input type="number" step="0.01" min="0" name="price" required>

        <label>Stock:</label>
        <input type="number" min="0" name="stock" required>

        <label>New Item Status:</label>
        <input type="checkbox" name="is_new" value="1">

        <button type="submit">Add Product</button>
    </form>

    <h3>Edit Item</h3>
    <form method="POST">
        <input type="hidden" name="action" value="edit_product">
        <label>Product ID:</label>
        <input type="number" min="1" name="id" required>
        <label>New Price ($):</label>
        <input type="number" step="0.01" min="0" name="price" required>
        <label>New Stock:</label>
        <input type="number" min="0" name="stock" required>
        <label>Is New Product:</label>
        <input type="checkbox" name="is_new" value="1">
        <button type="submit">Update Product</button>
    </form>

    <h3>Delete Item</h3>
    <form method="POST">
        <input type="hidden" name="action" value="delete_product">
        <label>Product ID:</label>
        <input type="number" min="1" name="id" required>
        <button type="submit">Delete Product</button>
    </form>

    <hr>

    <h2>Product Catalog</h2>

    <form method="GET">
        <label>Sort/Filter by Category:</label>
        <select name="category">
            <option value="All" <?php echo $categoryFilter === 'All' ? 'selected' : ''; ?>>All Categories</option>
            <?php foreach ($_SESSION['categories'] as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>

        <label>Filter by New Products:</label>
        <select name="status">
            <option value="All" <?php echo $statusFilter === 'All' ? 'selected' : ''; ?>>All Products</option>
            <option value="New" <?php echo $statusFilter === 'New' ? 'selected' : ''; ?>>New Products Only</option>
            <option value="Standard" <?php echo $statusFilter === 'Standard' ? 'selected' : ''; ?>>Standard Products Only</option>
        </select>

        <button type="submit">Filter Catalog</button>
    </form>
    <br>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Add to Cart</th>
        </tr>
        <?php foreach ($filteredCatalog as $p): ?>
        <tr>
            <td><?php echo $p->getId(); ?></td>
            <td><?php echo htmlspecialchars($p->getName()); ?></td>
            <td><?php echo htmlspecialchars($p->getCategory()); ?></td>
            <td><?php echo $p->isNew() ? 'New Product' : 'Regular Product'; ?></td>
            <td><?php echo htmlspecialchars($p->getDescription()); ?></td>
            <td><?php echo $p->getFormattedPrice(); ?></td>
            <td><?php echo $p->getStock(); ?></td>
            <td>
                <?php if ($p->getStock() > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="id" value="<?php echo $p->getId(); ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $p->getStock(); ?>" required>
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    Out of Stock
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <hr>

    <h2>Shopping Cart</h2>
    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Calculated Price</th>
            </tr>
            <?php 
            $cartTotal = 0;
            foreach ($_SESSION['cart'] as $cId => $cQty):
                foreach ($products as $p) {
                    if ($p->getId() === $cId) {
                        $subtotal = $p->getPrice() * $cQty;
                        $cartTotal += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo $p->getId(); ?></td>
                            <td><?php echo htmlspecialchars($p->getName()); ?></td>
                            <td><?php echo $p->getFormattedPrice(); ?></td>
                            <td><?php echo $cQty; ?></td>
                            <td><?php echo '$' . number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php
                        break;
                    }
                }
            endforeach; 
            ?>
            <tr>
                <td colspan="4"><strong>Total Calculated Price</strong></td>
                <td><strong><?php echo '$' . number_format($cartTotal, 2); ?></strong></td>
            </tr>
        </table>
        <br>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="confirm_cart">
            <button type="submit">Confirm Cart & Checkout</button>
        </form>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="clear_cart">
            <button type="submit">Clear Cart</button>
        </form>
    <?php endif; ?>

</body>
</html>