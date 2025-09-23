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

<!-- Hero Section with Professional Design -->
<div class="relative bg-gradient-to-br from-primary via-blue-700 to-secondary text-white py-20 overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full"></div>
        <div class="absolute bottom-20 right-20 w-24 h-24 bg-coral rounded-full"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-accent rounded-full"></div>
        <div class="absolute top-20 right-1/3 w-20 h-20 bg-emerald-400 rounded-full"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="relative z-10">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-4xl text-white"></i>
                </div>
            </div>
            <h1 class="text-5xl font-bold mb-6 leading-tight">
                <span class="text-white">Job-Ready</span>
                <span class="text-coral block">Graduates</span>
            </h1>
            <p class="text-xl mb-4 font-medium text-blue-100">Certified • Skilled • Employment-Ready</p>
            <p class="text-lg mb-8 max-w-4xl mx-auto leading-relaxed text-blue-50">
                Discover our exceptional graduates who have completed rigorous training programs and are ready to make 
                an immediate impact in your organization. Each graduate is certified, skilled, and prepared for the modern workplace.
            </p>
            
            <!-- Key Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 max-w-3xl mx-auto">
                <div class="bg-white bg-opacity-10 rounded-lg p-4 backdrop-blur-sm">
                    <div class="text-3xl font-bold text-coral">500+</div>
                    <div class="text-blue-100">Certified Graduates</div>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-4 backdrop-blur-sm">
                    <div class="text-3xl font-bold text-emerald-400">95%</div>
                    <div class="text-blue-100">Employment Rate</div>
                </div>
                <div class="bg-white bg-opacity-10 rounded-lg p-4 backdrop-blur-sm">
                    <div class="text-3xl font-bold text-accent">50+</div>
                    <div class="text-blue-100">Partner Companies</div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="max-w-3xl mx-auto relative z-10">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search by name, title, or bio..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-6 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-4 focus:ring-white focus:ring-opacity-50 shadow-lg">
                </div>
                <div class="sm:w-48">
                    <select name="skill" class="w-full px-6 py-4 rounded-xl text-gray-900 focus:outline-none focus:ring-4 focus:ring-white focus:ring-opacity-50 shadow-lg">
                        <option value="">All Skills</option>
                        <?php foreach($all_skills as $skill): ?>
                            <option value="<?php echo htmlspecialchars($skill); ?>" 
                                    <?php echo $skill_filter === $skill ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($skill); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-white text-primary px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
            <p class="text-blue-100 mt-4 text-center">
                <i class="fas fa-briefcase mr-2"></i>
                Find job-ready graduates trained by Whoba Ogo Foundation - Ready to hire today!
            </p>
        </div>
    </div>
</div>

