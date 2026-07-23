<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/api.php';

// Handle Logout
if (isset($_GET['logout'])) {
    
    // 1. Kosongkan semua data dalam $_SESSION (termasuk $_SESSION['token'])
    $_SESSION = array();

    // 2. Paksa hapus Cookie PHPSESSID dari browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    // 3. Hancurkan data session di server
    session_destroy();

    // 4. Redirect ke home.php
    header("Location: home.php");
    exit;
}
$error = null;
$success = null;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $success = "Anda telah berhasil logout.";
    } elseif ($_GET['msg'] === 'registered') {
        $success = "Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.";
    } elseif ($_GET['msg'] === 'registered_mock') {
        $success = "Pendaftaran berhasil (Mode Simulasi - API Offline)! Silakan masuk.";
    }
}

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email dan Password wajib diisi.";
    } else {
        // Send request to API
        $response = apiRequest('POST', '/login', [
            'email' => $email,
            'password' => $password
        ]);

        if ($response['success'] && isset($response['data']['token'])) {
            $_SESSION['token'] = $response['data']['token'];
            $_SESSION['user'] = $response['data']['user'];

            if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                $redirect = $_SESSION['redirect_url'] ?? 'home.php';
                unset($_SESSION['redirect_url']);
                header("Location: " . $redirect);
            }
            exit;
        } else {
            // API Offline / Connection Refused Bypass
            $matchedUser = null;
            
            // 1. Check custom registered users first
            if (isset($_SESSION['mock_registered_users'][$email])) {
                $mockUser = $_SESSION['mock_registered_users'][$email];
                if ($mockUser['password'] === $password) {
                    $matchedUser = $mockUser;
                }
            }
            
            // 2. Fallback to default mock users
            if (!$matchedUser) {
                if ($email === 'admin@smartretail.com' && $password === 'admin123') {
                    $matchedUser = [
                        'nama_pembeli' => 'Admin SmartRetail',
                        'email'        => 'admin@smartretail.com',
                        'role'         => 'admin',
                        'no_hp'        => '081122334455'
                    ];
                } elseif ($email === 'budi@gmail.com' && $password === 'budi123') {
                    $matchedUser = [
                        'nama_pembeli' => 'Budi Santoso',
                        'email' => 'budi@gmail.com',
                        'role' => 'pembeli',
                        'no_hp' => '08123456789'
                    ];
                }
            }

            if ($matchedUser) {
                $_SESSION['token'] = 'mock-token-session-key-12345';
                $_SESSION['user'] = $matchedUser;
                
                // Add mock indicator
                $_SESSION['mock_mode'] = true;

                if ($matchedUser['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php?msg=mock_active");
                } else {
                    $redirect = $_SESSION['redirect_url'] ?? 'home.php?msg=mock_active';
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect);
                }
                exit;
            } else {
                $error = $response['message'] ?? 'Login gagal. Silakan periksa kembali email dan password Anda.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LaptopStore</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .brand-logo {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo span {
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container py-5 d-flex justify-content-center">
    <div class="card login-card p-4 p-md-5">
        <div class="brand-logo">
            <i class="fa-solid fa-laptop text-primary me-2"></i>Laptop<span>Store</span>
        </div>
        
        <h4 class="text-center mb-4 font-weight-bold">Selamat Datang Kembali</h4>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label font-weight-bold text-dark">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label font-weight-bold text-dark">Password</label>
                    <a href="#" class="text-primary text-decoration-none" style="font-size: 0.9rem;">Lupa Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 font-weight-bold btn-tech-primary mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk Akun
            </button>
        </form>
        
        <p class="text-center mb-0 mt-3 text-secondary" style="font-size: 0.95rem;">
            Belum punya akun? <a href="register.php" class="text-primary font-weight-bold text-decoration-none">Daftar Sekarang</a>
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
