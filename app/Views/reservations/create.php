<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Nueva Reserva
                        </h4>
                        <a href="<?= \App\Core\View::url('/restaurants/' . $restaurant['id']) ?>" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Restaurante
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?= \App\Core\View::escape($restaurant['name']) ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= \App\Core\View::escape($restaurant['address']) ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-clock me-1"></i>
                                <?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                                <?= date('H:i', strtotime($restaurant['closing_time'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="rating-display">
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($restaurant['rating'])): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $restaurant['rating']): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <div class="text-muted small">
                                    <?= number_format($restaurant['rating'], 1) ?>/5 
                                    (<?= $restaurant['total_reviews'] ?> reseñas)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservation Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detalles de la Reserva</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= \App\Core\View::url('/reservations') ?>" id="reservationForm">
                        <?= \App\Helpers\CSRF::field() ?>
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                        <input type="hidden" name="time" id="selectedTime" value="<?= $selected_time ?? '' ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Fecha de la Reserva</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?= $date ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="party_size" class="form-label">Número de Personas</label>
                                <select class="form-select" id="party_size" name="party_size" required>
                                    <?php for ($i = 1; $i <= 20; $i++): ?>
                                        <option value="<?= $i ?>" <?= $party_size == $i ? 'selected' : '' ?>>
                                            <?= $i ?> <?= $i == 1 ? 'persona' : 'personas' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Available Time Slots -->
                        <div class="mb-4">
                            <label class="form-label">Horarios Disponibles</label>
                            <div class="row time-slots-container" id="timeSlotsContainer">
                                <?php if (!empty($available_slots)): ?>
                                    <?php foreach ($available_slots as $slot): ?>
                                        <div class="col-md-3 mb-2">
                                            <div class="time-slot <?= $selected_time === $slot['time'] ? 'selected' : '' ?>" 
                                                 data-time="<?= $slot['time'] ?>">
                                                <strong><?= $slot['time_display'] ?></strong><br>
                                                <small><?= $slot['shift_name'] ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-warning text-center">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            No hay horarios disponibles para la fecha y número de personas seleccionadas.
                                            <br>
                                            <small>Pruebe con una fecha diferente o menos personas.</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div id="noSlotsMessage" class="alert alert-warning text-center" style="display: none;">
                                <i class="fas fa-clock me-2"></i>
                                Cargando horarios disponibles...
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="mb-4">
                            <label for="special_requests" class="form-label">
                                Solicitudes Especiales (Opcional)
                            </label>
                            <textarea class="form-control" id="special_requests" name="special_requests" 
                                      rows="3" maxlength="500"
                                      placeholder="Ej: Mesa junto a la ventana, cumpleaños, alergias alimentarias, etc."></textarea>
                            <div class="form-text">Máximo 500 caracteres</div>
                        </div>

                        <!-- Customer Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Información de Contacto</h6>
                            </div>
                            <div class="card-body">
                                <?php $user = \App\Helpers\Auth::getInstance()->user(); ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" class="form-control" 
                                               value="<?= \App\Core\View::escape($user['name']) ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" 
                                               value="<?= \App\Core\View::escape($user['phone']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?= \App\Core\View::escape($user['email']) ?>" readonly>
                                </div>
                                <small class="text-muted">
                                    Los datos de contacto se tomarán de tu perfil. 
                                    <a href="/profile" class="text-decoration-none">Editar perfil</a>
                                </small>
                            </div>
                        </div>

                        <!-- Reservation Summary -->
                        <div class="card mb-4" id="reservationSummary" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Resumen de la Reserva</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Restaurante:</strong><br>
                                        <?= \App\Core\View::escape($restaurant['name']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Fecha y Hora:</strong><br>
                                        <span id="summaryDateTime">-</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Personas:</strong><br>
                                        <span id="summaryPartySize">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Estado:</strong><br>
                                        <span class="badge <?= $restaurant['auto_confirm'] ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $restaurant['auto_confirm'] ? 'Confirmación Automática' : 'Pendiente de Confirmación' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                                <label class="form-check-label" for="acceptTerms">
                                    Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a> 
                                    y la <a href="#" class="text-decoration-none">política de cancelación</a>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-check me-2"></i>
                                <?= $restaurant['auto_confirm'] ? 'Confirmar Reserva' : 'Solicitar Reserva' ?>
                            </button>
                            <a href="<?= \App\Core\View::url('/restaurants/' . $restaurant['id']) ?>" 
                               class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservationForm');
    const dateInput = document.getElementById('date');
    const partySizeSelect = document.getElementById('party_size');
    const selectedTimeInput = document.getElementById('selectedTime');
    const timeSlotsContainer = document.getElementById('timeSlotsContainer');
    const noSlotsMessage = document.getElementById('noSlotsMessage');
    const acceptTermsCheckbox = document.getElementById('acceptTerms');
    const submitBtn = document.getElementById('submitBtn');
    const reservationSummary = document.getElementById('reservationSummary');

    function updateAvailability() {
        const restaurantId = <?= $restaurant['id'] ?>;
        const date = dateInput.value;
        const partySize = partySizeSelect.value;

        if (!date || !partySize) return;

        // Show loading
        timeSlotsContainer.style.display = 'none';
        noSlotsMessage.style.display = 'block';
        noSlotsMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cargando horarios disponibles...';

        // Make AJAX request
        fetch(`/api/restaurants/${restaurantId}/availability?date=${date}&party_size=${partySize}`)
            .then(response => response.json())
            .then(data => {
                timeSlotsContainer.innerHTML = '';
                
                if (data.available_slots && data.available_slots.length > 0) {
                    data.available_slots.forEach(slot => {
                        const slotDiv = document.createElement('div');
                        slotDiv.className = 'col-md-3 mb-2';
                        slotDiv.innerHTML = `
                            <div class="time-slot" data-time="${slot.time}">
                                <strong>${slot.time_display}</strong><br>
                                <small>${slot.shift_name}</small>
                            </div>
                        `;
                        timeSlotsContainer.appendChild(slotDiv);
                    });
                    
                    // Re-attach click handlers
                    attachSlotHandlers();
                    timeSlotsContainer.style.display = '';
                    noSlotsMessage.style.display = 'none';
                } else {
                    noSlotsMessage.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay horarios disponibles para la fecha y número de personas seleccionadas.
                        <br><small>Pruebe con una fecha diferente o menos personas.</small>
                    `;
                    timeSlotsContainer.style.display = 'none';
                }
                
                // Reset selection
                selectedTimeInput.value = '';
                updateSubmitButton();
                updateSummary();
            })
            .catch(error => {
                console.error('Error:', error);
                noSlotsMessage.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error al cargar los horarios. Por favor, inténtelo de nuevo.
                `;
            });
    }

    function attachSlotHandlers() {
        const timeSlots = timeSlotsContainer.querySelectorAll('.time-slot');
        timeSlots.forEach(slot => {
            slot.addEventListener('click', function() {
                // Remove selection from other slots
                timeSlots.forEach(s => s.classList.remove('selected'));
                
                // Select current slot
                this.classList.add('selected');
                selectedTimeInput.value = this.dataset.time;
                
                updateSubmitButton();
                updateSummary();
            });
        });
    }

    function updateSubmitButton() {
        const hasTime = selectedTimeInput.value !== '';
        const hasAccepted = acceptTermsCheckbox.checked;
        submitBtn.disabled = !(hasTime && hasAccepted);
    }

    function updateSummary() {
        if (selectedTimeInput.value) {
            const date = new Date(dateInput.value);
            const timeString = selectedTimeInput.value;
            const partySize = partySizeSelect.value;
            
            document.getElementById('summaryDateTime').textContent = 
                `${date.toLocaleDateString('es-ES')} a las ${timeString}`;
            document.getElementById('summaryPartySize').textContent = 
                `${partySize} ${partySize == 1 ? 'persona' : 'personas'}`;
            
            reservationSummary.style.display = 'block';
        } else {
            reservationSummary.style.display = 'none';
        }
    }

    // Event listeners
    dateInput.addEventListener('change', updateAvailability);
    partySizeSelect.addEventListener('change', updateAvailability);
    acceptTermsCheckbox.addEventListener('change', updateSubmitButton);

    // Character counter for special requests
    const specialRequestsTextarea = document.getElementById('special_requests');
    const charCounter = document.createElement('small');
    charCounter.className = 'text-muted float-end';
    specialRequestsTextarea.parentNode.appendChild(charCounter);
    
    function updateCharCounter() {
        const remaining = 500 - specialRequestsTextarea.value.length;
        charCounter.textContent = `${remaining} caracteres restantes`;
        charCounter.className = remaining < 50 ? 'text-danger float-end' : 'text-muted float-end';
    }
    
    specialRequestsTextarea.addEventListener('input', updateCharCounter);
    updateCharCounter();

    // Initial setup
    attachSlotHandlers();
    updateSubmitButton();
    updateSummary();
    
    // Load availability if date and party size are pre-selected
    if (dateInput.value && partySizeSelect.value) {
        updateAvailability();
    }
});
</script>