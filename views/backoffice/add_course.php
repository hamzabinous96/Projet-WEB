<div class="content-header">
    <h1>Add New Course</h1>
    <p>Create a new course for the platform</p>
</div>

<div class="form-container">
    <form method="POST">
        <input type="hidden" name="add_course" value="1">
        
        <div class="form-group">
            <label for="name">Course Name *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="math">Mathematics</option>
                <option value="html">HTML & CSS</option>
                <option value="societe_paix_inclusion">Société Paix et Inclusion</option>
            </select>
        </div>
        
        <button type="submit" class="submit-btn">Add Course</button>
        <a href="backoffice.php?page=courses" class="btn" style="background: #95a5a6; margin-left: 10px;">Cancel</a>
    </form>
</div>