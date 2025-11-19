<?php
$courseController = new CourseController($db);
$courses = $courseController->getAllCourses();
?>

<section class="courses">
    <div class="container">
        <h2>Available Courses</h2>
        <p class="subtitle">Choose a course to view available coaches and their materials</p>
        
        <div class="courses-grid">
            <?php while($course = $courses->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="course-card" data-course-id="<?php echo $course['id']; ?>">
                    <div class="course-icon">
                        <?php 
                        $icons = [
                            'math' => '🧮',
                            'html' => '💻', 
                            'societe_paix_inclusion' => '🌍'
                        ];
                        echo $icons[$course['category']] ?? '📚';
                        ?>
                    </div>
                    <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="course-meta">
                        <span class="course-category"><?php echo ucfirst(str_replace('_', ' ', $course['category'])); ?></span>
                        <span class="coach-count">
                            <?php 
                            $coaches = $courseController->getCourseCoaches($course['id']);
                            $coachCount = $coaches->rowCount();
                            echo $coachCount . ' coach' . ($coachCount !== 1 ? 'es' : '') . ' available';
                            ?>
                        </span>
                    </div>
                    <div class="course-actions">
                        <a href="index.php?page=course_coaches&course_id=<?php echo $course['id']; ?>" class="course-button view-coaches">
                            View Coaches
                        </a>
                        <a href="index.php?page=materials&course_id=<?php echo $course['id']; ?>" class="course-button view-materials">
                            Course Materials
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>