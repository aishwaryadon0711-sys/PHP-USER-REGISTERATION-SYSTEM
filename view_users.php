<?php
require_once('db_config.php');

// --- Handle delete request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $delStmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($delStmt, "i", $deleteId);
    mysqli_stmt_execute($delStmt);
    mysqli_stmt_close($delStmt);

    // Redirect so a page refresh doesn't re-trigger the delete
    header("Location: view_users.php");
    exit;
}

$result = mysqli_query($conn, "SELECT id, name, mobile, email, college, created_at FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registered Users</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 30px;
    }
    .wrap {
        max-width: 1000px;
        margin: 0 auto;
        background: #fff;
        padding: 24px 32px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    h2 {
        margin: 0;
        color: #333;
    }
    a.back-btn {
        display: inline-block;
        padding: 8px 16px;
        background: #2d7ff9;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
    }
    a.back-btn:hover {
        background: #1a63d6;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    th, td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    th {
        background: #f0f3f7;
        color: #555;
    }
    tr:hover {
        background: #fafbfc;
    }
    .empty {
        text-align: center;
        color: #888;
        padding: 20px;
    }
    .actions {
        display: flex;
        gap: 6px;
    }
    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .edit-btn {
        background: #ffc107;
        color: #333;
    }
    .edit-btn:hover {
        background: #e0a800;
    }
    .delete-btn {
        background: #dc3545;
        color: #fff;
    }
    .delete-btn:hover {
        background: #bb2d3b;
    }
</style>
</head>
<body>

<div class="wrap">
    <div class="header-row">
        <h2>Registered Users</h2>
        <a class="back-btn" href="register.php">&larr; Back to Register</a>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>College</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['college']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn edit-btn" href="edit_user.php?id=<?php echo (int) $row['id']; ?>">Edit</a>
                            <form method="POST" action="view_users.php" onsubmit="return confirm('Delete this record?');">
                                <input type="hidden" name="delete_id" value="<?php echo (int) $row['id']; ?>">
                                <button type="submit" class="btn delete-btn">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="empty">No users registered yet.</div>
    <?php endif; ?>
</div>

</body>
</html>