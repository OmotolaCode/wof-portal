<?php
require_once '../classes/Database.php';
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();

$cohort_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$cohort_id) {
    header('Location: cohorts.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_outline'])) {
    $course_outline = $_POST['course_outline'];

    $db->query('UPDATE cohorts SET course_outline = :outline, updated_at = NOW() WHERE id = :id');
    $db->bind(':outline', $course_outline);
    $db->bind(':id', $cohort_id);
    $db->execute();

    $success_message = "Course outline updated successfully!";
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cohort_image'])) {
    $upload_dir = '../uploads/cohorts/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file = $_FILES['cohort_image'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if(in_array($file_ext, $allowed) && $file['size'] < 5000000) {
        $filename = 'cohort_' . $cohort_id . '_' . time() . '.' . $file_ext;
        $filepath = $upload_dir . $filename;

        if(move_uploaded_file($file['tmp_name'], $filepath)) {
            $db->query('UPDATE cohorts SET cohort_image = :image WHERE id = :id');
            $db->bind(':image', $filename);
            $db->bind(':id', $cohort_id);
            $db->execute();

            $success_message = "Cohort image uploaded successfully!";
        }
    }
}

$db->query('SELECT * FROM cohorts WHERE id = :id');
$db->bind(':id', $cohort_id);
$cohort = $db->single();

if(!$cohort) {
    header('Location: cohorts.php');
    exit();
}

$db->query('SELECT COUNT(*) as count FROM enrollments WHERE cohort_id = :id');
$db->bind(':id', $cohort_id);
$enrolled_count = $db->single()['count'];

$db->query('SELECT e.*, u.first_name, u.last_name, u.email
           FROM enrollments e
           JOIN users u ON e.user_id = u.id
           WHERE e.cohort_id = :id
           ORDER BY e.enrollment_date DESC');
$db->bind(':id', $cohort_id);
$students = $db->resultSet();

$db->query('SELECT * FROM course_materials WHERE cohort_id = :id ORDER BY sort_order ASC');
$db->bind(':id', $cohort_id);
$materials = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="cohorts.php" class="text-primary hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Cohorts
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($cohort['name']); ?></h1>
        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($cohort['description']); ?></p>
    </div>

    <?php if(isset($success_message)): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Cohort Information</h2>

                <div class="mb-6">
                    <?php if($cohort['cohort_image']): ?>
                        <img src="../uploads/cohorts/<?php echo htmlspecialchars($cohort['cohort_image']); ?>"
                             alt="Cohort Image"
                             class="w-full h-64 object-cover rounded-lg mb-4">
                    <?php else: ?>
                        <div class="w-full h-64 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-users text-white text-6xl"></i>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                        <input type="file" name="cohort_image" accept="image/*" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Upload Image
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Course Type</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars(str_replace('_', ' ', ucwords($cohort['course_type'], '_'))); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Duration</label>
                        <p class="text-gray-900"><?php echo $cohort['duration_months']; ?> months</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Start Date</label>
                        <p class="text-gray-900"><?php echo date('M j, Y', strtotime($cohort['start_date'])); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">End Date</label>
                        <p class="text-gray-900"><?php echo date('M j, Y', strtotime($cohort['end_date'])); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Status</label>
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            <?php if($cohort['status'] === 'upcoming'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                            <?php if($cohort['status'] === 'active'): ?>bg-green-100 text-green-800<?php endif; ?>
                            <?php if($cohort['status'] === 'completed'): ?>bg-blue-100 text-blue-800<?php endif; ?>">
                            <?php echo ucfirst($cohort['status']); ?>
                        </span>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Enrolled Students</label>
                        <p class="text-gray-900"><?php echo $enrolled_count; ?> / <?php echo $cohort['max_students']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Course Outline</h2>
                <form method="POST">
                    <textarea name="course_outline" rows="15"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Enter course outline here... You can use markdown formatting."><?php echo htmlspecialchars($cohort['course_outline'] ?? ''); ?></textarea>
                    <button type="submit" name="update_outline"
                            class="mt-4 bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Course Outline
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Enrolled Students</h2>

                <?php if(empty($students)): ?>
                    <p class="text-gray-600">No students enrolled yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($students as $student): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                                        <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 text-sm font-medium rounded-full
                                        <?php if($student['status'] === 'enrolled'): ?>bg-green-100 text-green-800<?php endif; ?>
                                        <?php if($student['status'] === 'completed'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                        <?php if($student['status'] === 'dropped'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                    <a href="student_details.php?id=<?php echo $student['user_id']; ?>"
                                       class="text-primary hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="cohorts.php?edit=<?php echo $cohort_id; ?>"
                       class="block w-full bg-primary text-white text-center py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>Edit Cohort
                    </a>
                    <a href="examinations.php?cohort_id=<?php echo $cohort_id; ?>"
                       class="block w-full bg-secondary text-white text-center py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-clipboard-check mr-2"></i>Manage Exams
                    </a>
                    <a href="graduates.php?cohort_id=<?php echo $cohort_id; ?>"
                       class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-graduation-cap mr-2"></i>View Graduates
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Enrolled:</span>
                        <span class="font-semibold"><?php echo $enrolled_count; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Available Spots:</span>
                        <span class="font-semibold"><?php echo $cohort['max_students'] - $enrolled_count; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Capacity:</span>
                        <span class="font-semibold">
                            <?php echo round(($enrolled_count / $cohort['max_students']) * 100); ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
