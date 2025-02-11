create order
<?php
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
ini_set('max_execution_time', 60); // Set max execution time to 60 seconds

if ($_SERVER['SERVER_NAME'] != 'your-production-domain.com') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Database configuration
$host = 'localhost';
$username = 'newnaom-admin_';
$password = '147Abr??@';
$database = 'newnaom-admin_';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_id = $_POST['customer_id'] ?? null;
    $service_id = $_POST['service_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $subcategory_id = $_POST['subcategory_id'] ?? null;
    $price_id = $_POST['price_id'] ?? null;
    $tax_id = $_POST['tax_id'] ?? null;
    $payment_id = $_POST['payment_id'] ?? null;
    $status = $_POST['status'] ?? 'Pending'; // Default to 'Pending'

    if ($customer_id && $service_id && $category_id && $price_id && $payment_id) {
        try {
            // Generate unique 5-digit invoice_id
            do {
                $invoice_id = 'CU' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);

                // Check if the generated invoice_id already exists
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM orders WHERE invoice_id = :invoice_id");
                $stmtCheck->bindParam(':invoice_id', $invoice_id, PDO::PARAM_STR);
                $stmtCheck->execute();

                // If the invoice_id already exists, regenerate it
                $exists = $stmtCheck->fetchColumn() > 0;
            } while ($exists);

            // Fetch price and tax rate
            $stmtPrice = $conn->prepare("SELECT order_price FROM price WHERE id = :price_id");
            $stmtPrice->bindParam(':price_id', $price_id, PDO::PARAM_INT);
            $stmtPrice->execute();
            $price = $stmtPrice->fetchColumn();

            // Handle tax: if no tax selected, default to 0
            $stmtTax = $conn->prepare("SELECT rate FROM taxes WHERE id = :tax_id");
            $stmtTax->bindParam(':tax_id', $tax_id, PDO::PARAM_INT);
            $stmtTax->execute();
            $tax_rate = $stmtTax->fetchColumn() ?? 0; // Default to 0 if no tax is selected

            // Calculate tax and total amounts
            $tax_amount = $price * ($tax_rate / 100);
            $total_amount = $price + $tax_amount;

            // Handle payment method name
            $stmtPayment = $conn->prepare("SELECT method_name FROM payment_methods WHERE id = :payment_id");
            $stmtPayment->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
            $stmtPayment->execute();
            $payment_method = $stmtPayment->fetchColumn();

            $created_at = date('Y-m-d H:i:s');

            // Insert into orders table including invoice_id
            $stmt = $conn->prepare("
                INSERT INTO orders (customer_id, service_id, category_id, subcategory_id, price_id, tax_id, payment_id, status, created_at, tax_rate, tax_amount, order_price, total_amount, invoice_id) 
                VALUES (:customer_id, :service_id, :category_id, :subcategory_id, :price_id, :tax_id, :payment_id, :status, :created_at, :tax_rate, :tax_amount, :order_price, :total_amount, :invoice_id)
            ");
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);
            $stmt->bindParam(':price_id', $price_id, PDO::PARAM_INT);
            $stmt->bindParam(':tax_id', $tax_id, PDO::PARAM_INT);
            $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
            $stmt->bindParam(':tax_rate', $tax_rate, PDO::PARAM_STR);
            $stmt->bindParam(':tax_amount', $tax_amount, PDO::PARAM_STR);
            $stmt->bindParam(':order_price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
            $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_STR); // Bind the invoice_id
            $stmt->execute();

            $success = "Order added successfully with Invoice ID: $invoice_id.";
            header("Location: manage_orders.php");  // Redirect to manage orders page
            exit();
        } catch (PDOException $e) {
            $error = "Error adding order: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch customers, services, categories, price table data
try {
    $customers = $conn->query("SELECT id, name FROM customers")->fetchAll(PDO::FETCH_ASSOC);
    $services = $conn->query("SELECT id, name FROM services")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $conn->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $subcategories = $conn->query("SELECT id, name FROM subcategories")->fetchAll(PDO::FETCH_ASSOC);
    $prices = $conn->query("SELECT id, order_price FROM price")->fetchAll(PDO::FETCH_ASSOC);
    $taxes = $conn->query("SELECT id, rate FROM taxes")->fetchAll(PDO::FETCH_ASSOC);
    $payments = $conn->query("SELECT id, method_name FROM payment_methods")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

include "sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        html, body, h1, h2, h3, h4, h5 { font-family: "Raleway", sans-serif; }
    </style>
</head>
<body>
    <div class="w3-main" style="margin-left:300px;margin-top:43px;">
        <div class="container mt-5">
            <h1 class="mb-4">Create New Order</h1>

            <!-- Display success or error messages -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Order Creation Form -->
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-control" id="customer_id" name="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo $customer['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="service_id" class="form-label">Service</label>
                    <select class="form-control" id="service_id" name="service_id" required>
                        <option value="">Select Service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>"><?php echo $service['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subcategory_id" class="form-label">Subcategory</label>
                    <select class="form-control" id="subcategory_id" name="subcategory_id" required>
                        <option value="">Select Subcategory</option>
                        <?php foreach ($subcategories as $subcategory): ?>
                            <option value="<?php echo $subcategory['id']; ?>"><?php echo $subcategory['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price_id" class="form-label">Price</label>
                    <select class="form-control" id="price_id" name="price_id" required>
                        <option value="">Select Price</option>
                        <?php foreach ($prices as $price): ?>
                            <option value="<?php echo $price['id']; ?>"><?php echo $price['order_price']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tax_id" class="form-label">Tax</label>
                    <select class="form-control" id="tax_id" name="tax_id">
                        <option value="">Select Tax</option>
                        <?php foreach ($taxes as $tax): ?>
                            <option value="<?php echo $tax['id']; ?>"><?php echo $tax['rate']; ?>%</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="payment_id" class="form-label">Payment Method</label>
                    <select class="form-control" id="payment_id" name="payment_id" required>
                        <option value="">Select Payment Method</option>
                        <?php foreach ($payments as $payment): ?>
                            <option value="<?php echo $payment['id']; ?>"><?php echo $payment['method_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Order Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <button type="submit" name="add_order" class="btn btn-primary">Add Order</button>
            </form>
        </div>
    </div>
</body>
</html>
