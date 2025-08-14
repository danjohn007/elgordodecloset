// Main JavaScript for Restaurant Reservation System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Date input validation (no past dates)
    var dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        var today = new Date().toISOString().split('T')[0];
        if (!input.hasAttribute('min')) {
            input.setAttribute('min', today);
        }
    });

    // Time slot selection
    var timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(function(slot) {
        slot.addEventListener('click', function() {
            if (!slot.classList.contains('unavailable')) {
                // Remove selection from other slots
                timeSlots.forEach(function(s) {
                    s.classList.remove('selected');
                });
                
                // Select current slot
                slot.classList.add('selected');
                
                // Update hidden input if exists
                var timeInput = document.querySelector('input[name="time"]');
                if (timeInput) {
                    timeInput.value = slot.dataset.time;
                }
            }
        });
    });

    // Real-time availability check
    function checkAvailability() {
        var restaurantId = document.querySelector('input[name="restaurant_id"]');
        var date = document.querySelector('input[name="date"]');
        var partySize = document.querySelector('select[name="party_size"]');
        
        if (restaurantId && date && partySize) {
            var params = new URLSearchParams({
                restaurant_id: restaurantId.value,
                date: date.value,
                party_size: partySize.value
            });
            
            fetch('/api/restaurants/' + restaurantId.value + '/availability?' + params)
                .then(response => response.json())
                .then(data => {
                    updateTimeSlots(data.available_slots || []);
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                });
        }
    }

    function updateTimeSlots(availableSlots) {
        var slotsContainer = document.querySelector('.time-slots-container');
        if (!slotsContainer) return;
        
        slotsContainer.innerHTML = '';
        
        if (availableSlots.length === 0) {
            slotsContainer.innerHTML = '<p class="text-muted text-center">No hay horarios disponibles para esta fecha</p>';
            return;
        }
        
        availableSlots.forEach(function(slot) {
            var slotElement = document.createElement('div');
            slotElement.className = 'col-md-3 mb-2';
            slotElement.innerHTML = `
                <div class="time-slot" data-time="${slot.time}">
                    <strong>${slot.time_display}</strong><br>
                    <small>${slot.shift_name}</small>
                </div>
            `;
            slotsContainer.appendChild(slotElement);
        });
        
        // Re-attach event listeners
        var newTimeSlots = slotsContainer.querySelectorAll('.time-slot');
        newTimeSlots.forEach(function(slot) {
            slot.addEventListener('click', function() {
                newTimeSlots.forEach(function(s) {
                    s.classList.remove('selected');
                });
                slot.classList.add('selected');
                
                var timeInput = document.querySelector('input[name="time"]');
                if (timeInput) {
                    timeInput.value = slot.dataset.time;
                }
            });
        });
    }

    // Trigger availability check when date or party size changes
    var dateInput = document.querySelector('input[name="date"]');
    var partySizeSelect = document.querySelector('select[name="party_size"]');
    
    if (dateInput) {
        dateInput.addEventListener('change', checkAvailability);
    }
    
    if (partySizeSelect) {
        partySizeSelect.addEventListener('change', checkAvailability);
    }

    // Search functionality
    var searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var location = searchForm.querySelector('input[name="location"]');
            var date = searchForm.querySelector('input[name="date"]');
            
            if (!location.value.trim()) {
                e.preventDefault();
                location.focus();
                showAlert('Por favor, ingrese una ubicación', 'warning');
                return;
            }
            
            if (!date.value) {
                e.preventDefault();
                date.focus();
                showAlert('Por favor, seleccione una fecha', 'warning');
                return;
            }
        });
    }

    // Rating stars interaction
    var ratingInputs = document.querySelectorAll('.rating-input');
    ratingInputs.forEach(function(container) {
        var stars = container.querySelectorAll('.star');
        var hiddenInput = container.querySelector('input[type="hidden"]');
        
        stars.forEach(function(star, index) {
            star.addEventListener('click', function() {
                var rating = index + 1;
                hiddenInput.value = rating;
                
                // Update star display
                stars.forEach(function(s, i) {
                    if (i < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
            
            star.addEventListener('mouseover', function() {
                var rating = index + 1;
                stars.forEach(function(s, i) {
                    if (i < rating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#e9ecef';
                    }
                });
            });
        });
        
        container.addEventListener('mouseleave', function() {
            var currentRating = parseInt(hiddenInput.value) || 0;
            stars.forEach(function(s, i) {
                if (i < currentRating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#e9ecef';
                }
            });
        });
    });

    // Confirmation dialogs
    var deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            var message = button.dataset.confirm || 'Â¿EstÃ¡s seguro?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-refresh for admin panels (every 30 seconds)
    if (document.querySelector('.admin-dashboard')) {
        setInterval(function() {
            var refreshElements = document.querySelectorAll('[data-auto-refresh]');
            refreshElements.forEach(function(element) {
                // Refresh specific sections without full page reload
                // Implementation would depend on specific needs
            });
        }, 30000);
    }
});

// Utility functions
function showAlert(message, type = 'info') {
    var alertContainer = document.querySelector('.alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert-container';
        document.body.insertBefore(alertContainer, document.body.firstChild);
    }
    
    var alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    var time = new Date('1970-01-01T' + timeString);
    return time.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
}