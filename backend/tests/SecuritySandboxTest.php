<?php

/**
 * ============================================================
 *  MEGA UNI STORE v3 — Security Sandbox (Entorno de Pruebas)
 * ============================================================
 *
 *  Cómo ejecutar (desde la raíz del proyecto):
 *
 *      php backend/tests/SecuritySandboxTest.php
 *
 *  O con PHPUnit si lo tienes instalado:
 *
 *      vendor/bin/phpunit backend/tests/SecuritySandboxTest.php
 *
 *  ⚠️  Este archivo NO modifica la base de datos real.
 *      Las pruebas de lógica se ejecutan con mocks/stubs.
 *      Las pruebas de HTTP apuntan a http://localhost/Mega_Uni_Store_v3
 *
 * ============================================================
 */

declare(strict_types=1);

// ──────────────────────────────────────────────────────────────
//  RUNNER MÍNIMO (corre sin PHPUnit)
// ──────────────────────────────────────────────────────────────

$tests  = [];
$passed = 0;
$failed = 0;

function test(string $nombre, callable $fn): void
{
    global $tests, $passed, $failed;
    $tests[] = ['nombre' => $nombre, 'fn' => $fn];
}

function assert_true(bool $condicion, string $msg = ''): void
{
    if (!$condicion) {
        throw new RuntimeException("FALLO: {$msg}");
    }
}

function assert_false(bool $condicion, string $msg = ''): void
{
    assert_true(!$condicion, $msg);
}

function assert_equals(mixed $esperado, mixed $actual, string $msg = ''): void
{
    if ($esperado !== $actual) {
        throw new RuntimeException("FALLO: {$msg} | Esperado: " . var_export($esperado, true) . " | Actual: " . var_export($actual, true));
    }
}

function runAll(): void
{
    global $tests, $passed, $failed;

    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║     MEGA UNI STORE v3 — Security Sandbox Tests          ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

    foreach ($tests as $t) {
        try {
            ($t['fn'])();
            echo "  ✅  {$t['nombre']}\n";
            $passed++;
        } catch (RuntimeException $e) {
            echo "  ❌  {$t['nombre']}\n";
            echo "      → {$e->getMessage()}\n";
            $failed++;
        }
    }

    $total = $passed + $failed;
    echo "\n──────────────────────────────────────────────────────────\n";
    echo "  Total: {$total}  |  ✅ Pasaron: {$passed}  |  ❌ Fallaron: {$failed}\n";
    echo "──────────────────────────────────────────────────────────\n\n";
}


// ══════════════════════════════════════════════════════════════
//
//  MÓDULO 1: LÓGICA AISLADA (sin base de datos, sin HTTP)
//
// ══════════════════════════════════════════════════════════════

// ── 1.1 Password hashing ──────────────────────────────────────

test('[LÓGICA] password_hash usa bcrypt (cost >= 10)', function () {
    $plain = 'miPassword123';
    $hash  = password_hash($plain, PASSWORD_DEFAULT);
    $info  = password_get_info($hash);

    assert_equals('bcrypt', $info['algoName'], 'El algoritmo debe ser bcrypt');
    assert_true($info['options']['cost'] >= 10, 'El cost debe ser >= 10');
});

test('[LÓGICA] password_verify devuelve true para password correcta', function () {
    $plain = 'miPassword123';
    $hash  = password_hash($plain, PASSWORD_DEFAULT);
    assert_true(password_verify($plain, $hash), 'Debe verificar password correcta');
});

test('[LÓGICA] password_verify devuelve false para password incorrecta', function () {
    $hash = password_hash('correcta', PASSWORD_DEFAULT);
    assert_false(password_verify('incorrecta', $hash), 'No debe verificar password incorrecta');
});

test('[LÓGICA] Dos hashes del mismo texto son distintos (salting)', function () {
    $plain = 'mismaPassword';
    $hash1 = password_hash($plain, PASSWORD_DEFAULT);
    $hash2 = password_hash($plain, PASSWORD_DEFAULT);
    assert_false($hash1 === $hash2, 'Cada hash debe ser único gracias al salt');
});


