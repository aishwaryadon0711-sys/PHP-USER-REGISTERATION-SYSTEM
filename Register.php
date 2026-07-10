<?php
require_once('db_config.php');

$errors = [];
$success = false;
$name = $mobile = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');

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

    // --- Check duplicates before insert ---
    if (empty($errors)) {
        if ($email !== '') {
            $emailStmt = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ? LIMIT 1");
            if ($emailStmt) {
                mysqli_stmt_bind_param($emailStmt, "s", $email);
                if (mysqli_stmt_execute($emailStmt)) {
                    mysqli_stmt_store_result($emailStmt);
                    if (mysqli_stmt_num_rows($emailStmt) > 0) {
                        $errors[] = 'This email address is already registered.';
                    }
                } else {
                    $errors[] = 'Unable to verify email. Please try again.';
                }
                mysqli_stmt_close($emailStmt);
            } else {
                $errors[] = 'Unable to verify email. Please try again.';
            }
        }

        if ($mobile !== '') {
            $mobileStmt = mysqli_prepare($conn, "SELECT mobile FROM users WHERE mobile = ? LIMIT 1");
            if ($mobileStmt) {
                mysqli_stmt_bind_param($mobileStmt, "s", $mobile);
                if (mysqli_stmt_execute($mobileStmt)) {
                    mysqli_stmt_store_result($mobileStmt);
                    if (mysqli_stmt_num_rows($mobileStmt) > 0) {
                        $errors[] = 'This mobile number is already registered.';
                    }
                } else {
                    $errors[] = 'Unable to verify mobile number. Please try again.';
                }
                mysqli_stmt_close($mobileStmt);
            } else {
                $errors[] = 'Unable to verify mobile number. Please try again.';
            }
        }
    }

    // --- Insert into DB if valid and no duplicates ---
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, mobile, email) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $mobile, $email);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            $name = $mobile = $email = '';
        } else {
            if (mysqli_errno($conn) == 1062) {
                $errors[] = 'This mobile number or email is already registered.';
            } else {
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
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
    input[type="text"], input[type="email"], input[type="tel"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
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
    .msg {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
        font-size: 14px;
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
    }
</style>
</head>
<body>

<div class="card">
    <h2>Register</h2>

    <?php if ($success): ?>
        <div class="msg success">Registration successful!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg error">
            <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required>

        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <button type="submit">Register</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const messages = document.querySelectorAll('.msg');
    if (messages.length > 0) {
        setTimeout(function () {
            messages.forEach(function (msg) {
                msg.style.transition = 'opacity 0.5s ease';
                msg.style.opacity = '0';
                setTimeout(function () {
                    msg.remove();
                }, 500);
            });
        }, 5000);
    }
});
</script>
</body>
</html>