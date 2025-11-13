    </div> <!-- Close main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Load saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
            
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('collapsed');
                
                // Save state
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        });
    </script>
</body>
</html>
