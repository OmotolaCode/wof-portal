<?php 
require_once '../config.php';
require_once '../classes/Auth.php';

$auth = new Auth();

// Redirect if already logged in as admin
if($auth->isLoggedIn() && $_SESSION['user_type'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Redirect non-admin users to main site
if($auth->isLoggedIn() && $_SESSION['user_type'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $auth->login($email, $password);
        if($result['success']) {
            if($result['user_type'] === 'admin') {
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Access denied. Admin credentials required.';
                $auth->logout();
            }
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="../images/wof_logo.png">
    <link rel="shortcut icon" type="image/png" href="../images/wof_logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#16a34a',
                        accent: '#dc2626',
                        coral: '#ef4444',
                        emerald: '#10b981'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary via-secondary to-emerald-600 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo and Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-6">
                    <img src="../images/wof_logo.png" alt="Whoba Ogo Foundation" class="h-20 w-auto">
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Admin Portal</h1>
                <p class="text-blue-100">Whoba Ogo Foundation Management System</p>
            </div>
            
            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 text-center">Administrator Login</h2>
                    <p class="text-gray-600 text-center mt-2">Access the foundation management system</p>
                </div>
                
                <?php if($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-primary"></i>Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-primary"></i>Password
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-primary transition-all transform hover:scale-105 font-semibold">
                        <i class="fas fa-sign-in-alt mr-2"></i>Access Admin Portal
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-primary hover:underline text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Main Site
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-8 text-blue-100">
                <p class="text-sm">&copy; <?php echo date('Y'); ?> Whoba Ogo Foundation</p>
                <p class="text-xs mt-1">...touching lives across Africa</p>
            </div>
        </div>
    </div>
</body>
</html>