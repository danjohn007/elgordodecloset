<div class="container py-4">
    <!-- Restaurant Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <img src="<?= $restaurant['image_url'] ?? \App\Core\View::asset('img/restaurant-placeholder.jpg') ?>" 
                             class="card-img h-100" style="object-fit: cover;" 
                             alt="<?= \App\Core\View::escape($restaurant['name']) ?>">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h1 class="card-title mb-2"><?= \App\Core\View::escape($restaurant['name']) ?></h1>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary me-2"><?= \App\Core\View::escape($restaurant['cuisine_type']) ?></span>
                                        <span class="badge bg-info me-2"><?= str_repeat('$', strlen($restaurant['price_range'])) ?></span>
                                        <span class="badge bg-success">Abierto</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="rating-display">
                                        <span class="text-warning fs-5">
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
                                        <div class="text-muted">
                                            <?= number_format($restaurant['rating'], 1) ?>/5 
                                            (<?= $restaurant['total_reviews'] ?> reseñas)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($restaurant['description']): ?>
                                <p class="card-text mb-3"><?= \App\Core\View::escape($restaurant['description']) ?></p>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h6>
                                    <p class="text-muted"><?= \App\Core\View::escape($restaurant['address']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-clock me-2"></i>Horario</h6>
                                    <p class="text-muted">
                                        <?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                                        <?= date('H:i', strtotime($restaurant['closing_time'])) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-phone me-2"></i>Teléfono</h6>
                                    <p class="text-muted"><?= \App\Core\View::escape($restaurant['phone']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-users me-2"></i>Capacidad</h6>
                                    <p class="text-muted">Hasta <?= $restaurant['max_capacity'] ?> personas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Reservation Section -->
        <div class="col-lg-8 mb-4">
            <!-- Quick Reservation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Hacer Reserva
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (\App\Helpers\Auth::getInstance()->check()): ?>
                        <form action="<?= \App\Core\View::url('/reservations/create') ?>" method="GET">
                            <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" name="date" class="form-control" 
                                           value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Número de Personas</label>
                                    <select name="party_size" class="form-select" required>
                                        <option value="1">1 persona</option>
                                        <option value="2" selected>2 personas</option>
                                        <option value="3">3 personas</option>
                                        <option value="4">4 personas</option>
                                        <option value="5">5 personas</option>
                                        <option value="6">6 personas</option>
                                        <option value="7">7 personas</option>
                                        <option value="8">8+ personas</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Ver Disponibilidad
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="mb-3">Debes iniciar sesión para hacer una reserva</p>
                            <a href="<?= \App\Core\View::url('/login') ?>" class="btn btn-primary me-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                            <a href="<?= \App\Core\View::url('/register') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Times for Today/Tomorrow -->
            <?php if (!empty($today_slots) || !empty($tomorrow_slots)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Horarios Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($today_slots)): ?>
                            <h6 class="mb-3">Hoy (<?= date('d/m/Y') ?>)</h6>
                            <div class="row mb-4">
                                <?php foreach (array_slice($today_slots, 0, 8) as $slot): ?>
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= \App\Core\View::url('/reservations/create?restaurant_id=' . $restaurant['id'] . '&date=' . date('Y-m-d') . '&time=' . $slot['time']) ?>" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            <?= $slot['time_display'] ?>
                                            <br><small><?= $slot['shift_name'] ?></small>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($tomorrow_slots)): ?>
                            <h6 class="mb-3">Mañana (<?= date('d/m/Y', strtotime('+1 day')) ?>)</h6>
                            <div class="row">
                                <?php foreach (array_slice($tomorrow_slots, 0, 8) as $slot): ?>
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= \App\Core\View::url('/reservations/create?restaurant_id=' . $restaurant['id'] . '&date=' . date('Y-m-d', strtotime('+1 day')) . '&time=' . $slot['time']) ?>" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            <?= $slot['time_display'] ?>
                                            <br><small><?= $slot['shift_name'] ?></small>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Reseñas (<?= $reviews['total'] ?>)
                    </h5>
                    <?php if (\App\Helpers\Auth::getInstance()->check()): ?>
                        <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="fas fa-edit me-1"></i>Escribir Reseña
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($reviews['data'])): ?>
                        <?php foreach ($reviews['data'] as $review): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?= \App\Core\View::escape($review['user_name']) ?></h6>
                                        <div class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($review['created_at'])) ?></small>
                                </div>
                                <p class="mb-2"><?= \App\Core\View::escape($review['comment']) ?></p>
                                
                                <?php if ($review['response']): ?>
                                    <div class="bg-light p-3 rounded mt-2">
                                        <h6 class="mb-1">
                                            <i class="fas fa-reply me-1"></i>
                                            Respuesta del Restaurante
                                        </h6>
                                        <p class="mb-1"><?= \App\Core\View::escape($review['response']) ?></p>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($review['response_date'])) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($reviews['total'] > count($reviews['data'])): ?>
                            <div class="text-center">
                                <a href="<?= \App\Core\View::url('/restaurants/' . $restaurant['id'] . '/reviews') ?>" 
                                   class="btn btn-outline-primary">
                                    Ver Todas las Reseñas
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Aún no hay reseñas para este restaurante</p>
                            <?php if (\App\Helpers\Auth::getInstance()->check()): ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    <i class="fas fa-edit me-2"></i>Escribir la Primera Reseña
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Rating Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Calificaciones Detalladas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="display-6 text-warning"><?= number_format($rating_breakdown['overall_rating'], 1) ?></div>
                            <div class="text-muted">General</div>
                        </div>
                        <div class="col">
                            <div class="display-6 text-info"><?= number_format($rating_breakdown['food_rating'], 1) ?></div>
                            <div class="text-muted">Comida</div>
                        </div>
                        <div class="col">
                            <div class="display-6 text-success"><?= number_format($rating_breakdown['service_rating'], 1) ?></div>
                            <div class="text-muted">Servicio</div>
                        </div>
                        <div class="col">
                            <div class="display-6 text-primary"><?= number_format($rating_breakdown['ambiance_rating'], 1) ?></div>
                            <div class="text-muted">Ambiente</div>
                        </div>
                    </div>
                    
                    <!-- Rating distribution -->
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <?php $count = $rating_breakdown['rating_distribution'][$i] ?? 0; ?>
                        <?php $percentage = $rating_breakdown['total_reviews'] > 0 ? ($count / $rating_breakdown['total_reviews']) * 100 : 0; ?>
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2"><?= $i ?></span>
                            <i class="fas fa-star text-warning me-2"></i>
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <span class="text-muted small"><?= $count ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Información de Contacto</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <a href="tel:<?= $restaurant['phone'] ?>" class="text-decoration-none">
                            <?= \App\Core\View::escape($restaurant['phone']) ?>
                        </a>
                    </div>
                    
                    <?php if ($restaurant['email']): ?>
                        <div class="mb-3">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:<?= $restaurant['email'] ?>" class="text-decoration-none">
                                <?= \App\Core\View::escape($restaurant['email']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <?= \App\Core\View::escape($restaurant['address']) ?>
                    </div>
                    
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                        <?= date('H:i', strtotime($restaurant['closing_time'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<?php if (\App\Helpers\Auth::getInstance()->check()): ?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Escribir Reseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= \App\Core\View::url('/reviews') ?>">
                <?= \App\Helpers\CSRF::field() ?>
                <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Calificación General</label>
                        <div class="rating-input" data-rating="0">
                            <input type="hidden" name="rating" value="0">
                            <span class="star far fa-star" data-rating="1"></span>
                            <span class="star far fa-star" data-rating="2"></span>
                            <span class="star far fa-star" data-rating="3"></span>
                            <span class="star far fa-star" data-rating="4"></span>
                            <span class="star far fa-star" data-rating="5"></span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Comida</label>
                            <select name="food_rating" class="form-select" required>
                                <option value="">Calificar</option>
                                <option value="1">1 estrella</option>
                                <option value="2">2 estrellas</option>
                                <option value="3">3 estrellas</option>
                                <option value="4">4 estrellas</option>
                                <option value="5">5 estrellas</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Servicio</label>
                            <select name="service_rating" class="form-select" required>
                                <option value="">Calificar</option>
                                <option value="1">1 estrella</option>
                                <option value="2">2 estrellas</option>
                                <option value="3">3 estrellas</option>
                                <option value="4">4 estrellas</option>
                                <option value="5">5 estrellas</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ambiente</label>
                            <select name="ambiance_rating" class="form-select" required>
                                <option value="">Calificar</option>
                                <option value="1">1 estrella</option>
                                <option value="2">2 estrellas</option>
                                <option value="3">3 estrellas</option>
                                <option value="4">4 estrellas</option>
                                <option value="5">5 estrellas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comentario</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" 
                                  placeholder="Comparte tu experiencia..." required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Publicar Reseña</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>