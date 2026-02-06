<?php
include 'config.php';
session_start();

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

// Proses login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    $query = "SELECT * FROM admin WHERE username = '$username' AND password = MD5('$password')";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $error_msg = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - NewsPedia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a365d;
            --secondary-color: #2a4a7f;
            --accent-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background: linear-gradient(to right, #1a365d, #2a4a7f);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
            text-align: center;
        }

        .login-logo {
            margin-bottom: 20px;
        }

        .login-logo i {
            font-size: 3rem;
            color: var(--accent-color);
        }

        .login-logo h2 {
            color: var(--primary-color);
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-family: inherit;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            font-weight: 500;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: var(--radius);
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-newspaper"></i>
                <h2>NewsPedia Admin</h2>
            </div>

            <?php if (isset($error_msg)): ?>
            <div class="alert">
                <?php echo $error_msg; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            
            <p style="margin-top: 20px; font-size: 0.9rem;">
                <a href="../index.php" style="color: var(--primary-color); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Website
                </a>
            </p>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($koneksi); ?>