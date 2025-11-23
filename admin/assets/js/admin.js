// Admin Panel JavaScript

// Toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('sidebar-close');

    // Open sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close sidebar
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking on a link (mobile only)
    if (window.innerWidth < 1024) {
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function () {
                setTimeout(closeSidebar, 100);
            });
        });
    }

    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            closeSidebar();
        }
    });
});


// Tab functionality
function showTab(tabId) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.add('hidden'));

    // Show selected tab
    document.getElementById(tabId).classList.remove('hidden');

    // Update tab buttons styling
    const buttons = document.querySelectorAll('#lecturerTabs button');
    buttons.forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        btn.classList.add('text-gray-600');
    });

    event.target.classList.remove('text-gray-600');
    event.target.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
}

// Modal functionality
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modals = document.querySelectorAll('[id$="Modal"]');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });
}

// Close modal on ESC key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('[id$="Modal"]');
        modals.forEach(modal => {
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });
    }
});

// Sidebar Dropdown Toggle
function toggleDropdown(dropdownId, button) {
    const dropdown = document.getElementById(dropdownId);
    const icon = button.querySelector('.fa-chevron-down');

    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        dropdown.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

