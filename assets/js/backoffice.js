// Back Office JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initBackOffice();
});

function initBackOffice() {
    // Initialize charts and statistics
    initCharts();
    
    // Table sorting and filtering
    initTableFeatures();
    
    // Form enhancements
    initBackofficeForms();
    
    // Real-time updates
    initRealTimeUpdates();
    
    // Dashboard widgets
    initDashboardWidgets();
}

// Chart initialization
function initCharts() {
    // Simple bar chart for statistics
    const statsContainers = document.querySelectorAll('.chart-placeholder');
    
    statsContainers.forEach(container => {
        if (container.querySelector('.popularity-list')) {
            animatePopularityBars();
        }
        
        if (container.querySelector('.chart-legend')) {
            createUserDistributionChart(container);
        }
    });
}

function animatePopularityBars() {
    const bars = document.querySelectorAll('.popularity-bar');
    bars.forEach(bar => {
        const originalWidth = bar.style.width;
        bar.style.width = '0%';
        bar.style.transition = 'width 1s ease-in-out';
        
        setTimeout(() => {
            bar.style.width = originalWidth;
        }, 500);
    });
}

function createUserDistributionChart(container) {
    const legend = container.querySelector('.chart-legend');
    const coachCount = parseInt(legend.querySelector('.coach').parentNode.textContent.match(/\d+/)[0]);
    const participantCount = parseInt(legend.querySelector('.participant').parentNode.textContent.match(/\d+/)[0]);
    const total = coachCount + participantCount;
    
    // Create simple pie chart visualization
    const pieChart = document.createElement('div');
    pieChart.className = 'pie-chart';
    pieChart.style.cssText = `
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: conic-gradient(
            #3498db 0% ${(coachCount / total) * 100}%,
            #27ae60 ${(coachCount / total) * 100}% 100%
        );
        margin: 0 auto;
    `;
    
    container.appendChild(pieChart);
}

// Table features
function initTableFeatures() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        let currentSortColumn = -1;
        let isAscending = true;
        
        headers.forEach((header, index) => {
            if (index < headers.length - 1) { // Don't add sorting to actions column
                header.style.cursor = 'pointer';
                header.style.position = 'relative';
                header.addEventListener('click', () => {
                    sortTable(table, index, currentSortColumn === index ? !isAscending : true);
                    currentSortColumn = index;
                    isAscending = currentSortColumn === index ? !isAscending : true;
                    
                    // Update sort indicators
                    updateSortIndicators(headers, index, isAscending);
                });
            }
        });
        
        // Add search functionality
        addTableSearch(table);
    });
}

function updateSortIndicators(headers, sortedIndex, isAscending) {
    headers.forEach((header, index) => {
        header.innerHTML = header.textContent;
        if (index === sortedIndex) {
            const indicator = document.createElement('span');
            indicator.textContent = isAscending ? ' ↑' : ' ↓';
            indicator.style.marginLeft = '5px';
            indicator.style.fontWeight = 'bold';
            header.appendChild(indicator);
        }
    });
}

function sortTable(table, columnIndex, ascending = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isNumeric = !isNaN(parseFloat(rows[0].cells[columnIndex].textContent));
    
    const sortedRows = rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();
        
        if (isNumeric) {
            aValue = parseFloat(aValue) || 0;
            bValue = parseFloat(bValue) || 0;
            return ascending ? aValue - bValue : bValue - aValue;
        } else {
            return ascending ? 
                aValue.localeCompare(bValue) : 
                bValue.localeCompare(aValue);
        }
    });
    
    // Clear existing rows
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Add sorted rows with animation
    sortedRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        tbody.appendChild(row);
        
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
}

