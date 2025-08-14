<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Panel de Admin
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= \App\Core\View::url('/admin') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/restaurant') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-store me-2"></i>Mi Restaurante
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/reservations') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2"></i>Reservas
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/tables') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-chair me-2"></i>Mesas
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/shifts') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-clock me-2"></i>Turnos
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/reviews') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-star me-2"></i>Reseñas
                    </a>
                    <a href="<?= \App\Core\View::url('/admin/reports') ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2>
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </h2>
                    <p class="text-muted">
                        Bienvenido, <?= \App\Core\View::escape(\App\Helpers\Auth::getInstance()->name()) ?>
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card dashboard-card success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Reservas Este Mes</h6>
                                    <h3 class="mb-0"><?= $stats['reservations']['total_reservations'] ?? 0 ?></h3>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card dashboard-card info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Reseñas</h6>
                                    <h3 class="mb-0"><?= $stats['reviews']['total_reviews'] ?? 0 ?></h3>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card dashboard-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Calificación</h6>
                                    <h3 class="mb-0"><?= number_format($stats['reviews']['average_ratings']['overall'] ?? 0, 1) ?></h3>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-star-half-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card dashboard-card danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Mesas</h6>
                                    <h3 class="mb-0"><?= $stats['tables']['total_tables'] ?? 0 ?></h3>
                                </div>
                                <div class="text-danger">
                                    <i class="fas fa-chair fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reservations -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Reservas Recientes
                            </h5>
                            <a href="<?= \App\Core\View::url('/admin/reservations') ?>" 
                               class="btn btn-sm btn-outline-primary">
                                Ver Todas
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_reservations['data'] ?? $recent_reservations)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Personas</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $reservations = $recent_reservations['data'] ?? $recent_reservations;
                                            foreach (array_slice($reservations, 0, 5) as $reservation): 
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= \App\Core\View::escape($reservation['customer_name'] ?? $reservation['user_name'] ?? 'N/A') ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= $reservation['confirmation_code'] ?? 'N/A' ?></small>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></td>
                                                    <td><?= date('H:i', strtotime($reservation['reservation_time'])) ?></td>
                                                    <td><?= $reservation['party_size'] ?></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?php
                                                            switch ($reservation['status']) {
                                                                case 'pending': echo 'bg-warning'; break;
                                                                case 'confirmed': echo 'bg-success'; break;
                                                                case 'cancelled': echo 'bg-danger'; break;
                                                                case 'completed': echo 'bg-info'; break;
                                                                default: echo 'bg-secondary';
                                                            }
                                                            ?>">
                                                            <?= ucfirst($reservation['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($reservation['status'] === 'pending'): ?>
                                                            <form method="POST" 
                                                                  action="<?= \App\Core\View::url('/admin/reservations/' . $reservation['id'] . '/confirm') ?>" 
                                                                  class="d-inline">
                                                                <?= \App\Helpers\CSRF::field() ?>
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay reservas recientes</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Resumen de Hoy
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Confirmadas:</span>
                                    <span class="badge bg-success"><?= $stats['reservations']['by_status']['confirmed'] ?? 0 ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Pendientes:</span>
                                    <span class="badge bg-warning"><?= $stats['reservations']['by_status']['pending'] ?? 0 ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Canceladas:</span>
                                    <span class="badge bg-danger"><?= $stats['reservations']['by_status']['cancelled'] ?? 0 ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <h4><?= $stats['reservations']['average_party_size'] ?? '0.0' ?></h4>
                                <small class="text-muted">Promedio de personas por mesa</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Acciones Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= \App\Core\View::url('/admin/reservations') ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Ver Reservas de Hoy
                                </a>
                                <a href="<?= \App\Core\View::url('/admin/restaurant') ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>
                                    Editar Restaurante
                                </a>
                                <a href="<?= \App\Core\View::url('/admin/tables') ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-chair me-2"></i>
                                    Gestionar Mesas
                                </a>
                                <a href="<?= \App\Core\View::url('/admin/reports') ?>" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Ver Reportes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>