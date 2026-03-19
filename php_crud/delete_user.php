<?php
// delete_user.php
require_once 'auth.php';
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: view_users.php");
    exit;
}

// Fetch the user to get the image filename before deleting
$result = mysqli_query($conn, "SELECT profile_image FROM userinfo WHERE user_id = $id LIMIT 1");

if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Delete from DB
    if (mysqli_query($conn, "DELETE FROM userinfo WHERE user_id = $id")) {
        // Delete image file if it exists
        if ($user['profile_image'] && file_exists('uploads/' . $user['profile_image'])) {
            @unlink('uploads/' . $user['profile_image']);
        }
        header("Location: view_users.php?success=deleted");
        exit;
    } else {
        header("Location: view_users.php?error=dberror");
        exit;
    }
} else {
    header("Location: view_users.php");
    exit;
}
?>
