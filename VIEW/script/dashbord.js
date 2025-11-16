// Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });

        // Active menu item
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Chart buttons
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Action buttons in reclamations table
        document.querySelectorAll('.reclamation-item .action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.querySelector('i').className;
                if (action.includes('ri-eye-line')) {
                    alert('Voir les détails de la réclamation');
                } else if (action.includes('ri-edit-line')) {
                    alert('Modifier la réclamation');
                }
            });
        });