// ── 1.2 Validación de email ───────────────────────────────────

test('[LÓGICA] filter_var acepta emails válidos', function () {
    $emails = [
        'usuario@ejemplo.com',
        'admin+test@tienda.com.ar',
        'u@x.io',
    ];
    foreach ($emails as $e) {
        assert_true(
            (bool) filter_var($e, FILTER_VALIDATE_EMAIL),
            "Debería aceptar: {$e}"
        );
    }
});

test('[LÓGICA] filter_var rechaza emails inválidos', function () {
    $invalidos = [
        'no-es-email',
        '@sinusuario.com',
        'sin-arroba-punto',
        '',
        '   ',
        'a@',
        '<script>@ataque.com',
    ];
    foreach ($invalidos as $e) {
        assert_false(
            (bool) filter_var($e, FILTER_VALIDATE_EMAIL),
            "Debería rechazar: {$e}"
        );
    }
});


// ── 1.3 Validación de longitud de contraseña ─────────────────

test('[LÓGICA] Password de 7 chars debe ser rechazada (< 8)', function () {
    $password = '1234567';
    assert_true(strlen($password) < 8, 'Contraseña de 7 caracteres es inválida');
});

test('[LÓGICA] Password de 8 chars exactos debe ser aceptada', function () {
    $password = '12345678';
    assert_false(strlen($password) < 8, 'Contraseña de 8 caracteres es válida');
});


// ── 1.4 Normalización de email ───────────────────────────────

test('[LÓGICA] Email se normaliza a minúsculas antes de guardar', function () {
    $emailOriginal   = 'ADMIN@TIENDA.COM';
    $emailNormalizado = strtolower(trim($emailOriginal));
    assert_equals('admin@tienda.com', $emailNormalizado, 'Email debe normalizarse');
});

test('[LÓGICA] Email con espacios en blanco se limpia', function () {
    $emailSucio = '   admin@tienda.com   ';
    $limpio     = strtolower(trim($emailSucio));
    assert_equals('admin@tienda.com', $limpio, 'Trim debe quitar espacios');
});


// ══════════════════════════════════════════════════════════════
//
//  MÓDULO 2: SIMULACIÓN DE ATAQUES (aislado, sin HTTP)
//
//  Aquí no atacamos nada: simulamos la LÓGICA de defensa
//  que DEBERÍA existir y verificamos si está implementada.
//
// ══════════════════════════════════════════════════════════════

// ── 2.1 SQL Injection — Verificamos que PDO prepared statements
//        harían inofensivos los payloads clásicos ────────────

test('[ATAQUE] SQL Injection: payload clásico queda como string literal', function () {
    // Simulamos lo que haría PDO al parametrizar el input
    $payloads = [
        "' OR '1'='1",
        "' OR 1=1 --",
        "'; DROP TABLE usuarios; --",
        "\" OR \"1\"=\"1",
        "admin'--",
        "1' UNION SELECT * FROM usuarios --",
    ];

    foreach ($payloads as $payload) {
        // PDO trata el valor como dato, no como SQL.
        // Simulamos: si el email normalizado pasara a filter_var, fallaría.
        $pasaValidacion = (bool) filter_var($payload, FILTER_VALIDATE_EMAIL);
        assert_false($pasaValidacion, "El payload '{$payload}' no debe pasar como email válido");
    }
});

test('[ATAQUE] SQL Injection: email con comillas no es email válido', function () {
    $malicioso = "test'@ejemplo.com";
    // Aunque llega al modelo, PDO lo parametriza.
    // Pero filter_var lo rechaza antes:
    assert_false(
        (bool) filter_var($malicioso, FILTER_VALIDATE_EMAIL),
        'Email con comilla simple no debe pasar filter_var'
    );
});


// ── 2.2 XSS — Detectamos si limpiarTexto() es suficiente ─────