function addTableSearch(table) {
    const container = table.closest('.table-container');
    const header = container.previousElementSibling;
    
    // Create search box
    const searchBox = document.createElement('div');
    searchBox.className = 'table-search';
    searchBox.style.cssText = `
        margin-bottom: 1rem;
        display: flex;
        gap: 10px;
        align-items: center;
    `;
    
    searchBox.innerHTML = `
        <input type="text" placeholder="Search..." style="
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
            font-size: 0.9rem;
        ">
        <button style="
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        ">Search</button>
        <button class="clear-search" style="
            padding: 10px 15px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        ">Clear</button>
    `;
    
    if (header.classList.contains('content-header')) {
        header.appendChild(searchBox);
    } else {
        container.parentNode.insertBefore(searchBox, container);
    }
    
    const searchInput = searchBox.querySelector('input');
    const searchButton = searchBox.querySelector('button');
    const clearButton = searchBox.querySelector('.clear-search');
    
    searchButton.addEventListener('click', () => filterTable(table, searchInput.value));
    searchInput.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') {
            filterTable(table, searchInput.value);
        }
    });
    clearButton.addEventListener('click', () => {
        searchInput.value = '';
        filterTable(table, '');
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Backoffice form enhancements
function initBackofficeForms() {
    // Auto-save functionality for forms
    const forms = document.querySelectorAll('.form-container form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        const formId = form.getAttribute('id') || 'form_' + Math.random().toString(36).substr(2, 9);
        
        inputs.forEach(input => {
            input.addEventListener('input', debounce(() => {
                saveFormDraft(formId, form);
            }, 1000));
        });
        
        // Load draft on page load
        loadFormDraft(formId, form);
        
        // Add character counters for textareas
        const textareas = form.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            addCharacterCounter(textarea);
        });
    });
}

