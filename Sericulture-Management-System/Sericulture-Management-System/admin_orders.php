<?php
session_start();
include "config.php";

// Only admin can access
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== 'admin') {
    header("Location: admin_login.html");
    exit;
}

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch all orders
$stmt = $conn->query("
    SELECT o.id AS order_id, 
           u.username, 
           u.email, 
           o.city, 
           o.address,
           o.product_name, 
           o.quantity, 
           o.total_amount, 
           o.status, 
           o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f1f3f6; overflow-x: hidden; }
    a { text-decoration: none; }

    /* Navbar */
    nav {
        position: sticky; top: 0; left: 0; width: 100%;
        background: #264653; color: #fff; z-index: 1002;
        padding: 12px 30px; display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    nav h2 { font-size: 22px; font-weight: 700; color: #e9c46a; }
    nav .logout-btn { color: #fff; border: 1px solid #e76f51; padding: 5px 12px; border-radius: 6px; transition: 0.3s; }
    nav .logout-btn:hover { background: #e76f51; color: #fff; }
    #sidebarToggle { border: none; background: transparent; color: #fff; font-size: 1.5rem; }

    /* Layout */
    .dashboard-container {
        display: flex;
        min-height: calc(100vh - 60px);
    }

    /* Sticky Sidebar */
    .sidebar {
        position: sticky;
        top: 100px;
        height: calc(100vh - 70px);
        overflow-y: auto;
        width: 240px;
        background: #264653;
        color: #fff;
        padding: 25px 15px;
        border-radius: 12px;
        box-shadow: 0 6px 25px rgba(0,0,0,0.1);
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            top: 10;
            left: -250px;
            height: 100%;
            z-index: 1001;
        }
        .sidebar.show {
            transform: translateX(250px);
        }
        .dashboard-container {
            flex-direction: column;
        }
    }

    .sidebar h5 {
        text-align: center;
        color: #e9c46a;
        margin-bottom: 25px;
        font-weight: 700;
        font-size: 18px;
    }
    .sidebar .menu a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        color: #fff;
        font-weight: 500;
        transition: 0.3s;
    }
    .sidebar .menu a i { margin-right: 12px; font-size: 1.1rem; }
    .sidebar .menu a:hover { background: rgba(255,255,255,0.15); color: #e9c46a; }

    .main-content {
        flex-grow: 1;
        padding: 25px;
        overflow-y: auto;
        max-height: calc(100vh - 60px);
    }

    .welcome-bar {
        background: linear-gradient(90deg,#198754,#20c997);
        color: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    .accordion-item { border: none; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .accordion-button { font-weight: 600; border-radius: 12px !important; transition: 0.2s; }
    .accordion-button.collapsed { background: #fff; }
    .accordion-header.pending .accordion-button { background: #fff3cd; }
    .accordion-header.completed .accordion-button { background: #d1e7dd; }
    .accordion-header.cancelled .accordion-button { background: #f8d7da; }

    .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; }
    .badge-status { font-size: 0.9rem; padding: 0.45em 0.8em; }
    .order-id-badge { font-size: 0.85rem; }
    .order-actions { text-align: right; }
    .order-actions .btn { margin-left: 5px; transition: transform 0.15s ease; }
    .order-actions .btn:hover { transform: translateY(-2px); }
  </style>
</head>
<body>

<nav>
    <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
    <div>
        <button id="sidebarToggle"><i class="bi bi-list"></i></button>
        <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</nav>

<div class="dashboard-container">
    <div class="sidebar" id="sidebar">
        <h5>Admin Menu</h5>
        <div class="menu">
            <a href="#"><i class="bi bi-house-door"></i> Dashboard</a>
            <a href="#"><i class="bi bi-bag-check"></i> Orders 
                <span class="badge bg-light text-dark"><?= count($orders) ?></span>
            </a>
            <a href="#"><i class="bi bi-clock-history"></i> Pending 
                <span class="badge bg-warning text-dark">
                    <?= count(array_filter($orders, fn($o)=>$o['status']=='pending')) ?>
                </span>
            </a>
            <a href="#"><i class="bi bi-check-circle"></i> Completed 
                <span class="badge bg-success">
                    <?= count(array_filter($orders, fn($o)=>$o['status']=='completed')) ?>
                </span>
            </a>
            <a href="#"><i class="bi bi-x-circle"></i> Cancelled 
                <span class="badge bg-danger">
                    <?= count(array_filter($orders, fn($o)=>$o['status']=='cancelled')) ?>
                </span>
            </a>
            <a href="#"><i class="bi bi-people"></i> Customers</a>
            <a href="#"><i class="bi bi-box-seam"></i> Products</a>
            <a href="#"><i class="bi bi-bar-chart-line"></i> Reports</a>
            <a href="#"><i class="bi bi-gear"></i> Settings</a>
        </div>
    </div>

    <div class="main-content">
        <div class="welcome-bar">
            <h4>Welcome back, <?= e($_SESSION['username']) ?>!</h4>
            <p>Manage orders, track statuses, and handle customer data here.</p>
        </div>

        <div class="accordion" id="ordersAccordion">
        <?php foreach ($orders as $i => $o): 
            $statusClass = $o['status'] === 'pending' ? 'pending' : ($o['status'] === 'completed' ? 'completed' : 'cancelled');
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header <?= $statusClass ?>" id="heading<?= $i ?>">
                    <button class="accordion-button collapsed d-flex justify-content-between align-items-center" 
                            type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapse<?= $i ?>" 
                            aria-expanded="false" 
                            aria-controls="collapse<?= $i ?>">
                        <div>
                            <div class="fw-bold"><i class="bi bi-box-seam"></i> <?= e($o['product_name']) ?></div>
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <?= e($o['username']) ?> |
                                <i class="bi bi-geo-alt"></i> <?= e($o['city'] ?: 'N/A') ?>
                            </small>
                        </div>
                        <span class="badge bg-primary order-id-badge">Order #<?= e($o['order_id']) ?></span>
                    </button>
                </h2>
                <div id="collapse<?= $i ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $i ?>" data-bs-parent="#ordersAccordion">
                    <div class="accordion-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><b>Email:</b> <?= e($o['email']) ?></li>
                            <li class="list-group-item"><b>Quantity:</b> <?= e($o['quantity']) ?></li>
                            <li class="list-group-item"><b>Total:</b> 
                                <span class="badge bg-success">₹<?= e($o['total_amount']) ?></span>
                            </li>
                            <li class="list-group-item"><b>Status:</b>
                                <?php if ($o['status'] === 'pending'): ?>
                                  <span class="badge bg-warning text-dark badge-status"><i class="bi bi-hourglass-split"></i> Pending</span>
                                <?php elseif ($o['status'] === 'completed'): ?>
                                  <span class="badge bg-success badge-status"><i class="bi bi-check-circle"></i> Completed</span>
                                <?php elseif ($o['status'] === 'cancelled'): ?>
                                  <span class="badge bg-danger badge-status"><i class="bi bi-x-circle"></i> Cancelled</span>
                                <?php else: ?>
                                  <span class="badge bg-secondary badge-status"><?= e($o['status']) ?></span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item"><b>Order Date:</b> <?= e($o['created_at']) ?></li>
                            <li class="list-group-item"><b>Full Address:</b> <?= e($o['address'] ?: 'Not provided') ?></li>
                        </ul>
                        <div class="order-actions mt-3">
                            <?php if ($o['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-outline-success update-status" data-id="<?= e($o['order_id']) ?>" data-action="complete">
                                    <i class="bi bi-check2-circle"></i> Mark Completed
                                </button>
                                <button class="btn btn-sm btn-outline-danger update-status" data-id="<?= e($o['order_id']) ?>" data-action="cancel">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-arrow-down"></i> Download Bill</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
});

document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    }
});

document.querySelectorAll(".update-status").forEach(btn => {
  btn.addEventListener("click", function() {
    const orderId = this.getAttribute("data-id");
    const action = this.getAttribute("data-action");

    fetch("update_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `order_id=${orderId}&action=${action}`
    })
    .then(res => res.text())
    .then(data => {
      if (data.trim() === "success") {
        alert("Order updated successfully!");
        location.reload();
      } else {
        alert("Failed to update order!");
      }
    });
  });
});
</script>

</body>
</html>
