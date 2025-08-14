<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Cuenta
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= \App\Core\View::url('/register') ?>">
                        <?= \App\Helpers\CSRF::field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= \App\Core\View::escape($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= \App\Core\View::escape($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= \App\Core\View::escape($_POST['phone'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Tipo de Cuenta</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Seleccionar...</option>
                                <option value="cliente" <?= ($_POST['role'] ?? '') === 'cliente' ? 'selected' : '' ?>>
                                    Cliente - Hacer reservas
                                </option>
                                <option value="admin_restaurante" <?= ($_POST['role'] ?? $_GET['role'] ?? '') === 'admin_restaurante' ? 'selected' : '' ?>>
                                    Administrador de Restaurante - Gestionar reservas
                                </option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="6" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i>
                            Crear Cuenta
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <span class="text-muted">¿Ya tienes cuenta?</span>
                    <a href="<?= \App\Core\View::url('/login') ?>" class="text-decoration-none">
                        Inicia sesión aquí
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password !== confirmation) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>