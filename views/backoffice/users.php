<?php
$userController = new UserController($db);
$users = $userController->getAllUsers();
?>

<div class="content-header">
    <h1>Users Management</h1>
    <p>Manage all users in the platform</p>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Joined Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <span class="role-badge <?php echo $row['role']; ?>">
                        <?php echo ucfirst($row['role']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="backoffice.php?page=view_user&id=<?php echo $row['id']; ?>" class="btn btn-edit">View</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>