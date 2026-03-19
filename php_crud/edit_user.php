<?php
// edit_user.php
require_once 'auth.php';
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: view_users.php");
    exit;
}

// Fetch user
$result = mysqli_query($conn, "SELECT * FROM userinfo WHERE user_id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: view_users.php");
    exit;
}
$user = mysqli_fetch_assoc($result);

$errors  = [];
$success = '';

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size      = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $email     = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $phone     = trim(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));
    $address   = trim(mysqli_real_escape_string($conn, $_POST['address'] ?? ''));

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email))     $errors[] = "Email is required.";
    elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    // Check email uniqueness (exclude current user)
    if (empty($errors)) {
        $chk = mysqli_query($conn, "SELECT user_id FROM userinfo WHERE email = '$email' AND user_id != $id");
        if (mysqli_num_rows($chk) > 0) $errors[] = "Email already in use by another user.";
    }

    // Handle image
    $new_image = $user['profile_image']; // keep existing by default

    if (!empty($_FILES['profile_image']['name'])) {
        $file     = $_FILES['profile_image'];
        $filetype = mime_content_type($file['tmp_name']);

        if (!in_array($filetype, $allowed_types)) {
            $errors[] = "Image must be JPG, PNG, GIF, or WEBP.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "Image must be under 2MB.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error. Please try again.";
        } else {
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_image = uniqid('user_', true) . '.' . $ext;
            $dest      = 'uploads/' . $new_image;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Delete old image if exists
                if ($user['profile_image'] && file_exists('uploads/' . $user['profile_image'])) {
                    @unlink('uploads/' . $user['profile_image']);
                }
            } else {
                $errors[] = "Failed to save image. Check uploads/ folder permissions.";
                $new_image = $user['profile_image'];
            }
        }
    }

    // Handle "Remove image" checkbox
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($user['profile_image'] && file_exists('uploads/' . $user['profile_image'])) {
            @unlink('uploads/' . $user['profile_image']);
        }
        $new_image = null;
    }

    if (empty($errors)) {
        $img_val = $new_image ? "'$new_image'" : "NULL";
        $sql = "UPDATE userinfo SET
                    full_name = '$full_name',
                    email     = '$email',
                    phone     = '$phone',
                    address   = '$address',
                    profile_image = $img_val
                WHERE user_id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: view_users.php?success=updated");
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }

    // Reload user with POST values for re-display
    $user['full_name'] = $_POST['full_name'];
    $user['email']     = $_POST['email'];
    $user['phone']     = $_POST['phone'];
    $user['address']   = $_POST['address'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1>✏️ Edit User</h1>
        <a href="view_users.php" class="btn btn-secondary btn-sm">← Back to Users</a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            ⚠️ Please fix the following:<br>
            <ul style="margin:8px 0 0 18px;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Editing: <?= htmlspecialchars($user['full_name']) ?></h2>
            <span class="badge badge-primary">ID #<?= $id ?></span>
        </div>

        <form method="POST" action="edit_user.php?id=<?= $id ?>" enctype="multipart/form-data">

            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name <span style="color:red">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                        value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Profile Photo</label>

                    <?php if ($user['profile_image'] && file_exists('uploads/' . $user['profile_image'])): ?>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                            <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                                 id="current-img"
                                 style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:2px solid #ddd;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#e74c3c;">
                                <input type="checkbox" name="remove_image" value="1" id="remove_img">
                                Remove current photo
                            </label>
                        </div>
                        <label for="profile_image" style="font-size:13px;color:#555;">Replace with new photo:</label>
                    <?php endif; ?>

                    <input type="file" id="profile_image" name="profile_image"
                        accept="image/jpeg,image/png,image/gif,image/webp">
                    <small style="color:#888;font-size:12px;">JPG, PNG, GIF or WEBP · Max 2MB</small>

                    <div class="profile-preview-wrap" id="preview-wrap" style="display:none;">
                        <img id="img-preview" src="" alt="Preview">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-warning">💾 Update User</button>
                <a href="view_users.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('profile_image').addEventListener('change', function () {
    const file  = this.files[0];
    const wrap  = document.getElementById('preview-wrap');
    const img   = document.getElementById('img-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
        reader.readAsDataURL(file);
    } else {
        wrap.style.display = 'none';
    }
});

// If remove checkbox ticked, grey out current image
const removeChk = document.getElementById('remove_img');
if (removeChk) {
    removeChk.addEventListener('change', function () {
        const cImg = document.getElementById('current-img');
        if (cImg) cImg.style.opacity = this.checked ? '0.3' : '1';
    });
}
</script>
</body>
</html>
