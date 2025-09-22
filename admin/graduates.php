<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle graduate certification
if(isset($_POST['certify_graduate'])) {
    $user_id = $_POST['user_id'];
    $cohort_id = $_POST['cohort_id'];
    $skills_verified = trim($_POST['skills_verified']);
    $admin_notes = trim($_POST['admin_notes']);
    $certification_level = $_POST['certification_level'];
    $is_job_ready = isset($_POST['is_job_ready']) ? 1 : 0;
    
    // Generate certificate number
    $certificate_number = 'WOF-' . strtoupper(substr($certification_level, 0, 2)) . '-' . date('Y') . '-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);
    
    $db->query('INSERT INTO graduate_certifications (user_id, cohort_id, certification_date, certificate_number, skills_verified, admin_notes, is_job_ready, certification_level, created_by) 
               VALUES (:user_id, :cohort_id, CURDATE(), :certificate_number, :skills_verified, :admin_notes, :is_job_ready, :certification_level, :created_by)');
    $db->bind(':user_id', $user_id);
    $db->bind(':cohort_id', $cohort_id);
    $db->bind(':certificate_number', $certificate_number);
    $db->bind(':skills_verified', $skills_verified);
    $db->bind(':admin_notes', $admin_notes);
    $db->bind(':is_job_ready', $is_job_ready);
    $db->bind(':certification_level', $certification_level);
    $db->bind(':created_by', $_SESSION['user_id']);
    
    if($db->execute()) {
        $message = 'Graduate certified successfully! Certificate Number: ' . $certificate_number;
    } else {
        $error = 'Failed to certify graduate';
    }
}

// Get all graduates with their profiles and certifications
$db->query('SELECT u.*, gp.title, gp.bio, gp.skills, gp.is_visible, gp.is_featured,
           gc.certificate_number, gc.certification_level, gc.is_job_ready, gc.certification_date,
           (SELECT c.name FROM enrollments e JOIN cohorts c ON e.cohort_id = c.id WHERE e.user_id = u.id AND e.status = "completed" LIMIT 1) as completed_cohort,
           (SELECT c.id FROM enrollments e JOIN cohorts c ON e.cohort_id = c.id WHERE e.user_id = u.id AND e.status = "completed" LIMIT 1) as cohort_id
           FROM users u 
           LEFT JOIN graduate_profiles gp ON u.id = gp.user_id 
           LEFT JOIN graduate_certifications gc ON u.id = gc.user_id
           WHERE u.status = "graduated" 
           ORDER BY u.updated_at DESC');
$graduates = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Graduates</h1>
        <p class="text-gray-600 mt-2">Certify graduates and manage their job-ready status</p>
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
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <?php
        $total_graduates = count($graduates);
        $certified_graduates = count(array_filter($graduates, function($g) { return !empty($g['certificate_number']); }));
        $job_ready = count(array_filter($graduates, function($g) { return $g['is_job_ready']; }));
        $with_profiles = count(array_filter($graduates, function($g) { return !empty($g['title']); }));
        ?>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $total_graduates; ?></h3>
                    <p class="text-gray-600">Total Graduates</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $certified_graduates; ?></h3>
                    <p class="text-gray-600">Certified</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-briefcase text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $job_ready; ?></h3>
                    <p class="text-gray-600">Job Ready</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $with_profiles; ?></h3>
                    <p class="text-gray-600">With Profiles</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graduates Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Graduates</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Graduate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profile</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($graduates as $graduate): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($graduate['first_name'] . ' ' . $graduate['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($graduate['email']); ?></div>
                                    <?php if($graduate['title']): ?>
                                        <div class="text-sm text-blue-600"><?php echo htmlspecialchars($graduate['title']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($graduate['completed_cohort'] ?: 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <?php if($graduate['title']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Complete</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Incomplete</span>
                                    <?php endif; ?>
                                    
                                    <?php if($graduate['is_visible']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Visible</span>
                                    <?php endif; ?>
                                    
                                    <?php if($graduate['is_featured']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Featured</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($graduate['certificate_number']): ?>
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($graduate['certificate_number']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        Level: <?php echo ucfirst($graduate['certification_level']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-500">Not Certified</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($graduate['is_job_ready']): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Job Ready</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Not Ready</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    <?php if(!$graduate['certificate_number'] && $graduate['completed_cohort']): ?>
                                        <button onclick="openCertifyModal(<?php echo $graduate['id']; ?>, <?php echo $graduate['cohort_id']; ?>)" 
                                                class="text-green-600 hover:text-green-900">Certify</button>
                                    <?php endif; ?>
                                    
                                    <a href="generate_cv.php?user_id=<?php echo $graduate['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">Generate CV</a>
                                    
                                    <a href="../profile.php?user_id=<?php echo $graduate['id']; ?>" 
                                       class="text-primary hover:text-blue-900">View Profile</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Certify Graduate Modal -->
<div id="certifyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Certify Graduate</h3>
        <form method="POST">
            <input type="hidden" id="certifyUserId" name="user_id">
            <input type="hidden" id="certifyCohortId" name="cohort_id">
            
            <div class="mb-4">
                <label for="skills_verified" class="block text-sm font-medium text-gray-700">Skills Verified</label>
                <textarea id="skills_verified" name="skills_verified" rows="3" required
                          placeholder="List the skills and competencies verified for this graduate"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="mb-4">
                <label for="certification_level" class="block text-sm font-medium text-gray-700">Certification Level</label>
                <select id="certification_level" name="certification_level" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="basic">Basic</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_job_ready" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700">Mark as Job Ready</span>
                </label>
            </div>
            
            <div class="mb-4">
                <label for="admin_notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                <textarea id="admin_notes" name="admin_notes" rows="2"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCertifyModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" name="certify_graduate" 
                        class="px-4 py-2 rounded-lg text-white bg-green-600 hover:bg-green-700">Certify Graduate</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCertifyModal(userId, cohortId) {
    document.getElementById('certifyUserId').value = userId;
    document.getElementById('certifyCohortId').value = cohortId;
    
    const modal = document.getElementById('certifyModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCertifyModal() {
    const modal = document.getElementById('certifyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php include '../includes/footer.php'; ?>