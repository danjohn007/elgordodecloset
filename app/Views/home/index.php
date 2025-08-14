<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Reserva tu Mesa</h1>
                <p class="lead mb-4">Descubre los mejores restaurantes y reserva tu mesa de forma fácil y rápida</p>
                
                <!-- Quick Search Form -->
                <form action="<?= \App\Core\View::url('/restaurants/search') ?>" method="POST" class="bg-white p-4 rounded shadow">
                    <?= \App\Helpers\CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-dark">Ubicación</label>
                            <input type="text" name="location" class="form-control" placeholder="Ciudad o restaurante">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-dark">Fecha</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-dark">Personas</label>
                            <select name="party_size" class="form-select">
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
                        <div class="col-md-2">
                            <label class="form-label text-dark">&nbsp;</label>
                            <button type="submit" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <img src="<?= \App\Core\View::asset('img/hero-restaurant.jpg') ?>" alt="Restaurant" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Featured Restaurants -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Restaurantes Destacados</h2>
                <p class="text-muted">Los restaurantes mejor calificados de la ciudad</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($featured_restaurants)): ?>
                <?php foreach ($featured_restaurants as $restaurant): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= $restaurant['image_url'] ?? \App\Core\View::asset('img/restaurant-placeholder.jpg') ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                 alt="<?= \App\Core\View::escape($restaurant['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= \App\Core\View::escape($restaurant['name']) ?></h5>
                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= \App\Core\View::escape($restaurant['address']) ?>
                                </p>
                                <p class="card-text mb-2">
                                    <span class="badge bg-secondary"><?= \App\Core\View::escape($restaurant['cuisine_type']) ?></span>
                                    <span class="badge bg-info"><?= str_repeat('$', strlen($restaurant['price_range'])) ?></span>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
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
                                        <small class="text-muted">(<?= $restaurant['total_reviews'] ?>)</small>
                                    </div>
                                    <a href="<?= \App\Core\View::url('/restaurants/' . $restaurant['id']) ?>" class="btn btn-primary btn-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No hay restaurantes disponibles</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center">
            <a href="<?= \App\Core\View::url('/restaurants') ?>" class="btn btn-outline-primary">
                Ver Todos los Restaurantes
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-4 mb-4">
                <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; line-height: 80px;">
                    <i class="fas fa-search fa-2x"></i>
                </div>
                <h4>Busca Fácil</h4>
                <p class="text-muted">Encuentra restaurantes por ubicación, tipo de comida y disponibilidad</p>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; line-height: 80px;">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h4>Reserva Instantánea</h4>
                <p class="text-muted">Confirma tu reserva al instante o recibe confirmación del restaurante</p>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; line-height: 80px;">
                    <i class="fas fa-bell fa-2x"></i>
                </div>
                <h4>Recordatorios</h4>
                <p class="text-muted">Recibe recordatorios por email y nunca olvides tus reservas</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Reviews -->
<?php if (!empty($recent_reviews)): ?>
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Reseñas Recientes</h2>
                <p class="text-muted">Lo que dicen nuestros clientes</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach (array_slice($recent_reviews, 0, 3) as $review): ?>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= \App\Core\View::escape($review['user_name']) ?></h6>
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
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-utensils me-1"></i>
                                <?= \App\Core\View::escape($review['restaurant_name']) ?>
                            </p>
                            <p class="card-text"><?= \App\Core\View::escape(substr($review['comment'], 0, 120)) ?>...</p>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($review['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">¿Tienes un restaurante?</h2>
        <p class="lead mb-4">Únete a nuestra plataforma y gestiona tus reservas de forma eficiente</p>
        <a href="<?= \App\Core\View::url('/register') ?>?role=admin_restaurante" class="btn btn-light btn-lg">
            <i class="fas fa-store me-2"></i>Registrar Restaurante
        </a>
    </div>
</section>