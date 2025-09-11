<?php 
require_once 'classes/Database.php';
include 'includes/header.php';

$db = new Database();

// Get graduate profiles
$db->query('SELECT gp.*, u.first_name, u.last_name, u.email 
           FROM graduate_profiles gp 
           JOIN users u ON gp.user_id = u.id 
           WHERE gp.is_visible = 1 
           ORDER BY gp.is_featured DESC, gp.created_at DESC');
$graduates = $db->resultSet();

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$skill_filter = isset($_GET['skill']) ? trim($_GET['skill']) : '';

if($search || $skill_filter) {
    $query = 'SELECT gp.*, u.first_name, u.last_name, u.email 
              FROM graduate_profiles gp 
              JOIN users u ON gp.user_id = u.id 
              WHERE gp.is_visible = 1';
    
    if($search) {
        $query .= ' AND (u.first_name LIKE :search OR u.last_name LIKE :search OR gp.title LIKE :search OR gp.bio LIKE :search)';
    }
    
    if($skill_filter) {
        $query .= ' AND gp.skills LIKE :skill';
    }
    
    $query .= ' ORDER BY gp.is_featured DESC, gp.created_at DESC';
    
    $db->query($query);
    
    if($search) {
        $db->bind(':search', '%' . $search . '%');
    }
    if($skill_filter) {
        $db->bind(':skill', '%' . $skill_filter . '%');
    }
    
    $graduates = $db->resultSet();
}

// Get all unique skills for filter dropdown
$db->query('SELECT DISTINCT skills FROM graduate_profiles WHERE is_visible = 1 AND skills IS NOT NULL');
$all_skills_raw = $db->resultSet();
$all_skills = [];
foreach($all_skills_raw as $skill_row) {
    $skills = explode(',', $skill_row['skills']);
    foreach($skills as $skill) {
        $skill = trim($skill);
        if($skill && !in_array($skill, $all_skills)) {
            $all_skills[] = $skill;
        }
    }
}
sort($all_skills);
?>

<div class="bg-gradient-to-r from-primary to-secondary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-4">Graduate Showcase</h1>
        <p class="text-xl mb-8">Meet our successful alumni and discover their professional journeys</p>
        
        <!-- Search and Filter -->
        <div class="max-w-2xl mx-auto">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search by name, title, or bio..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                </div>
                <div class="sm:w-48">
                    <select name="skill" class="w-full px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                        <option value="">All Skills</option>
                        <?php foreach($all_skills as $skill): ?>
                            <option value="<?php echo htmlspecialchars($skill); ?>" 
                                    <?php echo $skill_filter === $skill ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($skill); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <?php if(empty($graduates)): ?>
        <div class="text-center py-12">
            <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">No Graduates Found</h3>
            <p class="text-gray-600">
                <?php if($search || $skill_filter): ?>
                    Try adjusting your search criteria or <a href="showcase.php" class="text-primary hover:underline">view all graduates</a>.
                <?php else: ?>
                    Check back soon as our graduates create their professional profiles.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($graduates as $graduate): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <?php if($graduate['is_featured']): ?>
                        <div class="bg-gradient-to-r from-accent to-yellow-500 text-white text-center py-2">
                            <i class="fas fa-star mr-1"></i>Featured Graduate
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 text-xl font-semibold">
                                <?php if($graduate['profile_image']): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($graduate['profile_image']); ?>" 
                                         alt="Profile" class="w-16 h-16 rounded-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($graduate['first_name'], 0, 1) . substr($graduate['last_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($graduate['first_name'] . ' ' . $graduate['last_name']); ?>
                                </h3>
                                <?php if($graduate['title']): ?>
                                    <p class="text-sm text-primary font-medium"><?php echo htmlspecialchars($graduate['title']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($graduate['bio']): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo htmlspecialchars(substr($graduate['bio'], 0, 150)) . (strlen($graduate['bio']) > 150 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if($graduate['skills']): ?>
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Skills</h4>
                                <div class="flex flex-wrap gap-1">
                                    <?php 
                                    $skills = array_slice(explode(',', $graduate['skills']), 0, 4);
                                    foreach($skills as $skill): 
                                        $skill = trim($skill);
                                        if($skill):
                                    ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <?php if($graduate['linkedin_url']): ?>
                                    <a href="<?php echo htmlspecialchars($graduate['linkedin_url']); ?>" 
                                       target="_blank" class="text-blue-600 hover:text-blue-800">
                                        <i class="fab fa-linkedin text-lg"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($graduate['portfolio_url']): ?>
                                    <a href="<?php echo htmlspecialchars($graduate['portfolio_url']); ?>" 
                                       target="_blank" class="text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-external-link-alt text-lg"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($graduate['cv_filename']): ?>
                                    <a href="uploads/cvs/<?php echo htmlspecialchars($graduate['cv_filename']); ?>" 
                                       target="_blank" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-file-pdf text-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-xs text-gray-500">
                                <?php if($graduate['years_experience']): ?>
                                    <?php echo $graduate['years_experience']; ?>+ years exp.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>