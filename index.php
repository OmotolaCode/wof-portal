<?php include 'includes/header.php'; ?>
<?php
// Get featured testimonials
$db = new Database();
$db->query('SELECT t.*, u.first_name, u.last_name, c.name as cohort_name 
           FROM testimonials t 
           JOIN users u ON t.user_id = u.id 
           JOIN cohorts c ON t.cohort_id = c.id 
           WHERE t.is_active = 1 AND t.is_featured = 1 
           ORDER BY t.created_at DESC 
           LIMIT 3');
$featured_testimonials = $db->resultSet();

// Get active cohorts for enrollment
$db->query('SELECT * FROM cohorts WHERE status IN ("upcoming", "active") ORDER BY start_date ASC LIMIT 3');
$active_cohorts = $db->resultSet();
?>

<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-primary via-secondary to-emerald-600 text-white overflow-hidden min-h-screen">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="images/student-reading-wof.jpg" alt="WOF Students Learning" 
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-secondary/70 to-emerald-600/80"></div>
    </div>
    
    <!-- Decorative Elements -->
    <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-20 right-20 w-24 h-24 bg-coral opacity-10 rounded-full"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-accent opacity-5 rounded-full"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex items-center justify-center min-h-screen">
        <div class="relative z-10 py-24">
            <div class="flex justify-center mb-8">
                <img src="images/wof_logo.png" alt="Whoba Ogo Foundation" class="h-24 w-auto">
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                <span class="text-white">Whoba Ogo</span>
                <span class="text-coral block">Foundation</span>
            </h1>
            <p class="text-lg sm:text-2xl mb-4 font-medium text-blue-100">...touching lives</p>
            <p class="text-base sm:text-xl mb-8 max-w-4xl mx-auto leading-relaxed text-blue-50">
                Join our transformative cohorts and get <b>FREE</b> Lifetime Access
            </p>

            <?php if(!$auth->isLoggedIn()): ?>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="register.php" class="bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg text-center w-full sm:w-auto">
                        Join Current Cohort
                    </a>
                    <a href="showcase.php" class="border-2 border-white text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-primary transition-all transform hover:scale-105 text-center w-full sm:w-auto">
                        Find Job-Ready Graduates
                    </a>
                </div>
            <?php else: ?>
                <a href="dashboard.php" class="bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg text-center w-full sm:w-auto block mx-auto">
                    Access Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Active Cohorts Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Join Our Active Cohorts</h2>
            <p class="text-xl text-gray-600 mb-2">Transform your life through our comprehensive training programs</p>
            <p class="text-lg text-primary font-semibold">Applications are now open - Limited spaces available!</p>
        </div>
        
        <?php if(!empty($active_cohorts)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <?php foreach($active_cohorts as $cohort): ?>
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg p-6 border-2 border-transparent hover:border-primary transition-all transform hover:-translate-y-2">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-secondary text-white rounded-full text-sm font-medium">
                                <?php echo ucfirst($cohort['status']); ?>
                            </span>
                            <span class="text-sm text-gray-500"><?php echo $cohort['duration_months']; ?> months</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($cohort['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($cohort['description']); ?></p>
                        <div class="flex items-center text-sm text-gray-500 mb-6">
                            <i class="fas fa-calendar mr-2 text-primary"></i>
                            <span>Starts: <?php echo date('M j, Y', strtotime($cohort['start_date'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-users mr-1"></i>
                                Max <?php echo $cohort['max_students']; ?> students
                            </span>
                            <?php if($auth->isLoggedIn()): ?>
                                <a href="apply.php?cohort_id=<?php echo $cohort['id']; ?>" 
                                   class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                                    Apply Now
                                </a>
                            <?php else: ?>
                                <a href="register.php" 
                                   class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                                    Join to Apply
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center">
            <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-4">Ready to Transform Your Future?</h3>
                <p class="text-lg mb-6">Join thousands of graduates who have found meaningful careers through our programs</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if(!$auth->isLoggedIn()): ?>
                        <a href="register.php" class="bg-white text-primary px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition">
                            Register & Apply Today
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="bg-white text-primary px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition">
                            View Available Cohorts
                        </a>
                    <?php endif; ?>
                    <a href="showcase.php" class="border-2 border-white text-white px-8 py-3 rounded-full font-bold hover:bg-white hover:text-primary transition">
                        Browse Job-Ready Graduates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Testimonials -->
<?php if(!empty($featured_testimonials)): ?>
<div class="bg-gradient-to-r from-gray-50 to-blue-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Success Stories</h2>
            <p class="text-xl text-gray-600">Hear from our graduates who transformed their lives</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach($featured_testimonials as $testimonial): ?>
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($testimonial['first_name'], 0, 1) . substr($testimonial['last_name'], 0, 1)); ?>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?>
                            </h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($testimonial['cohort_name']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex mb-4">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="text-gray-700 italic">
                        "<?php echo htmlspecialchars(substr($testimonial['content'], 0, 150)) . (strlen($testimonial['content']) > 150 ? '...' : ''); ?>"
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="showcase.php" class="bg-primary text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition">
                View All Success Stories
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Mission Statement -->
<!-- <div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">Our Mission</h2>
            <div class="max-w-4xl mx-auto">
                <p class="text-xl text-gray-700 leading-relaxed mb-6">
                    We are propelled by a passion to ease the burdens of the unfortunate many, especially in rural 
                    communities of third world nations, who are being weighed down by the burdens of poverty.
                </p>
                <p class="text-lg text-gray-600">
                    We do this by narrowing our gaze to what we believe forms the core of the issue - 
                    <span class="font-semibold text-primary">access to quality healthcare and education</span>.
                </p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-coral rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-heart text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Medical Support</h3>
                        <p class="text-gray-600">Providing essential healthcare services and medical resources to underserved rural communities.</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Educational Empowerment</h3>
                        <p class="text-gray-600">Creating pathways to quality education and skill development for sustainable community growth.</p>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <div class="w-full h-80 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center">
                    <div class="text-center text-white">
                        <i class="fas fa-globe-africa text-6xl mb-4 opacity-80"></i>
                        <p class="text-xl font-semibold">Transforming Africa</p>
                        <p class="text-lg opacity-90">One Community at a Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Impact Programs</h2>
        <p class="text-xl text-gray-600">Comprehensive programs designed to create lasting change in rural communities</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:-translate-y-2 border-t-4 border-coral">
            <div class="w-16 h-16 bg-coral bg-opacity-10 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-stethoscope text-coral text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4 text-gray-900">Healthcare Initiative</h3>
            <p class="text-gray-600 mb-6">Mobile clinics and medical outreach programs bringing essential healthcare to remote areas</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-coral mr-2"></i>Mobile medical units</li>
                <li><i class="fas fa-check text-coral mr-2"></i>Preventive care programs</li>
                <li><i class="fas fa-check text-coral mr-2"></i>Health education workshops</li>
            </ul>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:-translate-y-2 border-t-4 border-secondary relative">
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                <span class="bg-secondary text-white px-4 py-2 rounded-full text-sm font-bold">Featured Program</span>
            </div>
            <div class="w-16 h-16 bg-secondary bg-opacity-10 rounded-full flex items-center justify-center mb-6 mt-4">
                <i class="fas fa-book-open text-secondary text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4 text-gray-900">Education & Training</h3>
            <p class="text-gray-600 mb-6">Comprehensive educational programs and vocational training for sustainable development</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-secondary mr-2"></i>Literacy programs</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Vocational training</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Scholarship programs</li>
            </ul>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:-translate-y-2 border-t-4 border-primary">
            <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-hands-helping text-primary text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4 text-gray-900">Community Development</h3>
            <p class="text-gray-600 mb-6">Holistic community empowerment through infrastructure and capacity building</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-primary mr-2"></i>Infrastructure development</li>
                <li><i class="fas fa-check text-primary mr-2"></i>Leadership training</li>
                <li><i class="fas fa-check text-primary mr-2"></i>Sustainable projects</li>
            </ul>
        </div>
    </div>
</div> -->

<!-- Impact Statistics -->
<!-- <div class="bg-gradient-to-r from-gray-50 to-blue-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Impact</h2>
            <p class="text-xl text-gray-600">Measurable change across African communities</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-coral rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <div class="text-4xl font-bold text-coral mb-2">50K+</div>
                <p class="text-gray-600 font-medium">Lives Touched</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                </div>
                <div class="text-4xl font-bold text-secondary mb-2">200+</div>
                <p class="text-gray-600 font-medium">Communities Served</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
                <div class="text-4xl font-bold text-primary mb-2">15K+</div>
                <p class="text-gray-600 font-medium">Students Educated</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heartbeat text-white text-2xl"></i>
                </div>
                <div class="text-4xl font-bold text-emerald-600 mb-2">25K+</div>
                <p class="text-gray-600 font-medium">Medical Consultations</p>
            </div>
        </div>
    </div>
</div> -->

<!-- <div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Partner With Us?</h2>
            <p class="text-xl text-gray-600">We're committed to sustainable community transformation</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-coral to-red-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-globe-africa text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Local Expertise</h4>
                <p class="text-gray-600">Deep understanding of African communities and their unique challenges</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-secondary to-green-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-handshake text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Community Partnership</h4>
                <p class="text-gray-600">Working hand-in-hand with local leaders and community members</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-primary to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-chart-line text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Measurable Impact</h4>
                <p class="text-gray-600">Data-driven approach with transparent reporting and accountability</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-seedling text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Sustainable Solutions</h4>
                <p class="text-gray-600">Long-term programs that create lasting positive change</p>
            </div>
        </div>
    </div>
</div> -->

<!-- Call to Action -->
<div class="bg-gradient-to-r from-primary via-secondary to-emerald-600 py-16">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-white mb-6">Start Your Journey Today</h2>
        <p class="text-xl text-blue-100 mb-8">
            Join our next cohort and become part of a community that's transforming lives across Africa. 
            Our graduates are job-ready and making real impact in their communities.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <?php if(!$auth->isLoggedIn()): ?>
                <a href="register.php" class="bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                    Apply for Next Cohort
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                    View Available Cohorts
                </a>
            <?php endif; ?>
            <a href="showcase.php" class="border-2 border-white text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-primary transition-all transform hover:scale-105">
                Hire Our Graduates
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>