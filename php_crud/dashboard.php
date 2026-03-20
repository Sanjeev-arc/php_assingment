<?php
// dashboard.php
require_once 'auth.php';
require_once 'db.php';

// Stats
$total_users  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM userinfo"))[0];
$recent_users = mysqli_query($conn, "SELECT * FROM userinfo ORDER BY created_at DESC LIMIT 5");
$today_users  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM userinfo WHERE DATE(created_at) = CURDATE()"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1>Dashboard</h1>
        <span style="font-size:13px;color:#888;"><h2>Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_user']) ?></strong></span><h2>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3><?= $total_users ?></h3>
               <p> Total Users</p>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#27ae60;">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3><?= $today_users ?></h3>
                <p>Added Today</p>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#e67e22;">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3>1</h3>
                <p>Admin Account</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="add_user.php" class="btn btn-primary"> Add New User</a>
            <a href="view_users.php" class="btn btn-success"> View All Users</a>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h2>Recently Added Users</h2>
            <a href="view_users.php" class="btn btn-secondary btn-sm">View All</a>
        </div>

        <?php if (mysqli_num_rows($recent_users) > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = mysqli_fetch_assoc($recent_users)): ?>
                    <tr>
                        <td>
                            <?php if ($u['profile_image'] && file_exists('uploads/' . $u['profile_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($u['profile_image']) ?>" class="user-thumb" alt="photo">
                            <?php else: ?>
                                <div class="user-thumb-placeholder"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                        <td><span class="badge badge-primary"><?= date('M d, Y', strtotime($u['created_at'])) ?></span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon"></div>
                <p>No users yet. <a href="add_user.php">Add your first user</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
