<?php 
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle cohort creation/updates
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['create_cohort'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $duration_months = $_POST['duration_months'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $max_students = $_POST['max_students'];
        $status = $_POST['status'];
        
        if(empty($name) || empty($start_date) || empty($end_date)) {
            $error = 'Please fill in all required fields';
        } else {
            $db->query('INSERT INTO cohorts (name, description, duration_months, start_date, end_date, max_students, status) 
                       VALUES (:name, :description, :duration_months, :start_date, :end_date, :max_students, :status)');
            $db->bind(':name', $name);
            $db->bind(':description', $description);
            $db->bind(':duration_months', $duration_months);
            $db->bind(':start_date', $start_date);
            $db->bind(':end_date', $end_date);
            $db->bind(':max_students', $max_students);
            $db->bind(':status', $status);
            
            if($db->execute()) {
                $message = 'Cohort created successfully!';
            } else {
                $error = 'Failed to create cohort';
            }
        }
    } elseif(isset($_POST['update_status'])) {
        $cohort_id = $_POST['cohort_id'];
        $new_status = $_POST['new_status'];
        
        $db->query('UPDATE cohorts SET status = :status WHERE id = :id');
        $db->bind(':status', $new_status);
        $db->bind(':id', $cohort_id);
        
        if($db->execute()) {
            $message = 'Cohort status updated successfully!';
        }
    }
}

// Get all cohorts with enrollment counts
$db->query('SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments e WHERE e.cohort_id = c.id) as total_enrollments,
           (SELECT COUNT(*) FROM enrollments e WHERE e.cohort_id = c.id AND e.status = "enrolled") as active_enrollments,
           (SELECT COUNT(*) FROM applications a WHERE a.cohort_id = c.id AND a.status = "pending") as pending_applications
           FROM cohorts c 
           ORDER BY c.start_date DESC');
$cohorts = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Cohorts</h1>
        <p class="text-gray-600 mt-2">Create and manage training cohorts</p>
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
    
    <!-- Create New Cohort Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Create New Cohort</h2>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Cohort Name</label>
                <input type="text" id="name" name="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="duration_months" class="block text-sm font-medium text-gray-700">Duration (Months)</label>
                <input type="number" id="duration_months" name="duration_months" min="1" max="24" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="start_date" name="start_date" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="end_date" name="end_date" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="max_students" class="block text-sm font-medium text-gray-700">Maximum Students</label>
                <input type="number" id="max_students" name="max_students" min="1" value="30"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="md:col-span-2">
                <button type="submit" name="create_cohort" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i>Create Cohort
                </button>
            </div>
        </form>
    </div>
    
    <!-- Existing Cohorts -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Cohorts</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($cohorts as $cohort): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cohort['name']); ?></div>
                                    <div class="text-sm text-gray-500">Max: <?php echo $cohort['max_students']; ?> students</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $cohort['duration_months']; ?> months
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div><?php echo date('M j, Y', strtotime($cohort['start_date'])); ?></div>
                                <div class="text-gray-500"><?php echo date('M j, Y', strtotime($cohort['end_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div><?php echo $cohort['active_enrollments']; ?> active</div>
                                <div class="text-gray-500"><?php echo $cohort['total_enrollments']; ?> total</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $cohort['pending_applications']; ?> pending
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($cohort['status'] === 'active'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($cohort['status'] === 'upcoming'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                    <?php if($cohort['status'] === 'completed'): ?>bg-gray-100 text-gray-800<?php endif; ?>">
                                    <?php echo ucfirst($cohort['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="cohort_id" value="<?php echo $cohort['id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                                            <option value="">Change Status</option>
                                            <option value="upcoming" <?php echo $cohort['status'] === 'upcoming' ? 'disabled' : ''; ?>>Upcoming</option>
                                            <option value="active" <?php echo $cohort['status'] === 'active' ? 'disabled' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $cohort['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                    <a href="cohort_details.php?id=<?php echo $cohort['id']; ?>"
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