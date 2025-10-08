<?php
ob_start();
require_once '../classes/config.php';
require_once '../classes/Auth.php';
$auth = new Auth();
$current_user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
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
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <img src="../images/wof_logo_png.png" alt="Whoba Ogo Foundation" class="h-10 w-auto">
                        <span class="text-xl font-bold text-gray-900"><?php echo APP_NAME; ?></span>
                    </a>
                </div>
                <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-dashboard mr-2"></i>Dashboard 
                </a>
                
                <div class="flex items-center space-x-4">
                    <?php if($auth->isLoggedIn()): ?>
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <div class="relative group">
                            <button class="flex items-center space-x-1 text-gray-700 hover:text-primary" onclick="toggleDropdown('userDropdown')">
                                <i class="fas fa-user-circle"></i>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-lg shadow-xl border invisible group-hover:visible">
                                <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-dashboard mr-2"></i>Dashboard
                                </a>
                                <?php if($_SESSION['user_type'] === 'admin'): ?>
                                    <a href="admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>Admin Panel
                                    </a>
                                <?php endif; ?>
                                <?php if($_SESSION['user_status'] === 'graduated' || $_SESSION['user_status'] === 'enrolled'): ?>
                                    <a href="testimonial.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-star mr-2"></i>Share Experience
                                    </a>
                                <?php endif; ?>
                                <a href="showcase.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-star mr-2"></i>Graduate Showcase
                                </a>
                                <hr class="my-1">
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="showcase.php" class="text-gray-700 hover:text-primary">Graduate Showcase</a>
                        <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Login</a>
                        <a href="register.php" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</body>
</html>