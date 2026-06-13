<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if already logged in, send to correct page
if (isset($_SESSION['email'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_page.php");
    } else {
        header("Location: user_page.php");
    }
    exit();
}

$errors = [
    'login'    => $_SESSION['login_error']    ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);

function showerror($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
function isActiveForm($formName, $activeForm) {
    return $formName == $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/newStyles.css">
    <link rel="stylesheet" href="Css/footerstyles.css">
    <link rel="stylesheet" href="Css/responsive.css">
    
    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>LocalLoop</title>
    
</head>
<body>

    <header id="home">

        <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="logoimg">

        <nav class="navbar">
            <a href="index.php">← Back to Home</a>
        </nav>
        
    </header>

<main>

    <div class="login-register-container">

        <div class="container">

            <div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">

                <form action="login_register.php" method="POST">
                    <h2>Login</h2>
                    <?= showerror($errors['login']) ?>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                    <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
                </form>

            </div>

            <div class="form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">

                <form action="login_register.php" method="POST">
                    <h2>Register</h2>
                    <?= showerror($errors['register']) ?>
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    <button type="submit" name="register">Register</button>
                    <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
                </form>

            </div>

        </div>

    </div>
    
</main>

    <script src="JS/script.js"></script>

    <footer class="site-footer">

        <div class="footer-inner">
            <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="footer-logo">
            <p class="footer-tagline">Connecting communities, one listing at a time.</p>
            <p class="footer-copy">© <?= date('Y') ?> LocalLoop. All rights reserved.</p>
        </div>

    </footer>

</body>
</html>
