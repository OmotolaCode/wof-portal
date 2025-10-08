<?php 
include 'includes/header.php';

$auth->requireAdmin();

$db = new Database();

if(!isset($_GET['id'])) {
    header('Location: applications.php');
    exit();
}

$application_id = $_GET['id'];

// Get application details with user and cohort info
$db->query('SELECT a.*, u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email, 
           c.name as cohort_name, c.start_date, c.duration_months
           FROM applications a 
           JOIN users u ON a.user_id = u.id 
           JOIN cohorts c ON a.cohort_id = c.id 
           WHERE a.id = :id');
$db->bind(':id', $application_id);
$application = $db->single();

if(!$application) {
    header('Location: applications.php');
    exit();
}

// Helper function to format enum values
function formatEnumValue($value) {
    return ucwords(str_replace('_', ' ', $value));
}

// Helper function to get qualification text
function getQualificationText($value) {
    $qualifications = [
        'olevel_ssce' => "O'Level/SSCE",
        'undergraduate' => 'Undergraduate',
        'national_diploma' => 'National Diploma (ND)',
        'nce' => 'National Certificate of Education (NCE)',
        'hnd' => 'Higher National Diploma (HND)',
        'degree' => 'Degree (BSc, B.Tech, B.Edu)',
        'postgraduate' => 'Post Graduate',
        'other' => 'Other'
    ];
    return isset($qualifications[$value]) ? $qualifications[$value] : $value;
}

// Helper function to get course text
function getCourseText($value) {
    $courses = [
        'desktop_publishing' => 'Desktop Publishing',
        'graphics_design_ui_ux' => 'Graphics Design - UI/UX',
        'web_design' => 'Web Design',
        'digital_marketing' => 'Digital Marketing/Content Creation',
        'photography_video_editing' => 'Photography/Video Editing',
        'frontend_development' => 'Front-End Software Development',
        'backend_development' => 'Back-End Software Development',
        'fullstack_development' => 'Fullstack Software Development',
        'mobile_app_development' => 'Mobile App Development',
        'data_analytics' => 'Data Analytics'
    ];
    return isset($courses[$value]) ? $courses[$value] : $value;
}

// Helper function to get session text
function getSessionText($value) {
    $sessions = [
        'morning_10_12' => 'Morning (10am - 12pm)',
        'afternoon_230_430' => 'Afternoon (2:30pm - 4:30pm)',
        'weekends_8_1' => 'Weekends only (8am - 1pm)'
    ];
    return isset($sessions[$value]) ? $sessions[$value] : $value;
}
?>

<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Application Details</h1>
                <p class="text-gray-600 mt-2">Comprehensive application review</p>
            </div>
            <a href="applications.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Applications
            </a>
        </div>
    </div>
    
    <!-- Application Status -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Application Status</h2>
                <p class="text-gray-600">Applied: <?php echo date('M j, Y g:i A', strtotime($application['applied_at'])); ?></p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-medium
                <?php if($application['status'] === 'approved'): ?>bg-green-100 text-green-800<?php endif; ?>
                <?php if($application['status'] === 'pending'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                <?php if($application['status'] === 'rejected'): ?>bg-red-100 text-red-800<?php endif; ?>">
                <?php echo ucfirst($application['status']); ?>
            </span>
        </div>
        
        <?php if($application['admin_notes']): ?>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Admin Notes</h4>
                <p class="text-gray-700"><?php echo htmlspecialchars($application['admin_notes']); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Personal Information</h3>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Surname</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['surname'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">First Name</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['first_name'] ?: 'N/A'); ?></p>
                    </div>
                </div>
                
                <?php if($application['other_names']): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Other Names</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['other_names']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Gender</label>
                        <p class="text-gray-900"><?php echo formatEnumValue($application['gender'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-gray-900"><?php echo $application['date_of_birth'] ? date('M j, Y', strtotime($application['date_of_birth'])) : 'N/A'; ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone Number</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['phone_number'] ?: 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['email_address'] ?: 'N/A'); ?></p>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">State of Origin</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($application['state_of_origin'] ?: 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Contact Address</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($application['contact_address'] ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Educational Background -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Educational Background</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Highest Qualification</label>
                    <p class="text-gray-900"><?php echo getQualificationText($application['highest_qualification'] ?: 'N/A'); ?></p>
                    <?php if($application['qualification_other']): ?>
                        <p class="text-sm text-gray-600 mt-1">Other: <?php echo htmlspecialchars($application['qualification_other']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Educational Background Details</label>
                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($application['education_background'] ?: 'N/A')); ?></p>
                </div>
                
                <?php if($application['work_experience']): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Work Experience</label>
                        <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($application['work_experience'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- English Proficiency -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">English Language Proficiency</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Speaking Level</label>
                    <p class="text-gray-900">
                        <?php 
                        $speaking_levels = [
                            'natural' => 'It happens naturally without me noticing',
                            'easier_but_confused' => 'It\'s easier but I still get confused sometimes',
                            'quite_tricky' => 'I find it quite tricky and have to think about it a lot',
                            'hard_to_string' => 'I find it hard to string sentences together'
                        ];
                        echo isset($speaking_levels[$application['english_speaking_level']]) ? $speaking_levels[$application['english_speaking_level']] : 'N/A';
                        ?>
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Understanding Level</label>
                    <p class="text-gray-900">
                        <?php 
                        $understanding_levels = [
                            'natural_as_native' => 'Understanding English is as natural as my native language',
                            'most_time_but_lost' => 'Most of the time, but sometimes I get lost if everyone is speaking fast',
                            'slow_and_clear' => 'I can understand when people speak slowly and clearly',
                            'lost_easily' => 'I get lost easily and usually only understand a few words'
                        ];
                        echo isset($understanding_levels[$application['english_understanding_level']]) ? $understanding_levels[$application['english_understanding_level']] : 'N/A';
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Course & Technical Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Course & Technical Information</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Cohort Applied For</label>
                    <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($application['cohort_name']); ?></p>
                    <p class="text-sm text-gray-600">Starts: <?php echo date('M j, Y', strtotime($application['start_date'])); ?> (<?php echo $application['duration_months']; ?> months)</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Course of Choice</label>
                    <p class="text-gray-900"><?php echo getCourseText($application['course_choice'] ?: 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Preferred Session</label>
                    <p class="text-gray-900"><?php echo getSessionText($application['preferred_session'] ?: 'N/A'); ?></p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Computer Understanding</label>
                    <p class="text-gray-900">
                        <?php 
                        $computer_levels = [
                            'can_operate' => 'Yes, I can operate the computer system',
                            'have_personal_effective' => 'I have a personal computer and I use it effectively',
                            'no_personal_but_operate' => 'I do not have a personal computer but I can operate a computer system well',
                            'never_operated' => 'No, I have never operated a computer system',
                            'other' => 'Other'
                        ];
                        echo isset($computer_levels[$application['computer_understanding']]) ? $computer_levels[$application['computer_understanding']] : 'N/A';
                        ?>
                    </p>
                    <?php if($application['computer_understanding_other']): ?>
                        <p class="text-sm text-gray-600 mt-1">Other: <?php echo htmlspecialchars($application['computer_understanding_other']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Credentials Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Uploaded Credentials</h3>

            <?php if($application['credential_filename']): ?>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <?php
                                $file_ext = pathinfo($application['credential_filename'], PATHINFO_EXTENSION);
                                if($file_ext === 'pdf'):
                                ?>
                                    <i class="fas fa-file-pdf text-white text-2xl"></i>
                                <?php else: ?>
                                    <i class="fas fa-file-image text-white text-2xl"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">Educational Certificate</h4>
                                <p class="text-sm text-gray-600">
                                    Uploaded: <?php echo $application['credential_uploaded_at'] ? date('M j, Y g:i A', strtotime($application['credential_uploaded_at'])) : 'N/A'; ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    Type: <?php echo strtoupper($file_ext); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="view_credential.php?id=<?php echo $application['id']; ?>" target="_blank"
                           class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-eye mr-2"></i>View Document
                        </a>
                        <a href="../uploads/credentials/<?php echo htmlspecialchars($application['credential_filename']); ?>" download
                           class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800"><i class="fas fa-exclamation-triangle mr-2"></i>No credential uploaded</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Marketing & Motivation -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">Marketing Source & Motivation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500">How They Heard About Us</label>
                    <p class="text-gray-900">
                        <?php 
                        $marketing_sources = [
                            'flyers' => 'Flyers',
                            'banner' => 'Banner',
                            'road_jingle' => 'Road jingle',
                            'social_media' => 'Social Media Advert',
                            'radio_jingle' => 'Radio Jingle',
                            'friend_relative' => 'Through a friend, relative or someone',
                            'wof_batch1_student' => 'Through a WOF batch 1 students',
                            'other' => 'Other'
                        ];
                        echo isset($marketing_sources[$application['how_heard_about']]) ? $marketing_sources[$application['how_heard_about']] : 'N/A';
                        ?>
                    </p>
                    <?php if($application['how_heard_other']): ?>
                        <p class="text-sm text-gray-600 mt-1">Other: <?php echo htmlspecialchars($application['how_heard_other']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Application Date</label>
                    <p class="text-gray-900"><?php echo date('M j, Y g:i A', strtotime($application['applied_at'])); ?></p>
                    <?php if($application['reviewed_at']): ?>
                        <p class="text-sm text-gray-600 mt-1">Reviewed: <?php echo date('M j, Y g:i A', strtotime($application['reviewed_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="text-sm font-medium text-gray-500">Motivation</label>
                <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($application['motivation'] ?: 'N/A')); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <?php if($application['status'] === 'pending'): ?>
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Review Application</h3>
            <div class="flex space-x-4">
                <button onclick="openModal(<?php echo $application['id']; ?>, 'approve')" 
                        class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Approve Application
                </button>
                <button onclick="openModal(<?php echo $application['id']; ?>, 'reject')" 
                        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i>Reject Application
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Application Review -->
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4"></h3>
        <form method="POST" action="applications.php">
            <input type="hidden" id="applicationId" name="application_id" value="<?php echo $application['id']; ?>">
            <input type="hidden" id="actionType" name="action">
            
            <div class="mb-4">
                <label for="admin_notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                <textarea id="admin_notes" name="admin_notes" rows="3" 
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" id="submitBtn" 
                        class="px-4 py-2 rounded-lg text-white">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(applicationId, action) {
    // Confirmation before opening modal
    const confirmMessage = action === 'approve'
        ? 'Are you sure you want to APPROVE this application? The student will be enrolled in the cohort.'
        : 'Are you sure you want to REJECT this application? This action cannot be easily undone.';

    if(!confirm(confirmMessage)) {
        return;
    }

    document.getElementById('applicationId').value = applicationId;
    document.getElementById('actionType').value = action;

    const modal = document.getElementById('reviewModal');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');

    if(action === 'approve') {
        title.textContent = 'Approve Application';
        submitBtn.textContent = 'Approve';
        submitBtn.className = 'px-4 py-2 rounded-lg text-white bg-green-600 hover:bg-green-700';
    } else {
        title.textContent = 'Reject Application';
        submitBtn.textContent = 'Reject';
        submitBtn.className = 'px-4 py-2 rounded-lg text-white bg-red-600 hover:bg-red-700';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('reviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php include '../includes/footer.php'; ?>