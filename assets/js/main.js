// Missing Children Pattern Detector - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form[method="POST"]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('submit', function(event) {
            if (!confirm('Are you sure you want to perform this action?')) {
                event.preventDefault();
            }
        });
    });

    // Auto-generate case number (if needed)
    const caseNumberInput = document.getElementById('case_number');
    if (caseNumberInput && !caseNumberInput.value) {
        const generateCaseNumber = function() {
            const year = new Date().getFullYear();
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return `MC-${year}-${random}`;
        };
        // Uncomment to auto-generate
        // caseNumberInput.value = generateCaseNumber();
    }

    // Real-time form field updates
    const ageInput = document.getElementById('age');
    const dobInput = document.getElementById('date_of_birth');
    
    if (ageInput && dobInput) {
        // Calculate age from date of birth
        dobInput.addEventListener('change', function() {
            if (this.value && !ageInput.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageInput.value = age;
            }
        });

        // Calculate date of birth from age (approximate)
        ageInput.addEventListener('change', function() {
            if (this.value && !dobInput.value) {
                const today = new Date();
                const birthYear = today.getFullYear() - parseInt(this.value);
                dobInput.value = `${birthYear}-01-01`;
            }
        });
    }

    // Location autocomplete helper (can be extended with Google Maps API)
    const cityInputs = document.querySelectorAll('input[name*="location_city"], input[name*="city"]');
    cityInputs.forEach(function(input) {
        // Add autocomplete attribute
        input.setAttribute('autocomplete', 'address-level2');
    });

    // Table row click to view (optional enhancement)
    const tableRows = document.querySelectorAll('.table-hover tbody tr');
    tableRows.forEach(function(row) {
        const viewLink = row.querySelector('a[href*="view"]');
        if (viewLink) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function(e) {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                    window.location.href = viewLink.href;
                }
            });
        }
    });

    // Print functionality
    window.printPage = function() {
        window.print();
    };

    // Export to CSV (basic implementation)
    window.exportToCSV = function(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');

            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '');
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }

            csv.push(row.join(','));
        }

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Loading state for forms
    const submitButtons = document.querySelectorAll('form button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.closest('form').addEventListener('submit', function() {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        });
    });

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Utility functions
const MissingChildrenApp = {
    // Format date
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Format datetime
    formatDateTime: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('main').insertBefore(alertDiv, document.querySelector('main').firstChild);
        
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
};