function addCharacterCounter(textarea) {
    const counter = document.createElement('div');
    counter.className = 'char-counter';
    counter.style.cssText = `
        font-size: 0.8rem;
        color: #7f8c8d;
        text-align: right;
        margin-top: 5px;
    `;
    
    textarea.parentNode.appendChild(counter);
    
    function updateCounter() {
        const maxLength = textarea.getAttribute('maxlength') || 1000;
        const currentLength = textarea.value.length;
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength * 0.9) {
            counter.style.color = '#e74c3c';
        } else if (currentLength > maxLength * 0.75) {
            counter.style.color = '#f39c12';
        } else {
            counter.style.color = '#7f8c8d';
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

function saveFormDraft(formId, form) {
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    localStorage.setItem(`draft_${formId}`, JSON.stringify(data));
    showNotification('Draft saved automatically', 'info');
}

function loadFormDraft(formId, form) {
    const draft = localStorage.getItem(`draft_${formId}`);
    if (draft) {
        const data = JSON.parse(draft);
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
        
        // Show restore option
        const restoreBtn = document.createElement('button');
        restoreBtn.textContent = 'Restore Saved Draft';
        restoreBtn.type = 'button';
        restoreBtn.style.cssText = `
            background: #f39c12;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            font-size: 0.9rem;
        `;
        
        restoreBtn.addEventListener('click', () => {
            showNotification('Draft restored successfully', 'success');
        });
        
        const submitBtn = form.querySelector('.submit-btn');
        if (submitBtn) {
            submitBtn.parentNode.insertBefore(restoreBtn, submitBtn.nextSibling);
        }
    }
}

// Real-time updates
function initRealTimeUpdates() {
    // Simulate real-time user count updates
    //setInterval(updateUserCounts, 30000);
    
    // Check for new messages
    //setInterval(checkNewMessages, 60000);
    
    // Update activity feed
    setInterval(updateActivityFeed, 45000);
}

function updateUserCounts() {
    const userCountElements = document.querySelectorAll('.stat-number');
    
    userCountElements.forEach(element => {
        const currentCount = parseInt(element.textContent);
        const randomChange = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
        const newCount = Math.max(0, currentCount + randomChange);
        
        if (newCount !== currentCount) {
            // Animate count change
            animateCountChange(element, currentCount, newCount);
        }
    });
    
}

function animateCountChange(element, oldValue, newValue) {
    const duration = 1000;
    const startTime = Date.now();
    
    function update() {
        const currentTime = Date.now();
        const progress = Math.min((currentTime - startTime) / duration, 1);
        
        const currentValue = Math.floor(oldValue + (newValue - oldValue) * progress);
        element.textContent = currentValue;
        
        // Color animation
        if (newValue > oldValue) {
            element.style.color = '#27ae60';
        } else if (newValue < oldValue) {
            element.style.color = '#e74c3c';
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            setTimeout(() => {
                element.style.color = '';
            }, 2000);
        }
    }
    
    update();
}

function checkNewMessages() {
    // Simulate checking for new messages
    if (Math.random() > 0.7) { // 30% chance of new message
        showNotification('New user registration received', 'info');
    }
}

function updateActivityFeed() {
    const activities = [
        'New course material uploaded',
        'User completed a course',
        'New coach registration',
        'Course rating submitted',
        'Support ticket created'
    ];
    
    const randomActivity = activities[Math.floor(Math.random() * activities.length)];
    const randomUser = ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Wilson'][Math.floor(Math.random() * 4)];
    
    // Add to activity table if it exists
    const activityTable = document.querySelector('.data-table');
    if (activityTable) {
        const tbody = activityTable.querySelector('tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>${randomActivity}</td>
            <td>${randomUser}</td>
            <td>Just now</td>
        `;
        
        newRow.style.background = '#f8f9fa';
        tbody.insertBefore(newRow, tbody.firstChild);
        
        // Remove old rows if too many
        const rows = tbody.querySelectorAll('tr');
        if (rows.length > 10) {
            tbody.removeChild(rows[rows.length - 1]);
        }
        
        // Animate new row
        setTimeout(() => {
            newRow.style.background = '';
        }, 2000);
    }
}

// Dashboard widgets
function initDashboardWidgets() {
    // Add quick action buttons
    addQuickActions();
    
    // Initialize stats refresh
    initStatsRefresh();
}

function addQuickActions() {
    const dashboard = document.querySelector('.content-header');
    if (dashboard && dashboard.querySelector('h1').textContent.includes('Dashboard')) {
        const quickActions = document.createElement('div');
        quickActions.className = 'quick-actions';
        quickActions.style.cssText = `
            display: flex;
            gap: 10px;
            margin-top: 1rem;
            flex-wrap: wrap;
        `;
        
        quickActions.innerHTML = `
            <button class="quick-btn" data-action="refresh">🔄 Refresh Stats</button>
            <button class="quick-btn" data-action="export">📊 Export Data</button>
            <button class="quick-btn" data-action="settings">⚙️ Settings</button>
            <button class="quick-btn" data-action="help">❓ Help</button>
        `;
        
        dashboard.appendChild(quickActions);
        
        // Add event listeners
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                handleQuickAction(action);
            });
        });
    }
}

function handleQuickAction(action) {
    switch(action) {
        case 'refresh':
            location.reload();
            break;
        case 'export':
            showNotification('Exporting data... This may take a moment.', 'info');
            setTimeout(() => {
                showNotification('Data exported successfully!', 'success');
            }, 2000);
            break;
        case 'settings':
            showNotification('Opening settings...', 'info');
            break;
        case 'help':
            showNotification('Opening help documentation...', 'info');
            break;
    }
}

function initStatsRefresh() {
    const refreshBtn = document.querySelector('[data-action="refresh"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('loading');
            this.disabled = true;
            
            setTimeout(() => {
                this.classList.remove('loading');
                this.disabled = false;
                showNotification('Statistics updated successfully!', 'success');
            }, 1500);
        });
    }
}

// Notification system for backoffice
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '5px',
        color: 'white',
        zIndex: '1000',
        maxWidth: '300px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        fontWeight: '500',
        fontSize: '0.9rem'
    });
    
    switch(type) {
        case 'success':
            notification.style.background = '#27ae60';
            break;
        case 'error':
            notification.style.background = '#e74c3c';
            break;
        case 'warning':
            notification.style.background = '#f39c12';
            break;
        default:
            notification.style.background = '#3498db';
    }
    
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }, 5000);
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export backoffice functions
window.BackOffice = {
    sortTable,
    filterTable,
    saveFormDraft,
    loadFormDraft,
    updateUserCounts,
    showNotification
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBackOffice);
} else {
    initBackOffice();
}