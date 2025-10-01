<?php
require_once '../classes/Database.php';
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();

$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$user_id) {
    header('Location: students.php');
    exit();
}

$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $user_id);
$student = $db->single();

if(!$student) {
    header('Location: students.php');
    exit();
}

$db->query('SELECT a.*, c.name as cohort_name FROM applications a
           JOIN cohorts c ON a.cohort_id = c.id
           WHERE a.user_id = :user_id ORDER BY a.applied_at DESC');
$db->bind(':user_id', $user_id);
$applications = $db->resultSet();

$db->query('SELECT e.*, c.name as cohort_name, c.course_type, c.start_date, c.end_date
           FROM enrollments e
           JOIN cohorts c ON e.cohort_id = c.id
           WHERE e.user_id = :user_id ORDER BY e.enrollment_date DESC');
$db->bind(':user_id', $user_id);
$enrollments = $db->resultSet();

$db->query('SELECT ex.*, c.name as cohort_name FROM examinations ex
           JOIN cohorts c ON ex.cohort_id = c.id
           WHERE ex.user_id = :user_id ORDER BY ex.exam_date DESC');
$db->bind(':user_id', $user_id);
$examinations = $db->resultSet();

$db->query('SELECT ce.*, c.name as cohort_name FROM certificates ce
           JOIN enrollments e ON ce.enrollment_id = e.id
           JOIN cohorts c ON e.cohort_id = c.id
           WHERE ce.user_id = :user_id ORDER BY ce.issue_date DESC');
$db->bind(':user_id', $user_id);
$certificates = $db->resultSet();

$db->query('SELECT * FROM graduate_profiles WHERE user_id = :user_id');
$db->bind(':user_id', $user_id);
$graduate_profile = $db->single();

$db->query('SELECT t.*, c.name as cohort_name FROM testimonials t
           LEFT JOIN cohorts c ON t.cohort_id = c.id
           WHERE t.user_id = :user_id ORDER BY t.created_at DESC');
$db->bind(':user_id', $user_id);
$testimonials = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="students.php" class="text-primary hover:underline mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Students
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Student Details</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <?php if($student['profile_image']): ?>
                        <img src="../uploads/profiles/<?php echo htmlspecialchars($student['profile_image']); ?>"
                             alt="Profile" class="w-32 h-32 rounded-full mx-auto object-cover mb-4 border-4 border-primary">
                    <?php else: ?>
                        <div class="w-32 h-32 bg-primary rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
                            <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                    <span class="mt-3 inline-block px-4 py-2 rounded-full text-sm font-medium
                        <?php if($student['user_status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                        <?php if($student['user_status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                        <?php if($student['user_status'] === 'active'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                        <?php if($student['user_status'] === 'graduated'): ?>bg-purple-100 text-purple-800<?php endif; ?>">
                        <?php echo ucfirst($student['user_status']); ?>
                    </span>
                </div>

                <div class="space-y-3 border-t pt-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Phone</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">User Type</label>
                        <p class="text-gray-900"><?php echo ucfirst($student['user_type']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Joined</label>
                        <p class="text-gray-900"><?php echo date('M j, Y', strtotime($student['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Applications:</span>
                        <span class="font-semibold"><?php echo count($applications); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Enrollments:</span>
                        <span class="font-semibold"><?php echo count($enrollments); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Examinations:</span>
                        <span class="font-semibold"><?php echo count($examinations); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Certificates:</span>
                        <span class="font-semibold"><?php echo count($certificates); ?></span>
                    </div>
                </div>
            </div>

            <?php if(!empty($examinations)): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance</h3>
                    <?php
                    $total = 0;
                    $count = 0;
                    foreach($examinations as $exam) {
                        $total += $exam['percentage'];
                        $count++;
                    }
                    $average = $count > 0 ? $total / $count : 0;
                    ?>
                    <div class="text-center">
                        <div class="text-4xl font-bold
                            <?php if($average >= 70): ?>text-green-600<?php elseif($average >= 50): ?>text-yellow-600<?php else: ?>text-red-600<?php endif; ?>">
                            <?php echo number_format($average, 1); ?>%
                        </div>
                        <p class="text-gray-600 mt-2">Average Score</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Applications</h2>
                <?php if(empty($applications)): ?>
                    <p class="text-gray-600">No applications submitted.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($applications as $app): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($app['cohort_name']); ?></h3>
                                        <p class="text-sm text-gray-600">Course: <?php echo htmlspecialchars(str_replace('_', ' ', ucwords($app['course_choice'], '_'))); ?></p>
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
                                    <div class="mt-3 p-3 bg-gray-50 rounded">
                                        <p class="text-sm text-gray-700"><strong>Notes:</strong> <?php echo htmlspecialchars($app['admin_notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Enrollments</h2>
                <?php if(empty($enrollments)): ?>
                    <p class="text-gray-600">No enrollments.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($enrollments as $enrollment): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($enrollment['cohort_name']); ?></h3>
                                        <p class="text-sm text-gray-600">Course: <?php echo htmlspecialchars(str_replace('_', ' ', ucwords($enrollment['course_type'], '_'))); ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('M j, Y', strtotime($enrollment['start_date'])); ?> -
                                            <?php echo date('M j, Y', strtotime($enrollment['end_date'])); ?>
                                        </p>
                                        <?php if($enrollment['admission_letter_generated']): ?>
                                            <p class="text-sm text-green-600 mt-2">
                                                <i class="fas fa-check-circle mr-1"></i>Admission letter generated
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            <?php if($enrollment['status'] === 'enrolled'): ?>bg-green-100 text-green-800<?php endif; ?>
                                            <?php if($enrollment['status'] === 'completed'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                            <?php if($enrollment['status'] === 'dropped'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                        <?php if(!$enrollment['admission_letter_generated']): ?>
                                            <a href="generate_admission.php?enrollment_id=<?php echo $enrollment['id']; ?>"
                                               class="block mt-2 text-sm text-primary hover:underline">
                                                Generate Letter
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Examination Results</h2>
                <?php if(empty($examinations)): ?>
                    <p class="text-gray-600">No examination results.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($examinations as $exam): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($exam['exam_title']); ?></h3>
                                        <p class="text-sm text-gray-600">Cohort: <?php echo htmlspecialchars($exam['cohort_name']); ?></p>
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
                                                <?php echo htmlspecialchars($exam['grade']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Certificates</h2>
                <?php if(empty($certificates)): ?>
                    <p class="text-gray-600">No certificates issued.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($certificates as $cert): ?>
                            <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-r from-blue-50 to-purple-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            <i class="fas fa-certificate text-yellow-500 mr-2"></i>
                                            <?php echo htmlspecialchars($cert['course_name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">Cohort: <?php echo htmlspecialchars($cert['cohort_name']); ?></p>
                                        <p class="text-sm text-gray-600">Certificate No: <?php echo htmlspecialchars($cert['certificate_number']); ?></p>
                                        <p class="text-sm text-gray-600">Issue Date: <?php echo date('M j, Y', strtotime($cert['issue_date'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <?php if($cert['is_released']): ?>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Released
                                            </span>
                                            <?php if($cert['certificate_filename']): ?>
                                                <a href="../uploads/certificates/<?php echo htmlspecialchars($cert['certificate_filename']); ?>"
                                                   target="_blank"
                                                   class="block mt-2 text-sm text-primary hover:underline">
                                                    <i class="fas fa-download mr-1"></i>View Certificate
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                Pending Release
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($graduate_profile): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Graduate Profile</h2>
                    <div class="space-y-4">
                        <?php if($graduate_profile['title']): ?>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Title</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($graduate_profile['title']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if($graduate_profile['bio']): ?>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Bio</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($graduate_profile['bio']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if($graduate_profile['skills']): ?>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Skills</label>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <?php foreach(explode(',', $graduate_profile['skills']) as $skill): ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                            <?php echo htmlspecialchars(trim($skill)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 pt-4 border-t">
                            <span class="text-sm <?php echo $graduate_profile['is_visible'] ? 'text-green-600' : 'text-red-600'; ?>">
                                <i class="fas fa-<?php echo $graduate_profile['is_visible'] ? 'eye' : 'eye-slash'; ?> mr-1"></i>
                                <?php echo $graduate_profile['is_visible'] ? 'Visible' : 'Hidden'; ?>
                            </span>
                            <?php if($graduate_profile['is_job_ready']): ?>
                                <span class="text-sm text-green-600">
                                    <i class="fas fa-briefcase mr-1"></i>Job Ready
                                </span>
                            <?php endif; ?>
                            <?php if($graduate_profile['is_featured']): ?>
                                <span class="text-sm text-yellow-600">
                                    <i class="fas fa-star mr-1"></i>Featured
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
