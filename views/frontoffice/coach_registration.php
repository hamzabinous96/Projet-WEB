<?php
$courseController = new CourseController($db);
$courses = $courseController->getAllCourses();
?>

<section class="coach-registration">
    <div class="container">
        <h2>Register as Coach</h2>
        <p class="subtitle">Create your coach profile and select the subjects you want to teach</p>
        
        <?php if(isset($_GET['error']) && $_GET['error'] == 'email_exists'): ?>
            <div class="alert error">
                This email is already registered. Please use a different email.
            </div>
        <?php endif; ?>
        
        <form method="POST" class="coach-form" id="coachRegistrationForm">
            <input type="hidden" name="register_coach" value="1">
            
            <div class="form-section">
                <h3>Personal Information</h3>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="your.email@weconnect.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567">
                </div>
            </div>
            
            <div class="form-section">
                <h3>Coach Profile</h3>
                
                <div class="form-group">
                    <label for="bio">Professional Bio *</label>
                    <textarea id="bio" name="bio" rows="5" required placeholder="Tell us about your experience, expertise, and teaching philosophy..."></textarea>
                    <div class="char-counter">0/500 characters</div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Select Subjects to Teach</h3>
                <p class="section-description">Choose one or more subjects that you're qualified to teach:</p>
                
                <div class="course-selection">
                    <?php while($course = $courses->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="course-checkbox">
                            <input type="checkbox" id="course_<?php echo $course['id']; ?>" name="courses[]" value="<?php echo $course['id']; ?>">
                            <label for="course_<?php echo $course['id']; ?>">
                                <span class="course-name"><?php echo htmlspecialchars($course['name']); ?></span>
                                <span class="course-description"><?php echo htmlspecialchars($course['description']); ?></span>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Complete Coach Registration</button>
                <a href="index.php?page=role_selection" class="cancel-button">Cancel</a>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for bio
    const bioTextarea = document.getElementById('bio');
    const charCounter = document.querySelector('.char-counter');
    
    bioTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCounter.textContent = `${length}/500 characters`;
        
        if (length > 450) {
            charCounter.style.color = '#e74c3c';
        } else if (length > 400) {
            charCounter.style.color = '#f39c12';
        } else {
            charCounter.style.color = '#7f8c8d';
        }
    });
    
    // Form validation for course selection
    const form = document.getElementById('coachRegistrationForm');
    form.addEventListener('submit', function(e) {
        const selectedCourses = document.querySelectorAll('input[name="courses[]"]:checked');
        if (selectedCourses.length === 0) {
            e.preventDefault();
            alert('Please select at least one subject to teach.');
        }
    });
});
</script>