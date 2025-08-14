<div class="container py-4">
    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        Buscar Restaurantes
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= \App\Core\View::url('/restaurants') ?>" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nombre o Ubicación</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= \App\Core\View::escape($filters['name']) ?>" 
                                   placeholder="Buscar...">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Tipo de Cocina</label>
                            <select name="cuisine_type" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($cuisine_types as $cuisine): ?>
                                    <option value="<?= \App\Core\View::escape($cuisine['cuisine_type']) ?>" 
                                            <?= $filters['cuisine_type'] === $cuisine['cuisine_type'] ? 'selected' : '' ?>>
                                        <?= \App\Core\View::escape($cuisine['cuisine_type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Rango de Precio</label>
                            <select name="price_range" class="form-select">
                                <option value="">Todos</option>
                                <option value="$" <?= $filters['price_range'] === '$' ? 'selected' : '' ?>>$ Económico</option>
                                <option value="$$" <?= $filters['price_range'] === '$$' ? 'selected' : '' ?>>$$ Moderado</option>
                                <option value="$$$" <?= $filters['price_range'] === '$$$' ? 'selected' : '' ?>>$$$ Caro</option>
                                <option value="$$$$" <?= $filters['price_range'] === '$$$$' ? 'selected' : '' ?>>$$$$ Muy Caro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Calificación Mínima</label>
                            <select name="min_rating" class="form-select">
                                <option value="">Todas</option>
                                <option value="4" <?= $filters['min_rating'] === '4' ? 'selected' : '' ?>>4+ estrellas</option>
                                <option value="3" <?= $filters['min_rating'] === '3' ? 'selected' : '' ?>>3+ estrellas</option>
                                <option value="2" <?= $filters['min_rating'] === '2' ? 'selected' : '' ?>>2+ estrellas</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Ordenar por</label>
                            <select name="sort" class="form-select">
                                <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Calificación</option>
                                <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Nombre</option>
                                <option value="created_at" <?= $filters['sort'] === 'created_at' ? 'selected' : '' ?>>Más Recientes</option>
                            </select>
                        </div>
                        
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    Encontrados <?= $restaurants['total'] ?> restaurantes
                    <?php if ($restaurants['current_page'] > 1): ?>
                        - Página <?= $restaurants['current_page'] ?> de <?= $restaurants['total_pages'] ?>
                    <?php endif; ?>
                </h4>
            </div>
        </div>
    </div>

    <!-- Restaurant Grid -->
    <div class="row">
        <?php if (!empty($restaurants['data'])): ?>
            <?php foreach ($restaurants['data'] as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card restaurant-card h-100 shadow-sm">
                        <img src="<?= $restaurant['image_url'] ?? \App\Core\View::asset('img/restaurant-placeholder.jpg') ?>" 
                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                             alt="<?= \App\Core\View::escape($restaurant['name']) ?>">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= \App\Core\View::escape($restaurant['name']) ?></h5>
                            
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= \App\Core\View::escape($restaurant['address']) ?>
                            </p>
                            
                            <?php if ($restaurant['description']): ?>
                                <p class="card-text">
                                    <?= \App\Core\View::escape(substr($restaurant['description'], 0, 100)) ?>...
                                </p>
                            <?php endif; ?>
                            
                            <div class="mb-2">
                                <span class="badge bg-secondary me-1"><?= \App\Core\View::escape($restaurant['cuisine_type']) ?></span>
                                <span class="badge bg-info me-1"><?= str_repeat('$', strlen($restaurant['price_range'])) ?></span>
                                <?php if ($restaurant['status'] === 'active'): ?>
                                    <span class="badge bg-success">Abierto</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
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
                                <small class="text-muted">
                                    <?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                                    <?= date('H:i', strtotime($restaurant['closing_time'])) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="<?= \App\Core\View::url('/restaurants/' . $restaurant['id']) ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-2"></i>Ver Detalles
                                </a>
                                <a href="<?= \App\Core\View::url('/reservations/create?restaurant_id=' . $restaurant['id']) ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>Reservar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No se encontraron restaurantes</h4>
                    <p class="text-muted">Intenta ajustar tus filtros de búsqueda</p>
                    <a href="<?= \App\Core\View::url('/restaurants') ?>" class="btn btn-primary">
                        Ver Todos los Restaurantes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($restaurants['total_pages'] > 1): ?>
        <nav aria-label="Navegación de páginas" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <?php if ($restaurants['has_prev']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $restaurants['current_page'] - 1])) ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $start = max(1, $restaurants['current_page'] - 2);
                $end = min($restaurants['total_pages'], $restaurants['current_page'] + 2);
                ?>
                
                <?php if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $restaurants['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $restaurants['total_pages']): ?>
                    <?php if ($end < $restaurants['total_pages'] - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $restaurants['total_pages']])) ?>">
                            <?= $restaurants['total_pages'] ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($restaurants['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $restaurants['current_page'] + 1])) ?>">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>