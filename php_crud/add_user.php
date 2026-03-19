<?php
// add_user.php
require_once 'auth.php';
require_once 'db.php';

$errors  = [];
$success = '';

// Allowed image types
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size      = 2 * 1024 * 1024; // 2MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $email     = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $phone     = trim(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));
    $address   = trim(mysqli_real_escape_string($conn, $_POST['address'] ?? ''));

    // Validate
    if (empty($full_name))  $errors[] = "Full name is required.";
    if (empty($email))      $errors[] = "Email is required.";
    elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    // Check email uniqueness
    if (empty($errors)) {
        $chk = mysqli_query($conn, "SELECT user_id FROM userinfo WHERE email = '$email'");
        if (mysqli_num_rows($chk) > 0) $errors[] = "Email already exists.";
    }

    // Handle image upload
    $profile_image = null;
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
            $ext           = pathinfo($file['name'], PATHINFO_EXTENSION);
            $profile_image = uniqid('user_', true) . '.' . $ext;
            $dest          = 'uploads/' . $profile_image;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = "Failed to save image. Check uploads/ folder permissions.";
                $profile_image = null;
            }
        }
    }

    // Insert
    if (empty($errors)) {
        $img_val = $profile_image ? "'$profile_image'" : "NULL";
        $sql = "INSERT INTO userinfo (full_name, email, phone, address, profile_image)
                VALUES ('$full_name', '$email', '$phone', '$address', $img_val)";

        if (mysqli_query($conn, $sql)) {
            $success = "User <strong>" . htmlspecialchars($full_name) . "</strong> added successfully!";
            // Clear fields after success
            $full_name = $email = $phone = $address = '';
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1>➕ Add New User</h1>
        <a href="view_users.php" class="btn btn-secondary btn-sm">← Back to Users</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= $success ?> <a href="view_users.php">View all users →</a></div>
    <?php endif; ?>

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
            <h2>User Information</h2>
        </div>

        <form method="POST" action="add_user.php" enctype="multipart/form-data">

            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name <span style="color:red">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                        value="<?= htmlspecialchars($full_name ?? '') ?>"
                        placeholder="e.g. Ram Bahadur Thapa" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        placeholder="e.g. ram@example.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                        value="<?= htmlspecialchars($phone ?? '') ?>"
                        placeholder="e.g. 98XXXXXXXX">
                </div>
                <div class="form-group">
                    <label for="profile_image">Profile Photo</label>
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
                <textarea id="address" name="address"
                    placeholder="Street, City, District..."><?= htmlspecialchars($address ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">💾 Save User</button>
                <a href="view_users.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Live image preview
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
</script>
</body>
</html>
