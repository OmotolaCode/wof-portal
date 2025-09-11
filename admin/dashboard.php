<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();

// Get statistics
$db->query('SELECT COUNT(*) as count FROM users WHERE user_type = "student"');
$total_students = $db->single()['count'];

$db->query('SELECT COUNT(*) as count FROM applications WHERE status = "pending"');
$pending_applications = $db->single()['count'];

$db->query('SELECT COUNT(*) as count FROM enrollments WHERE status = "enrolled"');
$active_enrollments = $db->single()['count'];

$db->query('SELECT COUNT(*) as count FROM users WHERE user_type = "graduate"');
$total_graduates = $db->single()['count'];

// Get recent applications
$db->query('SELECT a.*, u.first_name, u.last_name, u.email, c.name as cohort_name 
           FROM applications a 
           JOIN users u ON a.user_id = u.id 
           JOIN cohorts c ON a.cohort_id = c.id 
           WHERE a.status = "pending" 
           ORDER BY a.applied_at DESC 
           LIMIT 10');
$recent_applications = $db->resultSet();

// Get recent enrollments
$db->query('SELECT e.*, u.first_name, u.last_name, c.name as cohort_name 
           FROM enrollments e 
           JOIN users u ON e.user_id = u.id 
           JOIN cohorts c ON e.cohort_id = c.id 
           ORDER BY e.enrollment_date DESC 
           LIMIT 10');
$recent_enrollments = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage students, applications, and cohorts</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-primary text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $total_students; ?></h3>
                    <p class="text-gray-600">Total Students</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-accent text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $pending_applications; ?></h3>
                    <p class="text-gray-600">Pending Applications</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book-open text-secondary text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $active_enrollments; ?></h3>
                    <p class="text-gray-600">Active Enrollments</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $total_graduates; ?></h3>
                    <p class="text-gray-600">Graduates</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="applications.php" class="bg-primary text-white p-4 rounded-lg hover:bg-blue-700 transition text-center">
            <i class="fas fa-file-alt text-2xl mb-2"></i>
            <div class="font-semibold">Manage Applications</div>
        </a>
        <a href="cohorts.php" class="bg-secondary text-white p-4 rounded-lg hover:bg-green-700 transition text-center">
            <i class="fas fa-layer-group text-2xl mb-2"></i>
            <div class="font-semibold">Manage Cohorts</div>
        </a>
        <a href="students.php" class="bg-accent text-white p-4 rounded-lg hover:bg-yellow-600 transition text-center">
            <i class="fas fa-user-graduate text-2xl mb-2"></i>
            <div class="font-semibold">Manage Students</div>
        </a>
        <a href="examinations.php" class="bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700 transition text-center">
            <i class="fas fa-clipboard-check text-2xl mb-2"></i>
            <div class="font-semibold">Examinations</div>
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Applications -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recent Applications</h2>
                <a href="applications.php" class="text-primary hover:underline">View All</a>
            </div>
            
            <?php if(empty($recent_applications)): ?>
                <p class="text-gray-600">No pending applications.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($recent_applications as $app): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                                </h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($app['cohort_name']); ?></p>
                                <p class="text-xs text-gray-500">
                                    Applied: <?php echo date('M j, Y', strtotime($app['applied_at'])); ?>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="application_detail.php?id=<?php echo $app['id']; ?>" 
                                   class="bg-primary text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                                    Review
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Enrollments -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recent Enrollments</h2>
                <a href="enrollments.php" class="text-primary hover:underline">View All</a>
            </div>
            
            <?php if(empty($recent_enrollments)): ?>
                <p class="text-gray-600">No recent enrollments.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($recent_enrollments as $enrollment): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                </h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($enrollment['cohort_name']); ?></p>
                                <p class="text-xs text-gray-500">
                                    Enrolled: <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>