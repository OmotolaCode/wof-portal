<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle examination scheduling and grading
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['schedule_exam'])) {
        $user_id = $_POST['user_id'];
        $cohort_id = $_POST['cohort_id'];
        $exam_date = $_POST['exam_date'];
        $exam_type = $_POST['exam_type'];
        $total_marks = $_POST['total_marks'];
        $passing_marks = $_POST['passing_marks'];
        
        $db->query('INSERT INTO examinations (user_id, cohort_id, exam_date, exam_type, total_marks, passing_marks) 
                   VALUES (:user_id, :cohort_id, :exam_date, :exam_type, :total_marks, :passing_marks)');
        $db->bind(':user_id', $user_id);
        $db->bind(':cohort_id', $cohort_id);
        $db->bind(':exam_date', $exam_date);
        $db->bind(':exam_type', $exam_type);
        $db->bind(':total_marks', $total_marks);
        $db->bind(':passing_marks', $passing_marks);
        
        if($db->execute()) {
            $message = 'Examination scheduled successfully!';
        } else {
            $error = 'Failed to schedule examination';
        }
    } elseif(isset($_POST['grade_exam'])) {
        $exam_id = $_POST['exam_id'];
        $score = $_POST['score'];
        $admin_feedback = trim($_POST['admin_feedback']);
        
        // Get exam details to determine pass/fail
        $db->query('SELECT passing_marks FROM examinations WHERE id = :id');
        $db->bind(':id', $exam_id);
        $exam = $db->single();
        
        $status = $score >= $exam['passing_marks'] ? 'passed' : 'failed';
        
        $db->query('UPDATE examinations SET score = :score, status = :status, admin_feedback = :feedback WHERE id = :id');
        $db->bind(':score', $score);
        $db->bind(':status', $status);
        $db->bind(':feedback', $admin_feedback);
        $db->bind(':id', $exam_id);
        
        if($db->execute()) {
            $message = 'Examination graded successfully!';
        } else {
            $error = 'Failed to grade examination';
        }
    }
}

// Get all examinations with user and cohort info
$db->query('SELECT e.*, u.first_name, u.last_name, u.email, c.name as cohort_name 
           FROM examinations e 
           JOIN users u ON e.user_id = u.id 
           JOIN cohorts c ON e.cohort_id = c.id 
           ORDER BY e.exam_date DESC');
$examinations = $db->resultSet();

// Get enrolled students for scheduling
$db->query('SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, c.id as cohort_id, c.name as cohort_name 
           FROM users u 
           JOIN enrollments en ON u.id = en.user_id 
           JOIN cohorts c ON en.cohort_id = c.id 
           WHERE en.status = "enrolled" 
           ORDER BY u.first_name, u.last_name');
$enrolled_students = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Examinations</h1>
        <p class="text-gray-600 mt-2">Schedule examinations and manage student assessments</p>
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
    
    <!-- Schedule Examination Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Schedule New Examination</h2>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="student_select" class="block text-sm font-medium text-gray-700">Select Student</label>
                <select id="student_select" name="user_id" required onchange="updateCohort()"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Choose Student</option>
                    <?php foreach($enrolled_students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" data-cohort="<?php echo $student['cohort_id']; ?>">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' - ' . $student['cohort_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="cohort_id" class="block text-sm font-medium text-gray-700">Cohort</label>
                <input type="hidden" id="cohort_id" name="cohort_id">
                <input type="text" id="cohort_display" readonly
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            
            <div>
                <label for="exam_date" class="block text-sm font-medium text-gray-700">Examination Date</label>
                <input type="date" id="exam_date" name="exam_date" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="exam_type" class="block text-sm font-medium text-gray-700">Examination Type</label>
                <select id="exam_type" name="exam_type" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="midterm">Midterm</option>
                    <option value="final">Final</option>
                    <option value="practical">Practical</option>
                    <option value="assessment">Assessment</option>
                </select>
            </div>
            
            <div>
                <label for="total_marks" class="block text-sm font-medium text-gray-700">Total Marks</label>
                <input type="number" id="total_marks" name="total_marks" value="100" min="1" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="passing_marks" class="block text-sm font-medium text-gray-700">Passing Marks</label>
                <input type="number" id="passing_marks" name="passing_marks" value="70" min="1" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div class="md:col-span-2">
                <button type="submit" name="schedule_exam" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Examination
                </button>
            </div>
        </form>
    </div>
    
    <!-- Examinations Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Examinations</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($examinations as $exam): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($exam['first_name'] . ' ' . $exam['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($exam['email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($exam['cohort_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    <?php echo ucfirst($exam['exam_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if($exam['score'] !== null): ?>
                                    <?php echo $exam['score']; ?>/<?php echo $exam['total_marks']; ?>
                                <?php else: ?>
                                    Not graded
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($exam['status'] === 'scheduled'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                                    <?php if($exam['status'] === 'taken'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                    <?php if($exam['status'] === 'passed'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($exam['status'] === 'failed'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                    <?php echo ucfirst($exam['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if($exam['status'] === 'scheduled' || $exam['status'] === 'taken'): ?>
                                    <button onclick="openGradeModal(<?php echo $exam['id']; ?>, <?php echo $exam['total_marks']; ?>)" 
                                            class="text-primary hover:text-blue-900">Grade Exam</button>
                                <?php else: ?>
                                    <span class="text-gray-500">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Grade Examination Modal -->
<div id="gradeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Grade Examination</h3>
        <form method="POST">
            <input type="hidden" id="examId" name="exam_id">
            
            <div class="mb-4">
                <label for="score" class="block text-sm font-medium text-gray-700">Score</label>
                <input type="number" id="score" name="score" min="0" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Out of <span id="totalMarks"></span> marks</p>
            </div>
            
            <div class="mb-4">
                <label for="admin_feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                <textarea id="admin_feedback" name="admin_feedback" rows="3" 
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeGradeModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" name="grade_exam" 
                        class="px-4 py-2 rounded-lg text-white bg-primary hover:bg-blue-700">Submit Grade</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCohort() {
    const select = document.getElementById('student_select');
    const selectedOption = select.options[select.selectedIndex];
    const cohortId = selectedOption.getAttribute('data-cohort');
    const cohortName = selectedOption.text.split(' - ')[1];
    
    document.getElementById('cohort_id').value = cohortId || '';
    document.getElementById('cohort_display').value = cohortName || '';
}

function openGradeModal(examId, totalMarks) {
    document.getElementById('examId').value = examId;
    document.getElementById('totalMarks').textContent = totalMarks;
    document.getElementById('score').max = totalMarks;
    
    const modal = document.getElementById('gradeModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeGradeModal() {
    const modal = document.getElementById('gradeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php include '../includes/footer.php'; ?>