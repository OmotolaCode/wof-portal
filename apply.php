<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$auth->requireAuth();
// if($_SESSION['user_status'] !== 'approved') {
//     header('Location: dashboard.php');
//     exit();
// }

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
    // Basic validation
    $required_fields = [
        'surname', 'first_name', 'gender', 'date_of_birth', 'phone_number',
        'email_address', 'contact_address', 'state_of_origin', 'highest_qualification',
        'english_speaking_level', 'english_understanding_level', 'course_choice',
        'preferred_session', 'computer_understanding', 'how_heard_about',
        'motivation', 'education_background'
    ];

    $missing_fields = [];
    foreach($required_fields as $field) {
        if(empty(trim($_POST[$field]))) {
            $missing_fields[] = $field;
        }
    }

    // Validate file upload
    if(!isset($_FILES['credential_file']) || $_FILES['credential_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $missing_fields[] = 'credential file';
    }

    if(!empty($missing_fields)) {
        $error = 'Please fill in all required fields: ' . implode(', ', $missing_fields);
    } else {
        // Handle credential file upload
        $credential_filename = null;
        $credential_file_type = null;

        if(isset($_FILES['credential_file']) && $_FILES['credential_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['credential_file'];
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if(!in_array($file['type'], $allowed_types)) {
                $error = 'Invalid file type. Please upload PDF, JPG, JPEG, or PNG file.';
            } elseif($file['size'] > $max_size) {
                $error = 'File size exceeds 5MB limit.';
            } else {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/credentials/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $credential_filename = 'credential_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $credential_filename;

                if(!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $error = 'Failed to upload credential file. Please try again.';
                } else {
                    $credential_file_type = $file['type'];
                }
            }
        }

        if(empty($error)) {
        // Collect all form data
        $surname = trim($_POST['surname']);
        $first_name = trim($_POST['first_name']);
        $other_names = trim($_POST['other_names']);
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $phone_number = trim($_POST['phone_number']);
        $email_address = trim($_POST['email_address']);
        $contact_address = trim($_POST['contact_address']);
        $state_of_origin = trim($_POST['state_of_origin']);
        $highest_qualification = $_POST['highest_qualification'];
        $qualification_other = trim($_POST['qualification_other']);
        $english_speaking_level = $_POST['english_speaking_level'];
        $english_understanding_level = $_POST['english_understanding_level'];
        $course_choice = $_POST['course_choice'];
        $preferred_session = $_POST['preferred_session'];
        $computer_understanding = $_POST['computer_understanding'];
        $computer_understanding_other = trim($_POST['computer_understanding_other']);
        $how_heard_about = $_POST['how_heard_about'];
        $how_heard_other = trim($_POST['how_heard_other']);
        $motivation = trim($_POST['motivation']);
        $education_background = trim($_POST['education_background']);
        $work_experience = trim($_POST['work_experience']);
        
        // Insert comprehensive application
        $db->query('INSERT INTO applications (
            user_id, cohort_id, surname, first_name, other_names, gender, date_of_birth,
            phone_number, email_address, contact_address, state_of_origin, highest_qualification,
            qualification_other, english_speaking_level, english_understanding_level, course_choice,
            preferred_session, computer_understanding, computer_understanding_other, how_heard_about,
            how_heard_other, motivation, education_background, work_experience,
            credential_filename, credential_file_type, credential_uploaded_at
        ) VALUES (
            :user_id, :cohort_id, :surname, :first_name, :other_names, :gender, :date_of_birth,
            :phone_number, :email_address, :contact_address, :state_of_origin, :highest_qualification,
            :qualification_other, :english_speaking_level, :english_understanding_level, :course_choice,
            :preferred_session, :computer_understanding, :computer_understanding_other, :how_heard_about,
            :how_heard_other, :motivation, :education_background, :work_experience,
            :credential_filename, :credential_file_type, NOW()
        )');
        
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':cohort_id', $cohort_id);
        $db->bind(':surname', $surname);
        $db->bind(':first_name', $first_name);
        $db->bind(':other_names', $other_names);
        $db->bind(':gender', $gender);
        $db->bind(':date_of_birth', $date_of_birth);
        $db->bind(':phone_number', $phone_number);
        $db->bind(':email_address', $email_address);
        $db->bind(':contact_address', $contact_address);
        $db->bind(':state_of_origin', $state_of_origin);
        $db->bind(':highest_qualification', $highest_qualification);
        $db->bind(':qualification_other', $qualification_other);
        $db->bind(':english_speaking_level', $english_speaking_level);
        $db->bind(':english_understanding_level', $english_understanding_level);
        $db->bind(':course_choice', $course_choice);
        $db->bind(':preferred_session', $preferred_session);
        $db->bind(':computer_understanding', $computer_understanding);
        $db->bind(':computer_understanding_other', $computer_understanding_other);
        $db->bind(':how_heard_about', $how_heard_about);
        $db->bind(':how_heard_other', $how_heard_other);
        $db->bind(':motivation', $motivation);
        $db->bind(':education_background', $education_background);
        $db->bind(':work_experience', $work_experience);
        $db->bind(':credential_filename', $credential_filename);
        $db->bind(':credential_file_type', $credential_file_type);

        if($db->execute()) {
            $message = 'Application submitted successfully! You will be notified once it is reviewed.';
        } else {
            $error = 'Failed to submit application. Please try again.';
        }
        }
    }
}
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Apply to Cohort</h1>
        <p class="text-gray-600 mt-2">Complete the comprehensive application form to join this training program</p>
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
        <div class="flex justify-between">
            <a href="dashboard.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if(!$existing_application && !$message): ?>
        <!-- Comprehensive Application Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Comprehensive Application Form</h3>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="surname" class="block text-sm font-medium text-gray-700">Surname *</label>
                            <input type="text" id="surname" name="surname" value="<?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?>" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>

                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : ''; ?>" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="other_names" class="block text-sm font-medium text-gray-700">Other Names</label>
                            <input type="text" id="other_names" name="other_names"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                            <select id="gender" name="gender" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                            <input type="tel" id="phone_number" name="phone_number" value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : ''; ?>" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="email_address" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email_address" name="email_address" value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''?>" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="state_of_origin" class="block text-sm font-medium text-gray-700">State of Origin *</label>
                            <input type="text" id="state_of_origin" name="state_of_origin" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="contact_address" class="block text-sm font-medium text-gray-700">Contact Address *</label>
                        <textarea id="contact_address" name="contact_address" rows="3" required
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                
                <!-- Educational Background -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Educational Background</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="highest_qualification" class="block text-sm font-medium text-gray-700">Highest Educational Qualification *</label>
                            <select id="highest_qualification" name="highest_qualification" required onchange="toggleOtherQualification()"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select Qualification</option>
                                <option value="olevel_ssce">O'Level/SSCE</option>
                                <option value="undergraduate">Undergraduate</option>
                                <option value="national_diploma">National Diploma (ND)</option>
                                <option value="nce">National Certificate of Education (NCE)</option>
                                <option value="hnd">Higher National Diploma (HND)</option>
                                <option value="degree">Degree (BSc, B.Tech, B.Edu)</option>
                                <option value="postgraduate">Post Graduate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div id="other_qualification_div" style="display: none;">
                            <label for="qualification_other" class="block text-sm font-medium text-gray-700">Please specify other qualification</label>
                            <input type="text" id="qualification_other" name="qualification_other"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="education_background" class="block text-sm font-medium text-gray-700">Educational Background Details *</label>
                        <textarea id="education_background" name="education_background" rows="3" required
                                  placeholder="Describe your educational qualifications, relevant courses, and any certifications..."
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                
                <!-- English Proficiency -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">English Language Proficiency</h4>
                    <div class="space-y-6">
                        <div>
                            <label for="english_speaking_level" class="block text-sm font-medium text-gray-700">How easy is it for you to speak in English? *</label>
                            <select id="english_speaking_level" name="english_speaking_level" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select your speaking level</option>
                                <option value="natural">It happens naturally without me noticing</option>
                                <option value="easier_but_confused">It's a lot easier than when I started learning, but I still get confused sometimes</option>
                                <option value="quite_tricky">I find it quite tricky and have to think about it a lot</option>
                                <option value="hard_to_string">I find it hard to string sentences together and use grammar rules correctly</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="english_understanding_level" class="block text-sm font-medium text-gray-700">Can you understand conversations easily? *</label>
                            <select id="english_understanding_level" name="english_understanding_level" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select your understanding level</option>
                                <option value="natural_as_native">Yes - understanding English is as natural as my native language</option>
                                <option value="most_time_but_lost">Most of the time, but sometimes I get lost if everyone is speaking fast</option>
                                <option value="slow_and_clear">I can understand when people speak slowly and clearly</option>
                                <option value="lost_easily">I get lost easily and usually only understand a few words in conversation</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Course Preferences -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Course Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="course_choice" class="block text-sm font-medium text-gray-700">Course of Choice *</label>
                            <select id="course_choice" name="course_choice" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select Course</option>
                                <option value="desktop_publishing">Desktop Publishing</option>
                                <option value="graphics_design_ui_ux">Graphics Design - UI/UX</option>
                                <option value="web_design">Web Design</option>
                                <option value="digital_marketing">Digital Marketing/Content Creation</option>
                                <option value="photography_video_editing">Photography/Video Editing</option>
                                <option value="frontend_development">Front-End Software Development</option>
                                <option value="backend_development">Back-End Software Development</option>
                                <option value="fullstack_development">Fullstack Software Development</option>
                                <option value="mobile_app_development">Mobile App Development</option>
                                <option value="data_analytics">Data Analytics</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="preferred_session" class="block text-sm font-medium text-gray-700">Preferred Class Session *</label>
                            <select id="preferred_session" name="preferred_session" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select Session</option>
                                <option value="morning_10_12">Morning (10am - 12pm)</option>
                                <option value="afternoon_230_430">Afternoon (2:30pm - 4:30pm)</option>
                                <option value="weekends_8_1">Weekends only (8am - 1pm)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Computer Literacy -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Computer Literacy</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="computer_understanding" class="block text-sm font-medium text-gray-700">Do you have basic understanding of computer systems? *</label>
                            <select id="computer_understanding" name="computer_understanding" required onchange="toggleOtherComputer()"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select your level</option>
                                <option value="can_operate">Yes, I can operate the computer system</option>
                                <option value="have_personal_effective">I have a personal computer and I use it effectively</option>
                                <option value="no_personal_but_operate">I do not have a personal computer but I can operate a computer system well</option>
                                <option value="never_operated">No, I have never operated a computer system</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div id="other_computer_div" style="display: none;">
                            <label for="computer_understanding_other" class="block text-sm font-medium text-gray-700">Please specify</label>
                            <input type="text" id="computer_understanding_other" name="computer_understanding_other"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <!-- Marketing Source -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">How did you hear about us?</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="how_heard_about" class="block text-sm font-medium text-gray-700">How did you hear about the ICT Hub? *</label>
                            <select id="how_heard_about" name="how_heard_about" required onchange="toggleOtherHeard()"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Select source</option>
                                <option value="flyers">Flyers</option>
                                <option value="banner">Banner</option>
                                <option value="road_jingle">Road jingle</option>
                                <option value="social_media">Social Media Advert</option>
                                <option value="radio_jingle">Radio Jingle</option>
                                <option value="friend_relative">Through a friend, relative or someone</option>
                                <option value="wof_batch1_student">Through a WOF batch 1 students</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div id="other_heard_div" style="display: none;">
                            <label for="how_heard_other" class="block text-sm font-medium text-gray-700">Please specify</label>
                            <input type="text" id="how_heard_other" name="how_heard_other"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <!-- Credentials Upload -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Upload Your Credentials</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i><strong>Required Document:</strong></p>
                        <ul class="text-sm text-blue-700 space-y-1 ml-6">
                            <li>Upload your educational certificate or qualification document</li>
                            <li>Accepted formats: PDF, JPG, JPEG, PNG (Max size: 5MB)</li>
                            <li>Ensure document is clear and legible</li>
                        </ul>
                    </div>
                    <div>
                        <label for="credential_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Educational Certificate/Credential *
                            <span class="text-xs text-gray-500">(PDF, JPG, JPEG, PNG - Max 5MB)</span>
                        </label>
                        <input type="file" id="credential_file" name="credential_file"
                               accept=".pdf,.jpg,.jpeg,.png" required
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-primary focus:border-primary p-2">
                        <p class="text-xs text-gray-500 mt-1">Admin will be able to view your document directly in the portal</p>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h4>
                    <div class="space-y-6">
                        <div>
                            <label for="motivation" class="block text-sm font-medium text-gray-700">Why do you want to join this program? *</label>
                            <textarea id="motivation" name="motivation" rows="4" required
                                      placeholder="Explain your motivation for joining this cohort, your career goals, and how this program aligns with your objectives..."
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        
                        <div>
                            <label for="work_experience" class="block text-sm font-medium text-gray-700">Work Experience (Optional)</label>
                            <textarea id="work_experience" name="work_experience" rows="3"
                                      placeholder="Describe any relevant work experience, internships, or projects..."
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Application Requirements</h4>
                    <ul class="text-blue-700 text-sm space-y-1">
                        <!-- <li><i class="fas fa-check mr-2"></i>Must be an approved user</li> -->
                        <li><i class="fas fa-check mr-2"></i>Complete all required fields accurately</li>
                        <li><i class="fas fa-check mr-2"></i>Demonstrate genuine interest and commitment</li>
                        <li><i class="fas fa-check mr-2"></i>Applications are reviewed by our Board of Directors</li>
                        <li><i class="fas fa-check mr-2"></i>Successful applicants will be contacted for enrollment</li>
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

<script>
function toggleOtherQualification() {
    const select = document.getElementById('highest_qualification');
    const otherDiv = document.getElementById('other_qualification_div');
    
    if(select.value === 'other') {
        otherDiv.style.display = 'block';
        document.getElementById('qualification_other').required = true;
    } else {
        otherDiv.style.display = 'none';
        document.getElementById('qualification_other').required = false;
    }
}

function toggleOtherComputer() {
    const select = document.getElementById('computer_understanding');
    const otherDiv = document.getElementById('other_computer_div');
    
    if(select.value === 'other') {
        otherDiv.style.display = 'block';
        document.getElementById('computer_understanding_other').required = true;
    } else {
        otherDiv.style.display = 'none';
        document.getElementById('computer_understanding_other').required = false;
    }
}

function toggleOtherHeard() {
    const select = document.getElementById('how_heard_about');
    const otherDiv = document.getElementById('other_heard_div');
    
    if(select.value === 'other') {
        otherDiv.style.display = 'block';
        document.getElementById('how_heard_other').required = true;
    } else {
        otherDiv.style.display = 'none';
        document.getElementById('how_heard_other').required = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>