
<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $cliente
 * @var string $csrfToken
 * @var array $usuario
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<div style="max-width:640px; margin:0 auto;">
    <h1 style="font-size:26px; font-weight:800; margin-bottom:24px;">👤 Mi perfil</h1>

    <div class="card" style="padding:32px;">
        <form method="post" action="index.php?route=portal.perfil.post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="text-align:center; margin-bottom:28px;">
                <div style="width:80px; height:80px; border-radius:50%; background:var(--primary); color:#fff; font-size:32px; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                    <?= strtoupper(substr($cliente['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <p style="font-size:13px; color:var(--gray-400);"><?= htmlspecialchars($usuario['email'] ?? '') ?></p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($cliente['apellido'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="tel" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" placeholder="+57 300 000 0000">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo de documento</label>
                    <select name="tipo_documento" class="form-control">
                        <?php foreach (['CC','TI','CE','Pasaporte'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($cliente['tipo_documento'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Número de documento</label>
                    <input type="text" name="numero_documento" class="form-control" value="<?= htmlspecialchars($cliente['numero_documento'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Dirección de entrega</label>
                <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>" placeholder="Calle, número, barrio, ciudad">
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
                <a href="index.php?route=password.change" class="btn btn-outline">🔑 Cambiar contraseña</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
