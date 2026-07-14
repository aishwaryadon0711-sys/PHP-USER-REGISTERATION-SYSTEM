<?php
require_once('db_config.php');

$errors = [];
$success = false;
$collegeOptions = ['Panimalar', 'SRM', 'REC'];

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die('Invalid user id.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $college = trim($_POST['college'] ?? '');

    // --- Basic validation ---
    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($mobile === '') {
        $errors[] = 'Mobile number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile number must be exactly 10 digits.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($college === '' || !in_array($college, $collegeOptions, true)) {
        $errors[] = 'Please select a valid college.';
    }

    // --- Check duplicates, excluding this user's own record ---
    if (empty($errors)) {
        $dupStmt = mysqli_prepare($conn, "SELECT email, mobile FROM users WHERE (email = ? OR mobile = ?) AND id != ?");
        mysqli_stmt_bind_param($dupStmt, "ssi", $email, $mobile, $id);
        mysqli_stmt_execute($dupStmt);
        $dupResult = mysqli_stmt_get_result($dupStmt);

        while ($row = mysqli_fetch_assoc($dupResult)) {
            if ($row['email'] === $email) {
                $errors[] = 'This mail id is already registered.';
            }
            if ($row['mobile'] === $mobile) {
                $errors[] = 'This mobile number is already registered.';
            }
        }
        mysqli_stmt_close($dupStmt);
    }

    // --- Update DB if valid ---
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, mobile = ?, email = ?, college = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $mobile, $email, $college, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            if (mysqli_errno($conn) == 1062) {
                $errors[] = 'This mail id or mobile number is already registered.';
            } else {
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // --- Fresh page load: fetch existing record to pre-fill the form ---
    $stmt = mysqli_prepare($conn, "SELECT name, mobile, email, college FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$user) {
        die('User not found.');
    }

    $name = $user['name'];
    $mobile = $user['mobile'];
    $email = $user['email'];
    $college = $user['college'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px 0;
    }
    .card {
        background: #fff;
        padding: 32px 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        width: 320px;
    }
    h2 {
        margin-top: 0;
        text-align: center;
        color: #333;
    }
    label {
        display: block;
        margin-top: 14px;
        margin-bottom: 4px;
        font-size: 14px;
        color: #555;
    }
    input[type="text"], input[type="email"], input[type="tel"], select {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        background: #fff;
    }
    button {
        width: 100%;
        margin-top: 20px;
        padding: 10px;
        background: #2d7ff9;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 15px;
        cursor: pointer;
    }
    button:hover {
        background: #1a63d6;
    }
    .back-btn {
        background: #6c757d;
        margin-top: 10px;
    }
    .back-btn:hover {
        background: #565e64;
    }
    .msg {
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
        font-size: 14px;
        text-align: center;
    }
    .success {
        background: #e3f9e5;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
    }
    .error {
        background: #fdecea;
        color: #b02a37;
        border: 1px solid #f5c6cb;
        text-align: left;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Edit User</h2>

    <?php if (!empty($errors)): ?>
        <div class="msg error">
            <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_user.php?id=<?php echo $id; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" maxlength="10" required>

        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="college">College</label>
        <select id="college" name="college" required>
            <option value="">-- Select College --</option>
            <?php foreach ($collegeOptions as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>"
                    <?php echo ($college === $option) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update</button>
    </form>

    <?php if ($success): ?>
        <div class="msg success" id="successMsg">Record updated successfully!</div>
        <script>
            setTimeout(function () {
                var msg = document.getElementById('successMsg');
                if (msg) {
                    msg.style.display = 'none';
                }
            }, 5000);
        </script>
    <?php endif; ?>                                                                                                                                                       

    <form method="GET" action="view_users.php">
        <button type="submit" class="back-btn">&larr; Back to Grid</button>
    </form>
</div>

</body>
</html>