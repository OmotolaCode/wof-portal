<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

if($_SESSION['user_status'] !== 'approved') {
    header('Location: dashboard.php');
    exit();
}

$db = new Database();
$message = '';
$error = '';

// Get cohort ID from URL
$cohort_id = isset($_GET['cohort_id']) ? $_GET['cohort_id'] : null;

if(!$cohort_id) {
    header('Location: dashboard.php');
    exit();
}

// Get cohort details
$db->query('SELECT * FROM cohorts WHERE id = :id AND status IN ("upcoming", "active")');
$db->bind(':id', $cohort_id);
$cohort = $db->single();

if(!$cohort) {
    header('Location: dashboard.php');
    exit();
}

// Check if user already applied
$db->query('SELECT id FROM applications WHERE user_id = :user_id AND cohort_id = :cohort_id');
$db->bind(':user_id', $_SESSION['user_id']);
$db->bind(':cohort_id', $cohort_id);
$existing_application = $db->single();

if($existing_application) {
    $error = 'You have already applied to this cohort.';
}

// Handle application submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing_application) {
    $motivation = trim($_POST['motivation']);
    $education_background = trim($_POST['education_background']);
    $work_experience = trim($_POST['work_experience']);
    
    if(empty($motivation) || empty($education_background)) {
        $error = 'Please fill in all required fields';
    } else {
        $db->query('INSERT INTO applications (user_id, cohort_id, motivation, education_background, work_experience) 
                   VALUES (:user_id, :cohort_id, :motivation, :education_background, :work_experience)');
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':cohort_id', $cohort_id);
        $db->bind(':motivation', $motivation);
        $db->bind(':education_background', $education_background);
        $db->bind(':work_experience', $work_experience);
        
        if($db->execute()) {
            $message = 'Application submitted successfully! You will be notified once it is reviewed.';
        } else {
            $error = 'Failed to submit application. Please try again.';
        }
    }
}
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Apply to Cohort</h1>
        <p class="text-gray-600 mt-2">Submit your application to join this training program</p>
    </div>
    
    <!-- Cohort Information -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($cohort['name']); ?></h2>
        <p class="text-blue-100 mb-4"><?php echo htmlspecialchars($cohort['description']); ?></p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <i class="fas fa-calendar mr-2"></i>
                <strong>Start Date:</strong> <?php echo date('M j, Y', strtotime($cohort['start_date'])); ?>
            </div>
            <div>
                <i class="fas fa-clock mr-2"></i>
                <strong>Duration:</strong> <?php echo $cohort['duration_months']; ?> months
            </div>
            <div>
                <i class="fas fa-users mr-2"></i>
                <strong>Max Students:</strong> <?php echo $cohort['max_students']; ?>
            </div>
        </div>
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
    
    <?php if(!$existing_application && !$message): ?>
        <!-- Application Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Application Form</h3>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="motivation" class="block text-sm font-medium text-gray-700">
                        Why do you want to join this program? *
                    </label>
                    <textarea id="motivation" name="motivation" rows="4" required
                              placeholder="Explain your motivation for joining this cohort, your career goals, and how this program aligns with your objectives..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div>
                    <label for="education_background" class="block text-sm font-medium text-gray-700">
                        Educational Background *
                    </label>
                    <textarea id="education_background" name="education_background" rows="3" required
                              placeholder="Describe your educational qualifications, relevant courses, and any certifications..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div>
                    <label for="work_experience" class="block text-sm font-medium text-gray-700">
                        Work Experience (Optional)
                    </label>
                    <textarea id="work_experience" name="work_experience" rows="3"
                              placeholder="Describe any relevant work experience, internships, or projects..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Application Requirements</h4>
                    <ul class="text-blue-700 text-sm space-y-1">
                        <li><i class="fas fa-check mr-2"></i>Must be an approved user</li>
                        <li><i class="fas fa-check mr-2"></i>Complete all required fields</li>
                        <li><i class="fas fa-check mr-2"></i>Demonstrate genuine interest and commitment</li>
                        <li><i class="fas fa-check mr-2"></i>Applications are reviewed by our admin team</li>
                    </ul>
                </div>
                
                <div class="flex justify-between">
                    <a href="dashboard.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </div>
            </form>
        </div>
    <?php elseif($existing_application): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <i class="fas fa-check-circle text-green-600 text-4xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Application Already Submitted</h3>
            <p class="text-gray-600 mb-6">You have already applied to this cohort. Check your dashboard for application status updates.</p>
            <a href="dashboard.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-dashboard mr-2"></i>Go to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>