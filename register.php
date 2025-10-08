<?php 
include 'includes/header.php';

if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    if(empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all required fields';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $result = $auth->register($email, $password, $first_name, $last_name, $phone);
        if($result['success']) {
            // Auto-login after successful registration
            $login_result = $auth->login($email, $password);
            if($login_result['success']) {
                header('Location: dashboard.php');
                exit();
            } else {
                $message = 'Registration successful! Please login to continue.';
            }
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">Register for Access</h2>
            <p class="mt-2 text-black-800">Create your account to access the WOF Training Portal and explore available cohorts</p>
        </div>
        
        <?php if($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" method="POST">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" id="phone" name="phone"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary transition">
                Register for Access
            </button>
            
            <div class="text-center">
                <p class="text-gray-600">Already have an account? 
                    <a href="login.php" class="text-primary hover:underline">Sign in here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>