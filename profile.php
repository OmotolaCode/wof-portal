<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();

$db = new Database();
$message = '';
$error = '';

// Check if user is graduated
if($_SESSION['user_status'] !== 'graduated') {
    header('Location: dashboard.php');
    exit();
}

// Handle profile creation/update
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $linkedin_url = trim($_POST['linkedin_url']);
    $portfolio_url = trim($_POST['portfolio_url']);
    $years_experience = $_POST['years_experience'];
    $education_history = trim($_POST['education_history']);
    $work_history = trim($_POST['work_history']);
    $certifications = trim($_POST['certifications']);
    $languages = trim($_POST['languages']);
    $achievements = trim($_POST['achievements']);
    $references = trim($_POST['references']);
    
    if(empty($title) || empty($bio)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if profile exists
        $db->query('SELECT id FROM graduate_profiles WHERE user_id = :user_id');
        $db->bind(':user_id', $_SESSION['user_id']);
        $existing = $db->single();
        
        if($existing) {
            // Update existing profile
            $db->query('UPDATE graduate_profiles SET 
                       title = :title, bio = :bio, skills = :skills, linkedin_url = :linkedin_url, 
                       portfolio_url = :portfolio_url, years_experience = :years_experience,
                       education_history = :education_history, work_history = :work_history,
                       certifications = :certifications, languages = :languages,
                       achievements = :achievements, references = :references,
                       updated_at = NOW()
                       WHERE user_id = :user_id');
        } else {
            // Create new profile
            $db->query('INSERT INTO graduate_profiles 
                       (user_id, title, bio, skills, linkedin_url, portfolio_url, years_experience,
                        education_history, work_history, certifications, languages, achievements, references) 
                       VALUES (:user_id, :title, :bio, :skills, :linkedin_url, :portfolio_url, :years_experience,
                               :education_history, :work_history, :certifications, :languages, :achievements, :references)');
        }
        
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':title', $title);
        $db->bind(':bio', $bio);
        $db->bind(':skills', $skills);
        $db->bind(':linkedin_url', $linkedin_url);
        $db->bind(':portfolio_url', $portfolio_url);
        $db->bind(':years_experience', $years_experience);
        $db->bind(':education_history', $education_history);
        $db->bind(':work_history', $work_history);
        $db->bind(':certifications', $certifications);
        $db->bind(':languages', $languages);
        $db->bind(':achievements', $achievements);
        $db->bind(':references', $references);
        
        if($db->execute()) {
            $message = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Get existing profile
$db->query('SELECT * FROM graduate_profiles WHERE user_id = :user_id');
$db->bind(':user_id', $_SESSION['user_id']);
$profile = $db->single();
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Graduate Profile</h1>
        <p class="text-gray-600 mt-2">Create your professional profile to showcase your skills to employers</p>
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
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Professional Title *</label>
                    <input type="text" id="title" name="title" required
                           value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>"
                           placeholder="e.g., Full Stack Developer, Digital Marketing Specialist"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="years_experience" class="block text-sm font-medium text-gray-700">Years of Experience</label>
                    <select id="years_experience" name="years_experience"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="0" <?php echo ($profile['years_experience'] ?? 0) == 0 ? 'selected' : ''; ?>>Fresh Graduate</option>
                        <option value="1" <?php echo ($profile['years_experience'] ?? 0) == 1 ? 'selected' : ''; ?>>1 Year</option>
                        <option value="2" <?php echo ($profile['years_experience'] ?? 0) == 2 ? 'selected' : ''; ?>>2 Years</option>
                        <option value="3" <?php echo ($profile['years_experience'] ?? 0) == 3 ? 'selected' : ''; ?>>3 Years</option>
                        <option value="4" <?php echo ($profile['years_experience'] ?? 0) == 4 ? 'selected' : ''; ?>>4 Years</option>
                        <option value="5" <?php echo ($profile['years_experience'] ?? 0) >= 5 ? 'selected' : ''; ?>>5+ Years</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700">Professional Summary *</label>
                <textarea id="bio" name="bio" rows="4" required
                          placeholder="Write a compelling summary of your professional background, skills, and career objectives..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
            </div>
            
            <div>
                <label for="skills" class="block text-sm font-medium text-gray-700">Technical Skills</label>
                <input type="text" id="skills" name="skills"
                       value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>"
                       placeholder="e.g., JavaScript, React, Node.js, Python, Digital Marketing, SEO"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Separate skills with commas</p>
            </div>
            
            <!-- Professional Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700">LinkedIn Profile</label>
                    <input type="url" id="linkedin_url" name="linkedin_url"
                           value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>"
                           placeholder="https://linkedin.com/in/yourprofile"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="portfolio_url" class="block text-sm font-medium text-gray-700">Portfolio Website</label>
                    <input type="url" id="portfolio_url" name="portfolio_url"
                           value="<?php echo htmlspecialchars($profile['portfolio_url'] ?? ''); ?>"
                           placeholder="https://yourportfolio.com"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                </div>
            </div>
            
            <!-- Education History -->
            <div>
                <label for="education_history" class="block text-sm font-medium text-gray-700">Education Background</label>
                <textarea id="education_history" name="education_history" rows="3"
                          placeholder="List your educational qualifications, institutions, and graduation years..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['education_history'] ?? ''); ?></textarea>
            </div>
            
            <!-- Work History -->
            <div>
                <label for="work_history" class="block text-sm font-medium text-gray-700">Work Experience</label>
                <textarea id="work_history" name="work_history" rows="4"
                          placeholder="Describe your work experience, including job titles, companies, dates, and key responsibilities..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['work_history'] ?? ''); ?></textarea>
            </div>
            
            <!-- Additional Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="certifications" class="block text-sm font-medium text-gray-700">Additional Certifications</label>
                    <textarea id="certifications" name="certifications" rows="3"
                              placeholder="List any additional certifications, courses, or professional qualifications..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['certifications'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label for="languages" class="block text-sm font-medium text-gray-700">Languages</label>
                    <textarea id="languages" name="languages" rows="3"
                              placeholder="List languages you speak and your proficiency level..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['languages'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div>
                <label for="achievements" class="block text-sm font-medium text-gray-700">Achievements & Awards</label>
                <textarea id="achievements" name="achievements" rows="3"
                          placeholder="List any notable achievements, awards, or recognitions..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['achievements'] ?? ''); ?></textarea>
            </div>
            
            <div>
                <label for="references" class="block text-sm font-medium text-gray-700">References</label>
                <textarea id="references" name="references" rows="3"
                          placeholder="Provide professional references with names, titles, and contact information..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($profile['references'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex justify-between">
                <a href="dashboard.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i><?php echo $profile ? 'Update Profile' : 'Create Profile'; ?>
                </button>
            </div>
        </form>
    </div>
    
    <?php if($profile): ?>
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                <div>
                    <h4 class="font-medium text-blue-900">Profile Status</h4>
                    <p class="text-blue-700 text-sm">
                        Your profile is <?php echo $profile['is_visible'] ? 'visible' : 'hidden'; ?> in the graduate showcase. 
                        <?php if($profile['is_featured']): ?>
                            <span class="font-medium">It's currently featured on the homepage!</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>