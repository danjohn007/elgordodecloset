<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Sistema de Reservas' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= \App\Core\View::asset('css/custom.css') ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= \App\Core\View::url('/') ?>">
                <i class="fas fa-utensils me-2"></i>
                El Gordo de Closet
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= \App\Core\View::url('/') ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= \App\Core\View::url('/restaurants') ?>">Restaurantes</a>
                    </li>
                    <?php if (\App\Helpers\Auth::getInstance()->check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= \App\Core\View::url('/reservations') ?>">Mis Reservas</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php $auth = \App\Helpers\Auth::getInstance(); ?>
                    <?php if ($auth->check()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?= \App\Core\View::escape($auth->name()) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ($auth->isSuperAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?= \App\Core\View::url('/superadmin') ?>">
                                        <i class="fas fa-cog me-2"></i>Super Admin
                                    </a></li>
                                <?php elseif ($auth->isAdminRestaurante()): ?>
                                    <li><a class="dropdown-item" href="<?= \App\Core\View::url('/admin') ?>">
                                        <i class="fas fa-store me-2"></i>Panel de Admin
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= \App\Core\View::url('/profile') ?>">
                                    <i class="fas fa-user-edit me-2"></i>Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="<?= \App\Core\View::url('/reviews') ?>">
                                    <i class="fas fa-star me-2"></i>Mis Reseñas
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= \App\Core\View::url('/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= \App\Core\View::url('/login') ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= \App\Core\View::url('/register') ?>">
                                <i class="fas fa-user-plus me-1"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-0">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= \App\Core\View::escape($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= \App\Core\View::escape($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= \App\Core\View::escape($_SESSION['info']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>El Gordo de Closet</h5>
                    <p>Sistema de Reservaciones para Restaurantes</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?= date('Y') ?> Todos los derechos reservados</p>
                    <a href="<?= \App\Core\View::url('/about') ?>" class="text-light me-3">Acerca de</a>
                    <a href="<?= \App\Core\View::url('/contact') ?>" class="text-light">Contacto</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= \App\Core\View::asset('js/app.js') ?>"></script>
</body>
</html>