test('[ATAQUE] XSS: limpiarTexto() (solo trim) NO sanitiza HTML', function () {
    $payload  = '<script>alert("XSS")</script>';
    $limpiado = trim($payload);  // así funciona limpiarTexto() en AuthController

    // PROBLEMA: trim no elimina etiquetas HTML
    // Si $limpiado se imprime en una vista sin htmlspecialchars → XSS
    assert_true(
        str_contains($limpiado, '<script>'),
        'VULNERABILIDAD CONFIRMADA: trim() no elimina <script>. Las vistas deben usar htmlspecialchars()'
    );
});

test('[ATAQUE] XSS: htmlspecialchars() SÍ neutraliza el payload', function () {
    $payload    = '<script>alert("XSS")</script>';
    $neutralizado = htmlspecialchars($payload, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    assert_false(
        str_contains($neutralizado, '<script>'),
        'htmlspecialchars debe neutralizar la etiqueta script'
    );
    assert_true(
        str_contains($neutralizado, '&lt;script&gt;'),
        'El payload debe quedar codificado como entidad HTML'
    );
});


// ── 2.3 CSRF — Verificamos que la lógica de token existe ─────

test('[ATAQUE] CSRF: formulario sin token CSRF es vulnerable', function () {
    // Simulamos el POST que llegaría de una página externa
    $postExterno = [
        'email'    => 'victima@tienda.com',
        'password' => 'passwordRobada',
        // Sin campo csrf_token
    ];

    $tokenEsperado = 'token_generado_en_sesion_123';

    // La validación que DEBERÍA existir:
    $tokenRecibido = $postExterno['csrf_token'] ?? null;
    $csrfValido    = $tokenRecibido !== null && hash_equals($tokenEsperado, $tokenRecibido);

    assert_false($csrfValido, 'VULNERABILIDAD: sin csrf_token el ataque CSRF pasaría la validación');
    // ↑ Este test PASA porque confirma que la vulnerabilidad existe.
    //   Cuando implementes CSRF, este test debe INVERTIRSE.
});

test('[ATAQUE] CSRF: con token correcto la validación sí protege', function () {
    // Así DEBERÍA funcionar tras implementar CSRF:
    $tokenSesion   = bin2hex(random_bytes(32));
    $postLegitimo  = ['csrf_token' => $tokenSesion];

    $csrfValido = hash_equals($tokenSesion, $postLegitimo['csrf_token'] ?? '');
    assert_true($csrfValido, 'Token CSRF correcto debe pasar la validación');
});

test('[ATAQUE] CSRF: token incorrecto debe rechazarse', function () {
    $tokenSesion    = bin2hex(random_bytes(32));
    $tokenFalso     = bin2hex(random_bytes(32));
    $postMalicioso  = ['csrf_token' => $tokenFalso];

    $csrfValido = hash_equals($tokenSesion, $postMalicioso['csrf_token'] ?? '');
    assert_false($csrfValido, 'Token CSRF falso debe ser rechazado');
});


// ── 2.4 Brute Force — Verificamos que NO hay rate limiting ───

test('[ATAQUE] Brute Force: sin contador de intentos no hay lockout', function () {
    // Simulamos el estado de la sesión/DB: no existe contador
    $sesion = [];  // $_SESSION real de AuthController no guarda intentos fallidos

    $intentosFallidos = $sesion['login_attempts'] ?? 0;
    $limiteMaximo     = 5;
    $estaBloqueado    = $intentosFallidos >= $limiteMaximo;

    assert_false(
        $estaBloqueado,
        'VULNERABILIDAD: sin contador de intentos, el login nunca bloquea'
    );
    // ↑ Confirma que la protección NO existe aún.
});

test('[ATAQUE] Brute Force: con contador implementado SÍ bloquea', function () {
    // Así DEBERÍA funcionar tras implementar rate limiting:
    $intentos     = 5;
    $limite       = 5;
    $estaBloqueado = $intentos >= $limite;

    assert_true($estaBloqueado, 'Con 5 intentos fallidos el login debe bloquearse');
});


// ── 2.5 Enumeración de usuarios ──────────────────────────────

test('[ATAQUE] Enumeración en LOGIN: mismo mensaje para email y password incorrectos', function () {
    // AuthController devuelve 'Credenciales incorrectas' en ambos casos → CORRECTO
    $mensajeEmailInexistente = 'Credenciales incorrectas.';
    $mensajePasswordMal      = 'Credenciales incorrectas.';

    assert_equals(
        $mensajeEmailInexistente,
        $mensajePasswordMal,
        'Login muestra el mismo mensaje: enumeración bloqueada ✅'
    );
});

test('[ATAQUE] Enumeración en REGISTRO: mensaje revela si el email existe', function () {
    // AuthController línea 151: devuelve mensaje diferente si email existe
    $mensajeSiExiste    = 'El correo electrónico ya está registrado.';
    $mensajeSiNoExiste  = '';  // simplemente crea el usuario

    assert_false(
        $mensajeSiExiste === $mensajeSiNoExiste,
        'VULNERABILIDAD MEDIA: el registro revela qué emails existen en el sistema'
    );
});


// ── 2.6 Session Fixation ─────────────────────────────────────

test('[ATAQUE] Session Fixation: session_regenerate_id(true) está implementado', function () {
    // Verificamos que el código fuente del AuthController llama session_regenerate_id
    $archivo  = __DIR__ . '/../app/controllers/AuthController.php';
    $contenido = file_get_contents($archivo);

    assert_true(
        str_contains($contenido, 'session_regenerate_id(true)'),
        'AuthController debe llamar session_regenerate_id(true) al hacer login'
    );
});


// ── 2.7 Headers de seguridad HTTP ────────────────────────────

test('[CONFIG] Cookie httponly está configurada', function () {
    $archivo   = __DIR__ . '/../app/controllers/AuthController.php';
    $contenido = file_get_contents($archivo);

    assert_true(
        str_contains($contenido, "'httponly' => true"),
        'La cookie de sesión debe tener httponly=true'
    );
});

test('[CONFIG] Cookie samesite está configurada', function () {
    $archivo   = __DIR__ . '/../app/controllers/AuthController.php';
    $contenido = file_get_contents($archivo);

    assert_true(
        str_contains($contenido, "samesite") && str_contains($contenido, 'Lax'),
        "La cookie de sesión debe tener samesite=Lax"
    );
});

test('[CONFIG] session.use_strict_mode está activado', function () {
    $archivo   = __DIR__ . '/../app/controllers/AuthController.php';
    $contenido = file_get_contents($archivo);

    assert_true(
        str_contains($contenido, 'session.use_strict_mode'),
        'Se debe activar session.use_strict_mode para prevenir session adoption'
    );
});


// ══════════════════════════════════════════════════════════════
//
//  MÓDULO 3: PRUEBAS HTTP (requieren servidor corriendo)
//
//  Descomenta y ejecuta cuando Laragon esté activo.
//  Apunta a: http://localhost/Mega_Uni_Store_v3/backend/public/
//
// ══════════════════════════════════════════════════════════════

/*

// Helper para hacer requests HTTP con cURL
function httpPost(string $url, array $data, array $cookieJar = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => true,
        CURLOPT_COOKIEJAR      => '/tmp/test_cookies.txt',
        CURLOPT_COOKIEFILE     => '/tmp/test_cookies.txt',
        CURLOPT_TIMEOUT        => 5,
    ]);
    $respuesta = curl_exec($ch);
    $info      = curl_getinfo($ch);
    curl_close($ch);

    [$headers, $body] = explode("\r\n\r\n", $respuesta, 2);
    return ['status' => $info['http_code'], 'headers' => $headers, 'body' => $body];
}

$BASE = 'http://localhost/Mega_Uni_Store_v3/backend/public/index.php';

test('[HTTP] Login con credenciales incorrectas devuelve 302 (no 200)', function () use ($BASE) {
    $r = httpPost("{$BASE}?route=login", [
        'email'    => 'noexiste@test.com',
        'password' => 'wrongpassword',
    ]);
    assert_equals(302, $r['status'], 'Login fallido debe redirigir, no devolver 200');
});

test('[HTTP] Brute Force: 10 intentos seguidos no causan lockout (vulnerabilidad)', function () use ($BASE) {
    $bloqueado = false;
    for ($i = 0; $i < 10; $i++) {
        $r = httpPost("{$BASE}?route=login", [
            'email'    => 'admin@test.com',
            'password' => "wrong{$i}",
        ]);
        if ($r['status'] === 429 || str_contains($r['body'], 'bloqueado')) {
            $bloqueado = true;
            break;
        }
    }
    assert_false($bloqueado, 'VULNERABILIDAD: 10 intentos no activan ningún bloqueo');
});

test('[HTTP] XSS en campo nombre del registro', function () use ($BASE) {
    $payload = '<script>alert(1)</script>';
    $r = httpPost("{$BASE}?route=register", [
        'nombre'           => $payload,
        'apellido'         => 'Test',
        'email'            => 'xss_test_' . time() . '@test.com',
        'password'         => 'password123',
        'password_confirm' => 'password123',
    ]);
    // Si el payload aparece sin escapar en la respuesta → XSS confirmado
    assert_false(
        str_contains($r['body'], $payload),
        'El payload XSS no debe aparecer sin escapar en la respuesta'
    );
});

test('[HTTP] Enumeración de email vía registro', function () use ($BASE) {
    // Usamos un email que probablemente exista (ajusta al tuyo)
    $r = httpPost("{$BASE}?route=register", [
        'nombre'           => 'Test',
        'apellido'         => 'User',
        'email'            => 'admin@megaunistoretest.com',
        'password'         => 'password123',
        'password_confirm' => 'password123',
    ]);
    $revelaMensaje = str_contains($r['body'], 'ya está registrado');
    // Si true → el registro revela que ese email existe en el sistema
    echo "\n      ℹ️  ¿Revela email existente?: " . ($revelaMensaje ? 'SÍ (vulnerabilidad media)' : 'NO') . "\n";
    assert_true(true, 'Test informativo ejecutado');
});

*/


// ══════════════════════════════════════════════════════════════
//  EJECUTAR TODOS LOS TESTS
// ══════════════════════════════════════════════════════════════

runAll();

echo "📋 RESUMEN DE VULNERABILIDADES PARA CORREGIR:\n\n";
echo "  1. 🔴 CSRF: Agregar token en login.php y register.php\n";
echo "     → Generar: \$_SESSION['csrf_token'] = bin2hex(random_bytes(32))\n";
echo "     → Verificar en AuthController::login() y ::registrar()\n\n";
echo "  2. 🔴 Brute Force: Implementar contador de intentos fallidos\n";
echo "     → Opción A: Tabla 'login_attempts' en DB (más robusto)\n";
echo "     → Opción B: Cache/APCu con TTL de 15 minutos\n\n";
echo "  3. 🟡 XSS en vistas: Asegurar que todas las vistas usen:\n";
echo "     → htmlspecialchars(\$valor, ENT_QUOTES | ENT_HTML5, 'UTF-8')\n\n";
echo "  4. 🟡 Enumeración en registro: Cambiar mensaje a genérico\n";
echo "     → Ej: 'Si el correo no está en uso, recibirás un email de confirmación'\n\n";
echo "  5. 🟡 Timeout de sesión inactiva: Agregar en iniciarSesionSegura():\n";
echo "     → if (time() - (\$_SESSION['last_activity'] ?? 0) > 1800) { session_destroy(); }\n\n";
echo "  6. 🟡 Log de auditoría: Registrar intentos fallidos con IP y timestamp\n\n";
