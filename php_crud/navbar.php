<?php
// navbar.php – Shared navigation bar
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a href="dashboard.php" class="brand">🛡️ <span>AdminPanel</span></a>
    <nav>
        <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
            🏠 <span>Dashboard</span>
        </a>
        <a href="add_user.php" class="<?= $current === 'add_user.php' ? 'active' : '' ?>">
            ➕ <span>Add User</span>
        </a>
        <a href="view_users.php" class="<?= $current === 'view_users.php' ? 'active' : '' ?>">
            👥 <span>View Users</span>
        </a>
        <a href="logout.php" class="logout">🚪 <span>Logout</span></a>
    </nav>
</nav>
