<?php
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

$db = new Database();

$cohort_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$cohort_id) {
    header('Location: dashboard.php');
    exit();
}

$db->query('SELECT e.*, c.* FROM enrollments e
           JOIN cohorts c ON e.cohort_id = c.id
           WHERE e.user_id = :user_id AND e.cohort_id = :cohort_id AND e.status = "enrolled"');
$db->bind(':user_id', $_SESSION['user_id']);
$db->bind(':cohort_id', $cohort_id);
$enrollment = $db->single();

if(!$enrollment) {
    header('Location: dashboard.php');
    exit();
}

$db->query('SELECT * FROM course_materials WHERE cohort_id = :cohort_id AND is_visible = 1 ORDER BY sort_order ASC');
$db->bind(':cohort_id', $cohort_id);
$materials = $db->resultSet();

$db->query('SELECT * FROM examinations WHERE user_id = :user_id AND cohort_id = :cohort_id ORDER BY exam_date DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$db->bind(':cohort_id', $cohort_id);
$exams = $db->resultSet();

$db->query('SELECT * FROM certificates WHERE user_id = :user_id AND enrollment_id = :enrollment_id');
$db->bind(':user_id', $_SESSION['user_id']);
$db->bind(':enrollment_id', $enrollment['id']);
$certificate = $db->single();

$db->query('SELECT COUNT(*) as count FROM enrollments WHERE cohort_id = :cohort_id');
$db->bind(':cohort_id', $cohort_id);
$classmates_count = $db->single()['count'];
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="dashboard.php" class="text-primary hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($enrollment['name']); ?></h1>
        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($enrollment['description']); ?></p>
    </div>

    <?php if($enrollment['admission_letter_generated']): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>Your admission letter is ready!
            <a href="uploads/admission_letters/<?php echo htmlspecialchars($enrollment['admission_letter_filename']); ?>"
               target="_blank" class="ml-4 underline font-semibold">
                Download Admission Letter <i class="fas fa-download ml-1"></i>
            </a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <?php if($enrollment['cohort_image']): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="uploads/cohorts/<?php echo htmlspecialchars($enrollment['cohort_image']); ?>"
                         alt="Cohort"
                         class="w-full h-64 object-cover">
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Course Outline</h2>
                <?php if($enrollment['course_outline']): ?>
                    <div class="prose max-w-none text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($enrollment['course_outline'])); ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">Course outline will be available soon.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Course Materials</h2>
                <?php if(empty($materials)): ?>
                    <p class="text-gray-600">No materials available yet. Check back soon!</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($materials as $material): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center
                                        <?php if($material['material_type'] === 'video'): ?>bg-red-100<?php endif; ?>
                                        <?php if($material['material_type'] === 'document'): ?>bg-blue-100<?php endif; ?>
                                        <?php if($material['material_type'] === 'link'): ?>bg-green-100<?php endif; ?>
                                        <?php if($material['material_type'] === 'other'): ?>bg-gray-100<?php endif; ?>">
                                        <?php if($material['material_type'] === 'video'): ?>
                                            <i class="fas fa-video text-red-600 text-xl"></i>
                                        <?php elseif($material['material_type'] === 'document'): ?>
                                            <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
                                        <?php elseif($material['material_type'] === 'link'): ?>
                                            <i class="fas fa-link text-green-600 text-xl"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file text-gray-600 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($material['title']); ?></h3>
                                        <?php if($material['description']): ?>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($material['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if($material['file_url']): ?>
                                    <a href="<?php echo htmlspecialchars($material['file_url']); ?>"
                                       target="_blank"
                                       class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-external-link-alt mr-2"></i>Open
                                    </a>
                                <?php elseif($material['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($material['file_path']); ?>"
                                       target="_blank"
                                       class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-download mr-2"></i>Download
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">My Examination Results</h2>
                <?php if(empty($exams)): ?>
                    <p class="text-gray-600">No examination results yet.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($exams as $exam): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($exam['exam_title']); ?></h3>
                                        <p class="text-sm text-gray-600">Date: <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold
                                            <?php if($exam['percentage'] >= 70): ?>text-green-600<?php elseif($exam['percentage'] >= 50): ?>text-yellow-600<?php else: ?>text-red-600<?php endif; ?>">
                                            <?php echo number_format($exam['percentage'], 1); ?>%
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <?php echo $exam['score']; ?> / <?php echo $exam['max_score']; ?>
                                        </div>
                                        <?php if($exam['grade']): ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded mt-1 inline-block">
                                                Grade: <?php echo htmlspecialchars($exam['grade']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if($exam['remarks']): ?>
                                    <div class="mt-3 p-3 bg-gray-50 rounded">
                                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($exam['remarks']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($certificate && $certificate['is_released']): ?>
                <div class="bg-gradient-to-r from-primary to-secondary rounded-lg shadow-lg p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">
                                <i class="fas fa-award mr-2"></i>Your Certificate is Ready!
                            </h2>
                            <p class="text-blue-100">Congratulations on successfully completing the program!</p>
                            <p class="text-sm text-blue-200 mt-2">Certificate No: <?php echo htmlspecialchars($certificate['certificate_number']); ?></p>
                        </div>
                        <a href="uploads/certificates/<?php echo htmlspecialchars($certificate['certificate_filename']); ?>"
                           target="_blank"
                           class="bg-white text-primary px-8 py-4 rounded-lg font-bold hover:bg-gray-100 transition">
                            <i class="fas fa-download mr-2"></i>Download Certificate
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cohort Details</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Course Type</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars(str_replace('_', ' ', ucwords($enrollment['course_type'], '_'))); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Duration</label>
                        <p class="text-gray-900"><?php echo $enrollment['duration_months']; ?> months</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Start Date</label>
                        <p class="text-gray-900"><?php echo date('M j, Y', strtotime($enrollment['start_date'])); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">End Date</label>
                        <p class="text-gray-900"><?php echo date('M j, Y', strtotime($enrollment['end_date'])); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Classmates</label>
                        <p class="text-gray-900"><?php echo $classmates_count; ?> students</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="contact.php" class="block text-primary hover:underline">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                    <a href="profile-settings.php" class="block text-primary hover:underline">
                        <i class="fas fa-user-cog mr-2"></i>Profile Settings
                    </a>
                    <a href="dashboard.php" class="block text-primary hover:underline">
                        <i class="fas fa-home mr-2"></i>My Dashboard
                    </a>
                </div>
            </div>

            <?php if(!empty($exams)): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Summary</h3>
                    <?php
                    $total_score = 0;
                    $count = 0;
                    foreach($exams as $exam) {
                        $total_score += $exam['percentage'];
                        $count++;
                    }
                    $average = $count > 0 ? $total_score / $count : 0;
                    ?>
                    <div class="text-center">
                        <div class="text-4xl font-bold
                            <?php if($average >= 70): ?>text-green-600<?php elseif($average >= 50): ?>text-yellow-600<?php else: ?>text-red-600<?php endif; ?>">
                            <?php echo number_format($average, 1); ?>%
                        </div>
                        <p class="text-gray-600 mt-2">Average Score</p>
                        <?php if($average >= 70): ?>
                            <p class="text-sm text-green-600 mt-2">
                                <i class="fas fa-check-circle mr-1"></i>Excellent Performance!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
