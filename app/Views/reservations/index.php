<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-calendar-alt me-2"></i>
                    Mis Reservas
                </h2>
                <a href="<?= \App\Core\View::url('/restaurants') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nueva Reserva
                </a>
            </div>

            <?php if (!empty($reservations['data'])): ?>
                <div class="row">
                    <?php foreach ($reservations['data'] as $reservation): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?= \App\Core\View::escape($reservation['restaurant_name']) ?></h6>
                                    <span class="badge 
                                        <?php
                                        switch ($reservation['status']) {
                                            case 'pending': echo 'bg-warning'; break;
                                            case 'confirmed': echo 'bg-success'; break;
                                            case 'cancelled': echo 'bg-danger'; break;
                                            case 'completed': echo 'bg-info'; break;
                                            case 'no_show': echo 'bg-secondary'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?php
                                        switch ($reservation['status']) {
                                            case 'pending': echo 'Pendiente'; break;
                                            case 'confirmed': echo 'Confirmada'; break;
                                            case 'cancelled': echo 'Cancelada'; break;
                                            case 'completed': echo 'Completada'; break;
                                            case 'no_show': echo 'No se presentó'; break;
                                            default: echo ucfirst($reservation['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar text-primary me-2"></i>
                                                <span><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-clock text-primary me-2"></i>
                                                <span><?= date('H:i', strtotime($reservation['reservation_time'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-users text-primary me-2"></i>
                                                <span><?= $reservation['party_size'] ?> 
                                                    <?= $reservation['party_size'] == 1 ? 'persona' : 'personas' ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-hashtag text-primary me-2"></i>
                                                <span class="fw-bold"><?= $reservation['confirmation_code'] ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($reservation['special_requests']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-comment me-1"></i>
                                                <?= \App\Core\View::escape($reservation['special_requests']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= \App\Core\View::escape($reservation['restaurant_address']) ?>
                                        </small>
                                    </div>

                                    <!-- Time until reservation -->
                                    <?php if ($reservation['status'] === 'confirmed' && strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']) > time()): ?>
                                        <?php
                                        $timeUntil = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']) - time();
                                        $daysUntil = floor($timeUntil / (24 * 3600));
                                        $hoursUntil = floor(($timeUntil % (24 * 3600)) / 3600);
                                        ?>
                                        <div class="alert alert-info py-2 mb-3">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?php if ($daysUntil > 0): ?>
                                                    Faltan <?= $daysUntil ?> días y <?= $hoursUntil ?> horas
                                                <?php elseif ($hoursUntil > 0): ?>
                                                    Faltan <?= $hoursUntil ?> horas
                                                <?php else: ?>
                                                    ¡Tu reserva es hoy!
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2">
                                        <a href="<?= \App\Core\View::url('/reservations/' . $reservation['id']) ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                        
                                        <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                                            <?php
                                            $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
                                            $canModify = $reservationDateTime - time() > 7200; // 2 hours
                                            ?>
                                            
                                            <?php if ($canModify): ?>
                                                <a href="<?= \App\Core\View::url('/reservations/' . $reservation['id'] . '/edit') ?>" 
                                                   class="btn btn-outline-warning btn-sm flex-fill">
                                                    <i class="fas fa-edit me-1"></i>Modificar
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-outline-danger btn-sm flex-fill" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal<?= $reservation['id'] ?>">
                                                <i class="fas fa-times me-1"></i>Cancelar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cancel Modal -->
                        <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                        <div class="modal fade" id="cancelModal<?= $reservation['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cancelar Reserva</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= \App\Core\View::url('/reservations/' . $reservation['id']) ?>">
                                        <?= \App\Helpers\CSRF::field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        
                                        <div class="modal-body">
                                            <p>¿Está seguro que desea cancelar esta reserva?</p>
                                            <div class="alert alert-warning">
                                                <strong>Restaurante:</strong> <?= \App\Core\View::escape($reservation['restaurant_name']) ?><br>
                                                <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?><br>
                                                <strong>Hora:</strong> <?= date('H:i', strtotime($reservation['reservation_time'])) ?><br>
                                                <strong>Código:</strong> <?= $reservation['confirmation_code'] ?>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Motivo de cancelación (opcional)</label>
                                                <textarea name="reason" class="form-control" rows="3" 
                                                          placeholder="Ingrese el motivo..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                No, mantener reserva
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Sí, cancelar reserva
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($reservations['total_pages'] > 1): ?>
                    <nav aria-label="Navegación de páginas" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($reservations['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $reservations['current_page'] - 1 ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $reservations['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $reservations['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($reservations['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $reservations['current_page'] + 1 ?>">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                    <h4>No tienes reservas</h4>
                    <p class="text-muted mb-4">Explora nuestros restaurantes y haz tu primera reserva</p>
                    <a href="<?= \App\Core\View::url('/restaurants') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Buscar Restaurantes
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>