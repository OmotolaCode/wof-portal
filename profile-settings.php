<?php
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

$db = new Database();

$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);

        if($first_name && $last_name) {
            $db->query('UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone, updated_at = NOW() WHERE id = :id');
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':phone', $phone);
            $db->bind(':id', $_SESSION['user_id']);

            if($db->execute()) {
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Failed to update profile.";
            }
        } else {
            $error_message = "First name and last name are required.";
        }
    }

    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = 'uploads/profiles/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['profile_image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if(in_array($file_ext, $allowed) && $file['size'] < 5000000) {
            $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext';
            $filepath = $upload_dir . $filename;

            if(move_uploaded_file($file['tmp_name'], $filepath)) {
                $db->query('UPDATE users SET profile_image = :image WHERE id = :id');
                $db->bind(':image', $filename);
                $db->bind(':id', $_SESSION['user_id']);
                $db->execute();

                $success_message = "Profile picture updated successfully!";
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type or file too large (max 5MB).";
        }
    }

    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if($new_password === $confirm_password) {
            $db->query('SELECT password_hash FROM users WHERE id = :id');
            $db->bind(':id', $_SESSION['user_id']);
            $user = $db->single();

            if(password_verify($current_password, $user['password_hash'])) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $db->query('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
                $db->bind(':hash', $new_hash);
                $db->bind(':id', $_SESSION['user_id']);

                if($db->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Failed to change password.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    }
}

$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="dashboard.php" class="text-primary hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
        <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
    </div>

    <?php if($success_message): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if($error_message): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Profile Picture</h2>

            <div class="flex items-center space-x-6">
                <?php if($user['profile_image']): ?>
                    <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>"
                         alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-primary">
                <?php else: ?>
                    <div class="w-32 h-32 bg-primary rounded-full flex items-center justify-center text-white text-4xl font-bold">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="flex-1">
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Upload New Picture</label>
                    <div class="flex items-center gap-4">
                        <input type="file" name="profile_image" accept="image/*"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <button type="submit"
                                class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-upload mr-2"></i>Upload
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">JPG, PNG or GIF. Max size 5MB.</p>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Personal Information</h2>

            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">First Name</label>
                        <input type="text" name="first_name"
                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Last Name</label>
                        <input type="text" name="last_name"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Email Address</label>
                    <input type="email"
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Phone Number</label>
                    <input type="tel" name="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <button type="submit" name="update_profile"
                        class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Change Password</h2>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Current Password</label>
                    <input type="password" name="current_password"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">New Password</label>
                    <input type="password" name="new_password"
                           required minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password"
                           required minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <button type="submit" name="change_password"
                        class="bg-secondary text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                    <i class="fas fa-key mr-2"></i>Change Password
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Account Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Account Status</label>
                    <p class="text-gray-900">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?php if($user['user_status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                            <?php if($user['user_status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                            <?php if($user['user_status'] === 'active'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                            <?php if($user['user_status'] === 'graduated'): ?>bg-purple-100 text-purple-800<?php endif; ?>">
                            <?php echo ucfirst($user['user_status']); ?>
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Account Type</label>
                    <p class="text-gray-900"><?php echo ucfirst($user['user_type']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Member Since</label>
                    <p class="text-gray-900"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Last Updated</label>
                    <p class="text-gray-900"><?php echo date('M j, Y', strtotime($user['updated_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