<!-- Graduates Section -->
<div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Meet Our Certified Graduates</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Each graduate has completed our comprehensive training program and passed rigorous assessments. 
                They are ready to contribute immediately to your organization's success.
            </p>
        </div>
        
    <?php if(empty($graduates)): ?>
        <div class="text-center py-16">
            <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-graduation-cap text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-3xl font-semibold text-gray-900 mb-4">No Graduates Found</h3>
            <div class="max-w-md mx-auto">
                <?php if($search || $skill_filter): ?>
                    <p class="text-gray-600 mb-6">
                        Try adjusting your search criteria or explore all our certified graduates.
                    </p>
                    <a href="showcase.php" class="bg-primary text-white px-8 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-all transform hover:scale-105">
                        View All Graduates
                    </a>
                <?php else: ?>
                    <p class="text-gray-600 mb-6">
                        Our graduates are currently updating their professional profiles. Check back soon to discover amazing talent!
                    </p>
                    <a href="register.php" class="bg-secondary text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-all transform hover:scale-105">
                        Join Our Next Cohort
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php foreach($graduates as $graduate): ?>
                <div class="graduate-card bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300">
                    <?php if($graduate['is_featured']): ?>
                        <div class="bg-gradient-to-r from-accent to-yellow-500 text-white text-center py-3">
                            <i class="fas fa-star mr-2"></i>⭐ Featured Graduate ⭐
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                <?php if($graduate['profile_image']): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($graduate['profile_image']); ?>" 
                                         alt="Profile" class="w-20 h-20 rounded-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($graduate['first_name'], 0, 1) . substr($graduate['last_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="ml-6 flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-1">
                                    <?php echo htmlspecialchars($graduate['first_name'] . ' ' . $graduate['last_name']); ?>
                                </h3>
                                <?php if($graduate['title']): ?>
                                    <p class="text-primary font-semibold"><?php echo htmlspecialchars($graduate['title']); ?></p>
                                <?php endif; ?>
                                <?php if($graduate['is_job_ready']): ?>
                                    <span class="job-ready-badge inline-block px-3 py-1 text-xs text-white rounded-full mt-2">
                                        <i class="fas fa-check-circle mr-1"></i>Job Ready
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($graduate['bio']): ?>
                            <p class="text-gray-700 mb-6 leading-relaxed">
                                <?php echo htmlspecialchars(substr($graduate['bio'], 0, 150)) . (strlen($graduate['bio']) > 150 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if($graduate['skills']): ?>
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Core Skills</h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $skills = array_slice(explode(',', $graduate['skills']), 0, 4);
                                    foreach($skills as $skill): 
                                        $skill = trim($skill);
                                        if($skill):
                                    ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full font-medium">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center space-x-4">
                                <?php if($graduate['linkedin_url']): ?>
                                    <a href="<?php echo htmlspecialchars($graduate['linkedin_url']); ?>" 
                                       target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($graduate['portfolio_url']): ?>
                                    <a href="<?php echo htmlspecialchars($graduate['portfolio_url']); ?>" 
                                       target="_blank" class="text-gray-600 hover:text-gray-800 transition-colors">
                                        <i class="fas fa-external-link-alt text-xl"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($graduate['cv_filename']): ?>
                                    <a href="uploads/cvs/<?php echo htmlspecialchars($graduate['cv_filename']); ?>" 
                                       target="_blank" class="text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-file-pdf text-xl"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-sm text-gray-500 font-medium">
                                <?php if($graduate['years_experience']): ?>
                                    <i class="fas fa-briefcase mr-1"></i><?php echo $graduate['years_experience']; ?>+ years
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Call to Action -->
        <div class="text-center">
            <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-8 text-white">
                <h3 class="text-3xl font-bold mb-4">Ready to Hire Exceptional Talent?</h3>
                <p class="text-xl mb-6 text-blue-100">
                    Connect with our certified graduates who are ready to make an immediate impact in your organization.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:recruitment@whobaogo.org" class="bg-white text-primary px-8 py-4 rounded-xl font-bold hover:bg-gray-100 transition-all transform hover:scale-105">
                        <i class="fas fa-envelope mr-2"></i>Contact for Recruitment
                    </a>
                    <a href="register.php" class="border-2 border-white text-white px-8 py-4 rounded-xl font-bold hover:bg-white hover:text-primary transition-all transform hover:scale-105">
                        <i class="fas fa-graduation-cap mr-2"></i>Join Our Next Cohort
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Why Choose Our Graduates Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose Our Graduates?</h2>
            <p class="text-xl text-gray-600">Our graduates are more than just skilled - they're transformation-ready professionals</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-primary to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-certificate text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Certified Excellence</h4>
                <p class="text-gray-600">Rigorous assessment and certification process ensures quality</p>
            </div>
            
            <div class="text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-secondary to-green-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-hands-helping text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Real-World Ready</h4>
                <p class="text-gray-600">Practical training with hands-on projects and industry experience</p>
            </div>
            
            <div class="text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-coral to-red-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-rocket text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Fast Integration</h4>
                <p class="text-gray-600">Graduates adapt quickly and contribute from day one</p>
            </div>
            
            <div class="text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-heart text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Social Impact</h4>
                <p class="text-gray-600">Passionate about making a difference in their communities</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>