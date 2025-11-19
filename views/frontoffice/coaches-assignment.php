<section class="teacher-assignment">
    <div class="container">
        <h2>Teacher Registration</h2>
        <p class="subtitle">Are you already an assigned teacher or new to our platform?</p>
        
        <div class="assignment-options">
            <!-- Already Assigned Teacher -->
            <div class="assignment-card">
                <div class="assignment-icon">
                    <div class="icon-circle">👨‍🏫</div>
                </div>
                <div class="assignment-content">
                    <h3>I'm Already a Teacher</h3>
                    <p class="assignment-description">I have my teacher ID and I'm already assigned to courses</p>
                    
                    <form method="POST" class="teacher-login-form">
                        <input type="hidden" name="existing_teacher" value="1">
                        
                        <div class="form-group">
                            <label for="teacher_code">Teacher ID *</label>
                            <input type="text" id="teacher_code" name="teacher_code" required placeholder="Enter your teacher ID (e.g., TCH001)">
                        </div>
                        
                        <div class="form-group">
                            <label for="teacher_name">Full Name *</label>
                            <input type="text" id="teacher_name" name="teacher_name" required placeholder="Enter your full name as registered">
                        </div>
                        
                        <button type="submit" class="assignment-button">
                            Access My Teacher Space
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- New Teacher -->
            <div class="assignment-card">
                <div class="assignment-icon">
                    <div class="icon-circle">🌟</div>
                </div>
                <div class="assignment-content">
                    <h3>New Teacher</h3>
                    <p class="assignment-description">I want to join as a new teacher and create my profile</p>
                    
                    <div class="new-teacher-features">
                        <div class="feature-item">
                            <span class="feature-icon">📝</span>
                            <span class="feature-text">Create your teacher profile</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">🎯</span>
                            <span class="feature-text">Select subjects to teach</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">📚</span>
                            <span class="feature-text">Upload course materials</span>
                        </div>
                    </div>
                    
                    <a href="index.php?page=coach_registration" class="assignment-button new-teacher-btn">
                        Register as New Teacher
                    </a>
                </div>
            </div>
        </div>
        
        <div class="assignment-footer">
            <p>Not sure? <a href="index.php?page=our_coaches" class="footer-link">View our current teachers first</a></p>
        </div>
    </div>
</section>