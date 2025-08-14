<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Iniciar Sesión
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= \App\Core\View::url('/login') ?>">
                        <?= \App\Helpers\CSRF::field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= \App\Core\View::escape($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Iniciar Sesión
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="<?= \App\Core\View::url('/forgot-password') ?>" class="text-decoration-none">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center bg-light">
                    <span class="text-muted">¿No tienes cuenta?</span>
                    <a href="<?= \App\Core\View::url('/register') ?>" class="text-decoration-none">
                        Regístrate aquí
                    </a>
                </div>
            </div>

            <!-- Demo Accounts -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Cuentas de Demo</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Cliente:</strong> juan@cliente.com<br>
                        <strong>Admin Restaurante:</strong> carlos@restaurante.com<br>
                        <strong>Super Admin:</strong> admin@sistema.com<br>
                        <strong>Contraseña para todas:</strong> password
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>