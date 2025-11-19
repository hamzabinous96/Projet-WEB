<?php
if(isset($_GET['id'])) {
    $course_id = $_GET['id'];
    $courseController = new CourseController($db);
    $course_data = $courseController->getCourse($course_id);
    
    if(!$course_data) {
        echo "Course not found!";
        exit;
    }
} else {
    echo "No course ID specified!";
    exit;
}
?>

<div class="content-header">
    <h1>Edit Course</h1>
    <p>Update course information</p>
</div>

<div class="form-container">
    <form method="POST">
        <input type="hidden" name="edit_course" value="1">
        <input type="hidden" name="id" value="<?php echo $course_id; ?>">
        
        <div class="form-group">
            <label for="name">Course Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($course_data->name); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($course_data->description); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="math" <?php echo $course_data->category == 'math' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="html" <?php echo $course_data->category == 'html' ? 'selected' : ''; ?>>HTML & CSS</option>
                <option value="societe_paix_inclusion" <?php echo $course_data->category == 'societe_paix_inclusion' ? 'selected' : ''; ?>>Société Paix et Inclusion</option>
            </select>
        </div>
        
        <button type="submit" class="submit-btn">Update Course</button>
        <a href="backoffice.php?page=courses" class="btn" style="background: #95a5a6; margin-left: 10px;">Cancel</a>
    </form>
</div>