<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

$db = new Database();
$message = '';
$error = '';

// Check if user is graduated or enrolled
$db->query('SELECT e.*, c.name as cohort_name 
           FROM enrollments e 
           JOIN cohorts c ON e.cohort_id = c.id 
           WHERE e.user_id = :user_id AND e.status IN ("completed", "enrolled")');
$db->bind(':user_id', $_SESSION['user_id']);
$enrollments = $db->resultSet();

if(empty($enrollments)) {
    header('Location: dashboard.php');
    exit();
}

// Handle testimonial submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cohort_id = $_POST['cohort_id'];
    $content = trim($_POST['content']);
    $rating = $_POST['rating'];
    
    if(empty($content)) {
        $error = 'Please write your testimonial';
    } else {
        // Check if user already submitted testimonial for this cohort
        $db->query('SELECT id FROM testimonials WHERE user_id = :user_id AND cohort_id = :cohort_id');
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':cohort_id', $cohort_id);
        
        if($db->single()) {
            $error = 'You have already submitted a testimonial for this cohort';
        } else {
            $db->query('INSERT INTO testimonials (user_id, cohort_id, content, rating) 
                       VALUES (:user_id, :cohort_id, :content, :rating)');
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':cohort_id', $cohort_id);
            $db->bind(':content', $content);
            $db->bind(':rating', $rating);
            
            if($db->execute()) {
                $message = 'Thank you! Your testimonial has been submitted and is pending approval.';
            } else {
                $error = 'Failed to submit testimonial. Please try again.';
            }
        }
    }
}

// Get existing testimonials
$db->query('SELECT t.*, c.name as cohort_name 
           FROM testimonials t 
           JOIN cohorts c ON t.cohort_id = c.id 
           WHERE t.user_id = :user_id 
           ORDER BY t.created_at DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$existing_testimonials = $db->resultSet();
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Share Your Experience</h1>
        <p class="text-gray-600 mt-2">Help future students by sharing your journey with Whoba Ogo Foundation</p>
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
    
    <!-- Submit New Testimonial -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Submit a Testimonial</h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="cohort_id" class="block text-sm font-medium text-gray-700">Select Cohort</label>
                <select id="cohort_id" name="cohort_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Choose a cohort...</option>
                    <?php foreach($enrollments as $enrollment): ?>
                        <option value="<?php echo $enrollment['cohort_id']; ?>">
                            <?php echo htmlspecialchars($enrollment['cohort_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Your Testimonial</label>
                <textarea id="content" name="content" rows="6" required
                          placeholder="Share your experience, what you learned, how it helped your career, etc."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                <select id="rating" name="rating" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Select rating...</option>
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                    <option value="3">⭐⭐⭐ Good</option>
                    <option value="2">⭐⭐ Fair</option>
                    <option value="1">⭐ Poor</option>
                </select>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary transition">
                Submit Testimonial
            </button>
        </form>
    </div>
    
    <!-- Existing Testimonials -->
    <?php if(!empty($existing_testimonials)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Testimonials</h2>
            
            <div class="space-y-6">
                <?php foreach($existing_testimonials as $testimonial): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($testimonial['cohort_name']); ?></h3>
                            <div class="flex items-center space-x-2">
                                <div class="flex">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $testimonial['is_active'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $testimonial['is_active'] ? 'Approved' : 'Pending'; ?>
                                </span>
                                <?php if($testimonial['is_featured']): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-gray-700"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                        <p class="text-sm text-gray-500 mt-2">
                            Submitted: <?php echo date('M j, Y', strtotime($testimonial['created_at'])); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>