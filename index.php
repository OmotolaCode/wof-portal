<?php include 'includes/header.php'; ?>

<div class="bg-gradient-to-r from-primary to-secondary text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-6">Welcome to WOF Training Institute</h1>
        <p class="text-xl mb-8 max-w-3xl mx-auto">Transform your career with our comprehensive training programs. Join thousands of successful alumni who have advanced their careers through our expert-led courses.</p>
        <?php if(!$auth->isLoggedIn()): ?>
            <div class="space-x-4">
                <a href="register.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Get Started</a>
                <a href="showcase.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition">View Alumni</a>
            </div>
        <?php else: ?>
            <a href="dashboard.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Training Programs</h2>
        <p class="text-xl text-gray-600">Choose from our industry-leading programs designed to accelerate your career</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                <i class="fas fa-laptop-code text-primary text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4">3-Month Intensive</h3>
            <p class="text-gray-600 mb-6">Fast-track programs for quick skill acquisition and career transition</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-secondary mr-2"></i>Industry-relevant curriculum</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Hands-on projects</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Career support</li>
            </ul>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition border-2 border-secondary">
            <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mb-6">
                <i class="fas fa-chart-line text-secondary text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4">4-Month Professional</h3>
            <p class="text-gray-600 mb-6">Comprehensive training with deeper specialization and mentoring</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-secondary mr-2"></i>Advanced curriculum</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>1-on-1 mentoring</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Job placement assistance</li>
            </ul>
            <div class="bg-secondary text-white text-center py-2 rounded-lg font-semibold">Most Popular</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-yellow-100 rounded-lg flex items-center justify-center mb-6">
                <i class="fas fa-trophy text-accent text-2xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-4">6-Month Mastery</h3>
            <p class="text-gray-600 mb-6">Complete transformation with leadership skills and advanced expertise</p>
            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                <li><i class="fas fa-check text-secondary mr-2"></i>Expert-level training</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Leadership development</li>
                <li><i class="fas fa-check text-secondary mr-2"></i>Alumni network access</li>
            </ul>
        </div>
    </div>
</div>

<div class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose WOF?</h2>
            <p class="text-xl text-gray-600">We're committed to your success</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2">Expert Instructors</h4>
                <p class="text-gray-600">Learn from industry professionals with years of experience</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2">Certified Programs</h4>
                <p class="text-gray-600">Receive industry-recognized certifications upon completion</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-accent rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2">Career Support</h4>
                <p class="text-gray-600">Job placement assistance and career guidance</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-network-wired text-white text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2">Alumni Network</h4>
                <p class="text-gray-600">Connect with successful graduates in your field</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>