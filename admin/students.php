<?php
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle student status updates
if(isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    
    if($action === 'approve') {
        $db->query('UPDATE users SET status = "approved" WHERE id = :id');
        $db->bind(':id', $user_id);
        if($db->execute()) {
            $message = 'Student approved successfully!';
        }
    } elseif($action === 'reject') {
        $db->query('UPDATE users SET status = "rejected" WHERE id = :id');
        $db->bind(':id', $user_id);
        if($db->execute()) {
            $message = 'Student rejected successfully!';
        }
    } elseif($action === 'graduate') {
        $db->query('UPDATE users SET status = "graduated", user_type = "graduate" WHERE id = :id');
        $db->bind(':id', $user_id);
        if($db->execute()) {
            $message = 'Student marked as graduated!';
        }
    }
}

// Get all students with their enrollment info
$db->query('SELECT u.*, 
           (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.id) as application_count,
           (SELECT COUNT(*) FROM enrollments e WHERE e.user_id = u.id AND e.status = "enrolled") as active_enrollments,
           (SELECT c.name FROM enrollments e JOIN cohorts c ON e.cohort_id = c.id WHERE e.user_id = u.id AND e.status = "enrolled" LIMIT 1) as current_cohort
           FROM users u 
           WHERE u.user_type IN ("student", "graduate") 
           ORDER BY u.created_at DESC');
$students = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Students</h1>
        <p class="text-gray-600 mt-2">Review and manage student registrations and status</p>
    </div>
    
    <?php if($message): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <?php
        $pending_count = count(array_filter($students, function($s) { return $s['status'] === 'pending'; }));
        $approved_count = count(array_filter($students, function($s) { return $s['status'] === 'approved'; }));
        $graduated_count = count(array_filter($students, function($s) { return $s['status'] === 'graduated'; }));
        $rejected_count = count(array_filter($students, function($s) { return $s['status'] === 'rejected'; }));
        ?>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $pending_count; ?></h3>
                    <p class="text-gray-600">Pending Approval</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $approved_count; ?></h3>
                    <p class="text-gray-600">Approved Students</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $graduated_count; ?></h3>
                    <p class="text-gray-600">Graduates</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $rejected_count; ?></h3>
                    <p class="text-gray-600">Rejected</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Students</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($students as $student): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">ID: <?php echo $student['id']; ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($student['email']); ?></div>
                                <?php if($student['phone']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($student['status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($student['status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                                    <?php if($student['status'] === 'rejected'): ?>bg-red-100 text-red-800<?php endif; ?>
                                    <?php if($student['status'] === 'graduated'): ?>bg-blue-100 text-blue-800<?php endif; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $student['current_cohort'] ? htmlspecialchars($student['current_cohort']) : 'None'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $student['application_count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    <?php if($student['status'] === 'pending'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="approve" 
                                                    class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="reject" 
                                                    class="text-red-600 hover:text-red-900">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if($student['status'] === 'approved' && $student['active_enrollments'] > 0): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="graduate" 
                                                    class="text-blue-600 hover:text-blue-900">Mark as Graduate</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="student_detail.php?id=<?php echo $student['id']; ?>" 
                                       class="text-primary hover:text-blue-900">View Details</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>