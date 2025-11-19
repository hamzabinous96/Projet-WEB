<?php
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'coach') {
    header("Location: index.php?page=role_selection");
    exit;
}
?>

<section class="add-material">
    <div class="container">
        <h2>Add Course Material</h2>
        <p class="subtitle">Share learning resources with your students</p>
        
        <form method="POST" class="material-form">
            <div class="form-group">
                <label for="title">Material Title *</label>
                <input type="text" id="title" name="title" required placeholder="Enter material title">
            </div>
            
            <div class="form-group">
                <label for="material_type">Material Type *</label>
                <select id="material_type" name="material_type" required>
                    <option value="">Select type</option>
                    <option value="pdf">PDF Document</option>
                    <option value="youtube">YouTube Video</option>
                    <option value="video_link">Video Link</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content">Content URL/ID *</label>
                <input type="text" id="content" name="content" required placeholder="Enter URL or YouTube ID">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required placeholder="Describe this learning material..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Add Material</button>
                <a href="index.php?page=coach_profile" class="cancel-button">Cancel</a>
            </div>
        </form>
    </div>
</section>