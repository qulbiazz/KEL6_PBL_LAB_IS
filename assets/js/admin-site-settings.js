/**
 * Admin Site Settings JavaScript
 * Handles form submissions and live preview updates
 */

$(document).ready(function() {
    // Live preview for logo file input
    $('#logo_file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Live preview for footer about
    $('#footer_about_title').on('input', function() {
        $('#previewTitle').text($(this).val());
    });

    $('#footer_about_text').on('input', function() {
        $('#previewText').text($(this).val());
    });

    // Live preview for contact
    $('#contact_email').on('input', function() {
        $('#previewEmail').html('<i class="fas fa-envelope mr-2"></i>' + $(this).val());
    });

    $('#contact_phone').on('input', function() {
        $('#previewPhone').html('<i class="fas fa-phone mr-2"></i>' + $(this).val());
    });

    // Show alert function
    function showAlert(message, type = 'success') {
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHTML = `
            <div class="border-l-4 ${alertClass} p-4 rounded-lg shadow-md animate-fade-in-down" role="alert">
                <div class="flex items-center">
                    <i class="fas ${iconClass} mr-3 text-xl"></i>
                    <div>
                        <p class="font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        $('#alert-container').html(alertHTML);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('#alert-container').html('');
        }, 5000);
    }

    // Logo Form Submission
    $('#logoForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const logoFile = $('#logo_file')[0].files[0];
        const logoAlt = $('#logo_alt').val();
        
        if (logoFile) {
            formData.append('logo_file', logoFile);
        }
        formData.append('logo_alt', logoAlt);
        formData.append('action', 'update_logo');
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...').prop('disabled', true);
        
        $.ajax({
            url: '../views/pages/ajax_update_site_settings.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    if (response.logo_path) {
                        $('#currentLogo').attr('src', '../' + response.logo_path);
                        $('#logoPreview').attr('src', '../' + response.logo_path);
                    }
                    $('#logo_file').val(''); // Clear file input
                } else {
                    showAlert(response.message || 'Failed to update logo', 'error');
                }
            },
            error: function() {
                showAlert('An error occurred while updating logo', 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Footer About Form Submission
    $('#footerAboutForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            action: 'update_footer_about',
            footer_about_title: $('#footer_about_title').val(),
            footer_about_text: $('#footer_about_text').val()
        };
        
        submitForm(this, data, 'Footer about section updated successfully!');
    });

    // Contact Form Submission
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            action: 'update_contact',
            contact_email: $('#contact_email').val(),
            contact_phone: $('#contact_phone').val(),
            contact_address: $('#contact_address').val()
        };
        
        submitForm(this, data, 'Contact information updated successfully!');
    });

    // Social Media Form Submission
    $('#socialForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            action: 'update_social',
            social_facebook: $('#social_facebook').val(),
            social_instagram: $('#social_instagram').val(),
            social_youtube: $('#social_youtube').val(),
            social_linkedin: $('#social_linkedin').val(),
            social_twitter: $('#social_twitter').val()
        };
        
        submitForm(this, data, 'Social media links updated successfully!');
    });

    // =========================================================================
    // QUICK LINKS MANAGEMENT
    // =========================================================================
    
    // Available menu items from navbar
    const menuOptions = [
        { label: 'Home', url: 'index.php' },
        { label: 'About', url: 'index.php?page=about' },
        { label: 'Products', url: 'index.php#products' },
        { label: 'Team', url: 'index.php#team' },
        { label: 'News', url: 'index.php#news' },
        { label: 'Gallery', url: 'index.php#gallery' },
        { label: 'Research', url: 'index.php#research' },
        { label: 'Guestbook', url: 'index.php#guestbook' },
        { label: 'Peminjaman', url: 'index.php?page=borrowing' },
        { label: 'Attendance', url: 'index.php?page=attendance' }
    ];

    let quickLinkCounter = 0;

    // Load existing quick links from database
    function loadQuickLinks() {
        $.ajax({
            url: '../views/pages/ajax_get_site_settings.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.settings.quick_links) {
                    const links = response.settings.quick_links;
                    $('#quickLinksContainer').empty();
                    
                    if (links.length > 0) {
                        links.forEach(link => {
                            addQuickLinkItem(link.label, link.url);
                        });
                    } else {
                        // Add default 4 links if empty
                        addQuickLinkItem('Home', 'index.php');
                        addQuickLinkItem('About', 'index.php?page=about');
                        addQuickLinkItem('Products', 'index.php#products');
                        addQuickLinkItem('Research', 'index.php#research');
                    }
                } else {
                    // Add default 4 links on error
                    addQuickLinkItem('Home', 'index.php');
                    addQuickLinkItem('About', 'index.php?page=about');
                    addQuickLinkItem('Products', 'index.php#products');
                    addQuickLinkItem('Research', 'index.php#research');
                }
                updateAddButtonState();
            },
            error: function() {
                // Add default 4 links on error
                addQuickLinkItem('Home', 'index.php');
                addQuickLinkItem('About', 'index.php?page=about');
                addQuickLinkItem('Products', 'index.php#products');
                addQuickLinkItem('Research', 'index.php#research');
                updateAddButtonState();
            }
        });
    }

    // Add a quick link item to the form
    function addQuickLinkItem(label = '', url = '') {
        const index = quickLinkCounter++;
        const optionsHTML = menuOptions.map(option => 
            `<option value="${option.url}" ${option.url === url ? 'selected' : ''}>${option.label}</option>`
        ).join('');

        const linkHTML = `
            <div class="quick-link-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="${index}">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-grip-vertical text-gray-400 cursor-move drag-handle"></i>
                    <span class="text-sm font-medium text-gray-600">Link #${index + 1}</span>
                    <button type="button" class="ml-auto text-red-600 hover:text-red-700 remove-link-btn" title="Remove link">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Text</label>
                        <input type="text" name="link_label_${index}" value="${label}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="e.g., Home" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL / Menu Item</label>
                        <select name="link_url_${index}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <option value="">Select a page...</option>
                            ${optionsHTML}
                        </select>
                    </div>
                </div>
            </div>
        `;

        $('#quickLinksContainer').append(linkHTML);
        updateAddButtonState();
        updateLinkNumbers();
    }

    // Remove a quick link item
    $(document).on('click', '.remove-link-btn', function() {
        $(this).closest('.quick-link-item').fadeOut(300, function() {
            $(this).remove();
            updateAddButtonState();
            updateLinkNumbers();
        });
    });

    // Add new quick link
    $('#addQuickLinkBtn').on('click', function() {
        const currentCount = $('.quick-link-item').length;
        if (currentCount < 8) {
            addQuickLinkItem();
        }
    });

    // Update add button state (disable if max reached)
    function updateAddButtonState() {
        const currentCount = $('.quick-link-item').length;
        if (currentCount >= 8) {
            $('#addQuickLinkBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        } else {
            $('#addQuickLinkBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        }
    }

    // Update link numbers after add/remove
    function updateLinkNumbers() {
        $('.quick-link-item').each(function(index) {
            $(this).find('.text-gray-600').text('Link #' + (index + 1));
        });
    }

    // Make links sortable (drag and drop)
    if (typeof $.fn.sortable !== 'undefined') {
        $('#quickLinksContainer').sortable({
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                updateLinkNumbers();
            }
        });
    }

    // Quick Links Form Submission
    $('#quickLinksForm').on('submit', function(e) {
        e.preventDefault();
        
        const links = [];
        $('.quick-link-item').each(function() {
            const $item = $(this);
            const index = $item.data('index');
            const label = $item.find(`input[name="link_label_${index}"]`).val();
            const url = $item.find(`select[name="link_url_${index}"]`).val();
            
            if (label && url) {
                links.push({ label: label, url: url });
            }
        });

        if (links.length === 0) {
            showAlert('Please add at least one quick link', 'error');
            return;
        }

        const data = {
            action: 'update_quick_links',
            links: links
        };
        
        submitForm(this, data, 'Quick links updated successfully!');
    });

    // Initialize quick links on page load
    loadQuickLinks();

    // Generic form submission function
    function submitForm(form, data, successMessage) {
        const submitBtn = $(form).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);
        
        $.ajax({
            url: '../views/pages/ajax_update_site_settings.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(successMessage, 'success');
                    updatePreview();
                } else {
                    showAlert(response.message || 'Failed to update settings', 'error');
                }
            },
            error: function() {
                showAlert('An error occurred while updating settings', 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }

    // Update social media preview
    function updatePreview() {
        const socialHTML = [];
        
        if ($('#social_facebook').val()) {
            socialHTML.push('<i class="fab fa-facebook text-lg"></i>');
        }
        if ($('#social_instagram').val()) {
            socialHTML.push('<i class="fab fa-instagram text-lg"></i>');
        }
        if ($('#social_youtube').val()) {
            socialHTML.push('<i class="fab fa-youtube text-lg"></i>');
        }
        if ($('#social_linkedin').val()) {
            socialHTML.push('<i class="fab fa-linkedin text-lg"></i>');
        }
        if ($('#social_twitter').val()) {
            socialHTML.push('<i class="fab fa-twitter text-lg"></i>');
        }
        
        $('#previewSocial').html(socialHTML.join(''));
    }
});

// Add fade-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out;
    }
`;
document.head.appendChild(style);
