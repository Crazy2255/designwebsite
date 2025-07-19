// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Initialize popovers
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
});

// Sidebar Toggle
window.addEventListener('DOMContentLoaded', event => {
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Check for stored sidebar state
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }
        
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }
});

// Add active class to current nav item
document.addEventListener('DOMContentLoaded', function() {
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.nav-link');
    const menuLength = menuItems.length;
    
    for (let i = 0; i < menuLength; i++) {
        if (menuItems[i].getAttribute('href') === currentLocation) {
            menuItems[i].classList.add('active');
            
            // If the active item is in a collapse menu, expand it
            const parent = menuItems[i].closest('.collapse');
            if (parent) {
                parent.classList.add('show');
                parent.previousElementSibling.classList.remove('collapsed');
            }
        }
    }
});

// Prevent empty links from scrolling to top
document.addEventListener('click', function(e) {
    const target = e.target.closest('a[href="#"]');
    if (target) {
        e.preventDefault();
    }
});

// Enable data tables
if (typeof $.fn.DataTable !== 'undefined') {
    $('.datatable').DataTable({
        responsive: true,
        pageLength: 10,
        language: {
            search: "",
            searchPlaceholder: "Search..."
        }
    });
}

// Enable date pickers
if (typeof flatpickr !== 'undefined') {
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        allowInput: true
    });
}

// File input preview
document.querySelectorAll('.custom-file-input').forEach(function(input) {
    input.addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
});

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Confirmation dialogs
document.querySelectorAll('[data-confirm]').forEach(function(element) {
    element.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm)) {
            e.preventDefault();
        }
    });
});

// Auto-hide alerts after 5 seconds
window.setTimeout(function() {
    document.querySelectorAll(".alert-dismissible").forEach(function(alert) {
        if (!alert.classList.contains('alert-permanent')) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000); 