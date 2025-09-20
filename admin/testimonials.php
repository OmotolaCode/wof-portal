<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle testimonial approval/rejection
if(isset($_POST['action']) && isset($_POST['testimonial_id'])) {
    $testimonial_id = $_POST['testimonial_id'];
    $action = $_POST['action'];
    
    if($action === 'approve') {
        $db->query('UPDATE testimonials SET is_active = 1 WHERE id = :id');
        $db->bind(':id', $testimonial_id);
        if($db->execute()) {
            $message = 'Testimonial approved successfully!';
        }
    } elseif($action === 'reject') {
        $db->query('UPDATE testimonials SET is_active = 0 WHERE id = :id');
        $db->bind(':id', $testimonial_id);
        if($db->execute()) {
            $message = 'Testimonial rejected successfully!';
        }
    } elseif($action === 'feature') {
        $db->query('UPDATE testimonials SET is_featured = 1 WHERE id = :id');
        $db->bind(':id', $testimonial_id);
        if($db->execute()) {
            $message = 'Testimonial featured on homepage!';
        }
    } elseif($action === 'unfeature') {
        $db->query('UPDATE testimonials SET is_featured = 0 WHERE id = :id');
        $db->bind(':id', $testimonial_id);
        if($db->execute()) {
            $message = 'Testimonial removed from homepage!';
        }
    }
}

// Get all testimonials with user and cohort info
$db->query('SELECT t.*, u.first_name, u.last_name, u.email, c.name as cohort_name 
           FROM testimonials t 
           JOIN users u ON t.user_id = u.id 
           JOIN cohorts c ON t.cohort_id = c.id 
           ORDER BY t.created_at DESC');
$testimonials = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Testimonials</h1>
        <p class="text-gray-600 mt-2">Review and manage student testimonials</p>
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
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Testimonials</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Testimonial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($testimonials as $testimonial): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($testimonial['email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($testimonial['cohort_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    <?php echo htmlspecialchars(substr($testimonial['content'], 0, 100)) . '...'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $testimonial['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $testimonial['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <?php if($testimonial['is_featured']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Featured</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    <?php if(!$testimonial['is_active']): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="action" value="approve" 
                                                    class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="action" value="reject" 
                                                    class="text-red-600 hover:text-red-900">Deactivate</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if(!$testimonial['is_featured']): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="action" value="feature" 
                                                    class="text-blue-600 hover:text-blue-900">Feature</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="action" value="unfeature" 
                                                    class="text-gray-600 hover:text-gray-900">Unfeature</button>
                                        </form>
                                    <?php endif; ?>
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