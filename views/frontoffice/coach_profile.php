<?php
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'coach') {
    header("Location: index.php?page=role_selection");
    exit;
}

$courseController = new CourseController($db);
$materialController = new MaterialController($db);

// Get coach's courses - fix the undefined array key error
$coachCourses = [];
$materialsByCourse = [];

try {
    // Get courses this coach teaches
    $stmt = $db->prepare("SELECT c.* FROM courses c 
                         INNER JOIN coach_courses cc ON c.id = cc.course_id 
                         WHERE cc.coach_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $coachCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get materials for each course
    foreach($coachCourses as $course) {
        $materials = $materialController->getCourseMaterials($course['id']);
        $materialsByCourse[$course['id']] = [
            'course_name' => $course['name'],
            'materials' => $materials
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}
?>

<section class="coach-profile">
    <div class="container">
        <h2>My Coach Dashboard</h2>
        
        <div class="profile-header">
            <div class="profile-info">
                <h3>Welcome, Coach <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👨‍🏫</h3>
                <p>Manage your teaching profile, courses, and materials</p>
            </div>
            <div class="profile-stats">
                <div class="stat">
                    <span class="number"><?php echo count($coachCourses); ?></span>
                    <span class="label">Courses Teaching</span>
                </div>
                <div class="stat">
                    <span class="number">
                        <?php 
                        $totalMaterials = 0;
                        foreach($materialsByCourse as $courseData) {
                            $totalMaterials += $courseData['materials']->rowCount();
                        }
                        echo $totalMaterials;
                        ?>
                    </span>
                    <span class="label">Materials Posted</span>
                </div>
            </div>
        </div>
        
        <div class="profile-actions">
            <div class="action-card">
                <h4>📚 My Courses</h4>
                <p>View and manage the courses you teach</p>
                <a href="index.php?page=courses" class="action-button">Manage Courses</a>
            </div>
            
            <div class="action-card">
                <h4>📁 Add Materials</h4>
                <p>Upload new PDFs, videos, and links for your courses</p>
                <a href="index.php?page=add_material" class="action-button">Add Materials</a>
            </div>
            
            <div class="action-card">
                <h4>💬 Messages</h4>
                <p>Chat with participants and answer questions</p>
                <a href="index.php?page=chat" class="action-button">View Messages</a>
            </div>
        </div>

        <!-- Course-specific materials section -->
        <div class="course-materials-section">
            <h3>My Course Materials</h3>
            
            <?php if(!empty($materialsByCourse)): ?>
                <?php foreach($materialsByCourse as $courseId => $courseData): ?>
                    <div class="course-materials-block">
                        <h4><?php echo htmlspecialchars($courseData['course_name']); ?></h4>
                        
                        <?php if($courseData['materials']->rowCount() > 0): ?>
                            <div class="materials-grid">
                                <?php while($material = $courseData['materials']->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="material-card">
                                        <div class="material-header">
                                            <h5><?php echo htmlspecialchars($material['title']); ?></h5>
                                            <span class="material-type-badge"><?php echo strtoupper($material['material_type']); ?></span>
                                        </div>
                                        <p class="material-description"><?php echo htmlspecialchars($material['description']); ?></p>
                                        <div class="material-meta">
                                            <span class="material-date">Posted: <?php echo date('M j, Y', strtotime($material['created_at'])); ?></span>
                                        </div>
                                        <div class="material-actions">
                                            <?php if($material['material_type'] == 'pdf'): ?>
                                                <a href="<?php echo htmlspecialchars($material['content']); ?>" target="_blank" class="material-btn">📄 View PDF</a>
                                            <?php elseif($material['material_type'] == 'youtube'): ?>
                                                <a href="https://youtube.com/watch?v=<?php echo htmlspecialchars($material['content']); ?>" target="_blank" class="material-btn">🎥 Watch Video</a>
                                            <?php else: ?>
                                                <a href="<?php echo htmlspecialchars($material['content']); ?>" target="_blank" class="material-btn">🔗 Visit Link</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-materials">No materials posted for this course yet.</p>
                        <?php endif; ?>
                        
                        <div class="add-material-prompt">
                            <a href="index.php?page=add_material&course_id=<?php echo $courseId; ?>" class="btn-add-material">
                                + Add Material to <?php echo htmlspecialchars($courseData['course_name']); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-courses-message">
                    <h4>You're not teaching any courses yet</h4>
                    <p>Once you register as a coach and select your subjects, they will appear here.</p>
                    <a href="index.php?page=coach_registration" class="btn-primary">Complete Your Profile</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>