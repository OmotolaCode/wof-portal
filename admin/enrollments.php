<?php
require_once '../classes/Database.php';
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cohort_filter = isset($_GET['cohort']) ? trim($_GET['cohort']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = 'SELECT e.*, u.first_name, u.last_name, u.email, u.phone,
          c.name as cohort_name, c.course_type, c.start_date, c.end_date,
          a.course_choice
          FROM enrollments e
          JOIN users u ON e.user_id = u.id
          JOIN cohorts c ON e.cohort_id = c.id
          LEFT JOIN applications a ON e.application_id = a.id
          WHERE 1=1';

if($search) {
    $query .= ' AND (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)';
}

if($cohort_filter) {
    $query .= ' AND e.cohort_id = :cohort';
}

if($status_filter) {
    $query .= ' AND e.status = :status';
}

$query .= ' ORDER BY e.enrollment_date DESC';

$db->query($query);

if($search) {
    $db->bind(':search', '%' . $search . '%');
}
if($cohort_filter) {
    $db->bind(':cohort', $cohort_filter);
}
if($status_filter) {
    $db->bind(':status', $status_filter);
}

$enrollments = $db->resultSet();

$db->query('SELECT * FROM cohorts ORDER BY start_date DESC');
$all_cohorts = $db->resultSet();

$db->query('SELECT COUNT(*) as count FROM enrollments WHERE status = "enrolled"');
$active_count = $db->single()['count'];

$db->query('SELECT COUNT(*) as count FROM enrollments WHERE status = "completed"');
$completed_count = $db->single()['count'];
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Enrollments</h1>
        <p class="text-gray-600 mt-2">View and manage student enrollments across all cohorts</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-primary text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo count($enrollments); ?></h3>
                    <p class="text-gray-600">Total Enrollments</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book-open text-secondary text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $active_count; ?></h3>
                    <p class="text-gray-600">Active Students</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $completed_count; ?></h3>
                    <p class="text-gray-600">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" placeholder="Search by name or email..."
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">

            <select name="cohort" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Cohorts</option>
                <?php foreach($all_cohorts as $cohort): ?>
                    <option value="<?php echo $cohort['id']; ?>" <?php echo $cohort_filter === $cohort['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cohort['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Status</option>
                <option value="enrolled" <?php echo $status_filter === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="dropped" <?php echo $status_filter === 'dropped' ? 'selected' : ''; ?>>Dropped</option>
            </select>

            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if(empty($enrollments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4 block"></i>
                            No enrollments found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($enrollments as $enrollment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                                        <?php echo strtoupper(substr($enrollment['first_name'], 0, 1) . substr($enrollment['last_name'], 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($enrollment['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($enrollment['cohort_name']); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('M Y', strtotime($enrollment['start_date'])); ?> -
                                    <?php echo date('M Y', strtotime($enrollment['end_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', ucwords($enrollment['course_choice'] ?? $enrollment['course_type'], '_'))); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-medium rounded-full
                                    <?php if($enrollment['status'] === 'enrolled'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($enrollment['status'] === 'completed'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                    <?php if($enrollment['status'] === 'dropped'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                    <?php echo ucfirst($enrollment['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="student_details.php?id=<?php echo $enrollment['user_id']; ?>"
                                   class="text-primary hover:text-blue-700 mr-3">View</a>
                                <?php if(!$enrollment['admission_letter_generated']): ?>
                                    <a href="generate_admission.php?enrollment_id=<?php echo $enrollment['id']; ?>"
                                       class="text-secondary hover:text-green-700">Generate Letter</a>
                                <?php else: ?>
                                    <span class="text-green-600"><i class="fas fa-check-circle"></i> Letter Generated</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
