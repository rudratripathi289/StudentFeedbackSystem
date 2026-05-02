// Student Feedback System - Main JavaScript File

// Utility functions
const utils = {
    // Show alert messages
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} fade-in`;
        alertDiv.innerHTML = message;
        
        // Insert at the top of the body
        document.body.insertBefore(alertDiv, document.body.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    },

    // Validate email format
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Generate random ID
    generateId: function() {
        return Math.random().toString(36).substr(2, 9);
    },

    // Local storage utilities
    storage: {
        set: function(key, value) {
            localStorage.setItem(key, JSON.stringify(value));
        },
        get: function(key) {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        },
        remove: function(key) {
            localStorage.removeItem(key);
        }
    }
};

// Form validation functions
const formValidation = {
    // Validate required fields
    validateRequired: function(fields) {
        let isValid = true;
        fields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });
        return isValid;
    },

    // Validate rating (1-5)
    validateRating: function(rating) {
        return rating >= 1 && rating <= 5;
    },

    // Validate comment length
    validateComment: function(comment, minLength = 10) {
        return comment.trim().length >= minLength;
    }
};

// Navigation functions
const navigation = {
    // Navigate to page
    navigateTo: function(page) {
        window.location.href = page;
    },

    // Go back
    goBack: function() {
        window.history.back();
    },

    // Check if user is logged in
    isLoggedIn: function() {
        return utils.storage.get('user') !== null;
    },

    // Get current user
    getCurrentUser: function() {
        return utils.storage.get('user');
    },

    // Logout user
    logout: function() {
        utils.storage.remove('user');
        utils.storage.remove('userType');
        window.location.href = '../index.html';
    }
};

// Search and filter functions
const searchFilter = {
    // Filter table rows
    filterTable: function(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = text.includes(searchTerm.toLowerCase());
            row.style.display = match ? '' : 'none';
        });
    },

    // Sort table by column
    sortTable: function(tableId, columnIndex, type = 'string') {
        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            let aVal = a.cells[columnIndex].textContent.trim();
            let bVal = b.cells[columnIndex].textContent.trim();

            if (type === 'number') {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else if (type === 'date') {
                aVal = new Date(aVal);
                bVal = new Date(bVal);
            }

            if (aVal < bVal) return -1;
            if (aVal > bVal) return 1;
            return 0;
        });

        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }
};

// Chart placeholder functions (for Chart.js integration)
const charts = {
    // Create rating distribution chart
    createRatingChart: function(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        // Placeholder for Chart.js implementation
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#3b82f6';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Rating Distribution Chart', canvas.width / 2, canvas.height / 2);
        ctx.fillText('(Chart.js integration required)', canvas.width / 2, canvas.height / 2 + 25);
    },

    // Create feedback trend chart
    createTrendChart: function(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        // Placeholder for Chart.js implementation
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#10b981';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Feedback Trend Chart', canvas.width / 2, canvas.height / 2);
        ctx.fillText('(Chart.js integration required)', canvas.width / 2, canvas.height / 2 + 25);
    }
};

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to main content
    const mainContent = document.querySelector('main') || document.querySelector('.main-content');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }

    // Add hover effects to buttons
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(button => {
        button.classList.add('btn-hover');
    });

    // Add card hover effects
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.add('card');
    });

    // Initialize search functionality if search input exists
    const searchInput = document.querySelector('#searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const tableId = this.dataset.table || 'dataTable';
            searchFilter.filterTable(tableId, this.value);
        });
    }

    // Initialize form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            if (!formValidation.validateRequired(requiredFields)) {
                e.preventDefault();
                utils.showAlert('Please fill in all required fields.', 'error');
            }
        });
    });
});

// Export functions for use in other scripts
window.FeedbackSystem = {
    utils,
    formValidation,
    navigation,
    searchFilter,
    charts
};
