<?php
ob_start();
require_once 'classes/config.php';
require_once 'classes/Auth.php';
require_once 'includes/cookies.php';
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
    
    <!-- Cookie Consent Styles -->
    <style>
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.95);
            color: white;
            padding: 1rem;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        .graduate-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: perspective(1000px) rotateY(-5deg);
            transition: all 0.3s ease;
        }
        .graduate-card:hover {
            transform: perspective(1000px) rotateY(0deg) translateY(-10px);
        }
        .job-ready-badge {
            background: linear-gradient(45deg, #00c851, #007e33);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
    
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
            <div class="flex justify-between items-center h-25">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <img src="images/wof_logo.png" alt="Whoba Ogo Foundation" class="h-20 w-auto">
                        <!-- <span class="text-xl font-bold text-gray-900"><?php echo APP_NAME; ?></span> -->
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if($auth->isLoggedIn()): ?>
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <div class="relative group">
                            <button class="flex items-center space-x-1 text-gray-700 hover:text-primary">
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
                        <a href="showcase.php" class="text-gray-700 hover:text-primary flex items-center">
                            <i class="fas fa-graduation-cap mr-2 text-primary"></i>Graduate Showcase
                        </a>
                        <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Login</a>
                        <a href="register.php" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Cookie Consent Banner -->
    <?php if(showCookieBanner()): ?>
    <div class="cookie-banner">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <div class="flex-1 mb-4 md:mb-0">
                <h4 class="font-semibold mb-2">🍪 We Value Your Privacy</h4>
                <p class="text-sm text-gray-300">
                    We use cookies to enhance your experience, analyze site traffic, and provide personalized content. 
                    By continuing to use our site, you consent to our use of cookies in accordance with our 
                    <a href="#" class="text-blue-400 hover:underline">Privacy Policy</a>.
                </p>
            </div>
            <div class="flex space-x-3">
                <form method="POST" class="inline">
                    <input type="hidden" name="cookie_action" value="reject">
                    <button type="submit" class="px-4 py-2 text-gray-300 hover:text-white border border-gray-600 rounded-lg transition">
                        Reject All
                    </button>
                </form>
                <form method="POST" class="inline">
                    <input type="hidden" name="cookie_action" value="accept">
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                        Accept All
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>