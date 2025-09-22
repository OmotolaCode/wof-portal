<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle application approval/rejection
if(isset($_POST['action']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $action = $_POST['action'];
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    if($action === 'approve') {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update application status
            $db->query('UPDATE applications SET status = "approved", admin_notes = :notes, reviewed_at = NOW() WHERE id = :id');
            $db->bind(':notes', $admin_notes);
            $db->bind(':id', $application_id);
            $db->execute();
            
            // Get application details
            $db->query('SELECT user_id, cohort_id FROM applications WHERE id = :id');
            $db->bind(':id', $application_id);
            $app = $db->single();
            
            // Create enrollment
            $db->query('INSERT INTO enrollments (user_id, cohort_id, enrollment_date, status) VALUES (:user_id, :cohort_id, CURDATE(), "enrolled")');
            $db->bind(':user_id', $app['user_id']);
            $db->bind(':cohort_id', $app['cohort_id']);
            $db->execute();
            
            $db->endTransaction();
            $message = 'Application approved and student enrolled successfully!';
        } catch(Exception $e) {
            $db->cancelTransaction();
            $error = 'Failed to approve application: ' . $e->getMessage();
        }
    } elseif($action === 'reject') {
        $db->query('UPDATE applications SET status = "rejected", admin_notes = :notes, reviewed_at = NOW() WHERE id = :id');
        $db->bind(':notes', $admin_notes);
        $db->bind(':id', $application_id);
        
        if($db->execute()) {
            $message = 'Application rejected successfully!';
        }
    }
}

// Get all applications with user and cohort info
$db->query('SELECT a.*, u.first_name, u.last_name, u.email, u.phone, c.name as cohort_name, c.start_date 
           FROM applications a 
           JOIN users u ON a.user_id = u.id 
           JOIN cohorts c ON a.cohort_id = c.id 
           ORDER BY a.applied_at DESC');
$applications = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Applications</h1>
        <p class="text-gray-600 mt-2">Review and process student applications</p>
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
    
    <!-- Applications Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Applications</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($applications as $app): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['email']); ?></div>
                                    <?php if($app['phone']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($app['cohort_name']); ?></div>
                                <div class="text-sm text-gray-500">Starts: <?php echo date('M j, Y', strtotime($app['start_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($app['applied_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($app['status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($app['status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                                    <?php if($app['status'] === 'rejected'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if($app['status'] === 'pending'): ?>
                                    <button onclick="openModal(<?php echo $app['id']; ?>, 'approve')" 
                                            class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                    <button onclick="openModal(<?php echo $app['id']; ?>, 'reject')" 
                                            class="text-red-600 hover:text-red-900">Reject</button>
                                <?php else: ?>
                                    <a href="application_detail.php?id=<?php echo $app['id']; ?>" 
                                       class="text-primary hover:text-blue-900">View Details</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Application Review -->
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4"></h3>
        <form method="POST">
            <input type="hidden" id="applicationId" name="application_id">
            <input type="hidden" id="actionType" name="action">
            
            <div class="mb-4">
                <label for="admin_notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                <textarea id="admin_notes" name="admin_notes" rows="3" 
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" id="submitBtn" 
                        class="px-4 py-2 rounded-lg text-white">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(applicationId, action) {
    document.getElementById('applicationId').value = applicationId;
    document.getElementById('actionType').value = action;
    
    const modal = document.getElementById('reviewModal');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    
    if(action === 'approve') {
        title.textContent = 'Approve Application';
        submitBtn.textContent = 'Approve';
        submitBtn.className = 'px-4 py-2 rounded-lg text-white bg-green-600 hover:bg-green-700';
    } else {
        title.textContent = 'Reject Application';
        submitBtn.textContent = 'Reject';
        submitBtn.className = 'px-4 py-2 rounded-lg text-white bg-red-600 hover:bg-red-700';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('reviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php include '../includes/footer.php'; ?>