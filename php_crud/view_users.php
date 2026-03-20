<?php
// view_users.php
require_once 'auth.php';
require_once 'db.php';

$success = $_GET['success'] ?? '';
$search  = trim(mysqli_real_escape_string($conn, $_GET['search'] ?? ''));

// Build query
if ($search) {
    $sql = "SELECT * FROM userinfo
            WHERE full_name LIKE '%$search%'
               OR email LIKE '%$search%'
               OR phone LIKE '%$search%'
            ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM userinfo ORDER BY created_at DESC";
}

$result = mysqli_query($conn, $sql);
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1> All Users <span class="badge badge-primary"></span></h1>
        <a href="add_user.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add User</a>
    </div>

    <?php if ($success === 'deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> User deleted successfully.</div>
    <?php elseif ($success === 'updated'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> User updated successfully.</div>
    <?php endif; ?>

    <!-- Search -->
   

    <div class="card" style="padding:0;overflow:hidden;">
        <?php if ($total > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($u = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <?php if ($u['profile_image'] && file_exists('uploads/' . $u['profile_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($u['profile_image']) ?>"
                                     class="user-thumb" alt="photo">
                            <?php else: ?>
                                <div class="user-thumb-placeholder">👤</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($u['address'] ?: '—') ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit_user.php?id=<?= $u['user_id'] ?>"
                                   class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete_user.php?id=<?= $u['user_id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmDelete('<?= htmlspecialchars(addslashes($u['full_name'])) ?>')">
                                   <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon"><?= $search ? '<i class="fas fa-search"></i>' : '<i class="fas fa-inbox"></i>' ?></div>
                <p>
                    <?php if ($search): ?>
                        No users found matching "<strong><?= htmlspecialchars($search) ?></strong>".
                        <a href="view_users.php">Show all</a>
                    <?php else: ?>
                        No users yet. <a href="add_user.php">Add your first user</a>.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(name) {
    return confirm('<i class="fas fa-exclamation-triangle"></i> Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.');
}
</script>
</body>
</html>
