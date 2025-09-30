<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

$db = new Database();

// Get user applications
$db->query('SELECT a.*, c.name as cohort_name, c.duration_months, c.start_date 
           FROM applications a 
           JOIN cohorts c ON a.cohort_id = c.id 
           WHERE a.user_id = :user_id 
           ORDER BY a.applied_at DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$applications = $db->resultSet();

// Get user enrollments
$db->query('SELECT e.*, c.name as cohort_name, c.duration_months, c.start_date, c.end_date 
           FROM enrollments e 
           JOIN cohorts c ON e.cohort_id = c.id 
           WHERE e.user_id = :user_id 
           ORDER BY e.enrollment_date DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$enrollments = $db->resultSet();

// Get available cohorts for application
$db->query('SELECT * FROM cohorts WHERE status IN ("upcoming", "active") ORDER BY start_date ASC');
$available_cohorts = $db->resultSet();

// Check if user has graduate profile
$db->query('SELECT * FROM graduate_profiles WHERE user_id = :user_id');
$db->bind(':user_id', $_SESSION['user_id']);
$graduate_profile = $db->single();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
        <p class="text-gray-600 mt-2">Manage your training journey and track your progress</p>
    </div>
    
    <!-- Status Alert -->
    <?php if($_SESSION['user_status'] === 'pending'): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-clock mr-2"></i>Welcome! Your account status is currently set to Prospective Student. You can explore our available cohorts, apply to the one that suits you best, and we’ll notify you once your eligibility has been confirmed.
        </div>
    <?php elseif($_SESSION['user_status'] === 'graduated'): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-graduation-cap mr-2"></i>Congratulations! On your graduation. You can now create your professional profile.
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Available Cohorts -->
            <?php if($_SESSION['user_status'] === 'approved' || $_SESSION['user_status'] === 'pending'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Available Cohorts</h2>
                    <div class="grid gap-4">
                        <?php foreach($available_cohorts as $cohort): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($cohort['name']); ?></h3>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($cohort['description']); ?></p>
                                        <div class="flex items-center mt-2 text-sm text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <span><?php echo date('M j, Y', strtotime($cohort['start_date'])); ?></span>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock mr-1"></i>
                                            <span><?php echo $cohort['duration_months']; ?> months</span>
                                        </div>
                                    </div>
                                    <a href="apply.php?cohort_id=<?php echo $cohort['id']; ?>" 
                                       class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Apply Now
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Applications -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">My Applications</h2>
                <?php if(empty($applications)): ?>
                    <p class="text-gray-600">You haven't submitted any applications yet.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($applications as $app): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($app['cohort_name']); ?></h3>
                                        <p class="text-sm text-gray-600">Applied: <?php echo date('M j, Y', strtotime($app['applied_at'])); ?></p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        <?php if($app['status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                                        <?php if($app['status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                                        <?php if($app['status'] === 'rejected'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </div>
                                <?php if($app['admin_notes']): ?>
                                    <div class="mt-2 p-3 bg-gray-50 rounded">
                                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($app['admin_notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Enrollments -->
            <?php if(!empty($enrollments)): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">My Enrollments</h2>
                    <div class="space-y-4">
                        <?php foreach($enrollments as $enrollment): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($enrollment['cohort_name']); ?></h3>
                                        <p class="text-sm text-gray-600">
                                            Enrolled: <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Duration: <?php echo $enrollment['duration_months']; ?> months
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            <?php if($enrollment['status'] === 'completed'): ?>bg-green-100 text-green-800<?php endif; ?>
                                            <?php if($enrollment['status'] === 'enrolled'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                            <?php if($enrollment['status'] === 'dropped'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                        <?php if($enrollment['status'] === 'enrolled'): ?>
                                            <div class="mt-2">
                                                <a href="cohort.php?id=<?php echo $enrollment['cohort_id']; ?>" 
                                                   class="text-primary hover:underline text-sm">
                                                    Access Cohort <i class="fas fa-arrow-right ml-1"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Account Status:</span>
                        <span class="px-2 py-1 rounded text-sm font-medium
                            <?php if($_SESSION['user_status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                            <?php if($_SESSION['user_status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                            <?php if($_SESSION['user_status'] === 'graduated'): ?>bg-blue-100 text-blue-800<?php endif; ?>">
                            <?php echo ucfirst($_SESSION['user_status']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Applications:</span>
                        <span class="font-medium"><?php echo count($applications); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Enrollments:</span>
                        <span class="font-medium"><?php echo count($enrollments); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Graduate Profile -->
            <?php if($_SESSION['user_status'] === 'graduated'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Graduate Profile</h3>
                    <?php if($graduate_profile): ?>
                        <p class="text-green-600 mb-4"><i class="fas fa-check-circle mr-2"></i>Profile Created</p>
                        <a href="profile.php" class="block w-full bg-primary text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                            Edit Profile
                        </a>
                    <?php else: ?>
                        <p class="text-gray-600 mb-4">Create your professional profile to showcase your skills to employers.</p>
                        <a href="profile.php" class="block w-full bg-secondary text-white text-center py-2 rounded-lg hover:bg-green-700 transition">
                            Create Profile
                            <br>
                            <a href="testimonial.php" class="text-secondary hover:underline text-sm">
                                Share Experience <i class="fas fa-star ml-1"></i>
                            </a>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="showcase.php" class="block text-primary hover:underline">
                        <i class="fas fa-star mr-2"></i>Graduate Showcase
                    </a>
                    <a href="profile-settings.php" class="block text-primary hover:underline">
                        <i class="fas fa-cog mr-2"></i>Account Settings
                    </a>
                    <a href="contact.php" class="block text-primary hover:underline">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                    <?php if($_SESSION['user_status'] === 'graduated' || !empty($enrollments)): ?>
                        <a href="testimonial.php" class="block text-primary hover:underline">
                            <i class="fas fa-star mr-2"></i>Share Your Experience
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>