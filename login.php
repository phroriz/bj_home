<?php
session_start();

// Conexão com banco de dados MySQL (ajuste conforme seu Docker)
$host = 'mysql'; // Nome do serviço no docker-compose
$db = 'login_db';
$user = 'user';
$password = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Chaves do reCAPTCHA
$recaptcha_site_key = "6LeOoH0qAAAAAJn8_VgE-2P8VA-nIel4MBDCQ3iN";
$recaptcha_secret_key = "6LeOoH0qAAAAAHCKaVxD2eG01adyJ5v0bTv7dvVB";

// IP do usuário e controle de tentativas
$ip_usuario = $_SERVER['REMOTE_ADDR'];
$tentativas_key = "tentativas_" . $ip_usuario;

if (!isset($_SESSION[$tentativas_key])) {
    $_SESSION[$tentativas_key] = 0;
}

if ($_SESSION[$tentativas_key] >= 5) {
    $erro = "Muitas tentativas de login. Tente novamente mais tarde.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];
    $recaptcha_response = $_POST["g-recaptcha-response"];

    // Verificação do reCAPTCHA
    $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
    $recaptcha_data = [
        "secret" => $recaptcha_secret_key,
        "response" => $recaptcha_response,
        "remoteip" => $ip_usuario
    ];

    $options = [
        "http" => [
            "header" => "Content-Type: application/x-www-form-urlencoded",
            "method" => "POST",
            "content" => http_build_query($recaptcha_data)
        ]
    ];

    $context = stream_context_create($options);
    $recaptcha_verify = file_get_contents($recaptcha_url, false, $context);
    $recaptcha_success = json_decode($recaptcha_verify, true);

    if (!$recaptcha_success["success"]) {
        $erro = "Verificação reCAPTCHA falhou. Tente novamente.";
    } else {
        // Consulta ao banco de dados
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND senha = :senha");
        $stmt->execute([
            ':usuario' => $usuario,
            ':senha' => md5($senha)
        ]);
        $usuario_encontrado = $stmt->fetch();

        if ($usuario_encontrado) {
            $_SESSION["autenticado"] = true;
            $_SESSION[$tentativas_key] = 0;
            header("Location: index.php");
            exit;
        } else {
            $_SESSION[$tentativas_key]++;
            $erro = "Usuário ou senha inválidos! Tentativas restantes: " . (5 - $_SESSION[$tentativas_key]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Acesso Restrito</h2>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm" style="max-width: 355px; margin: auto;">
            <div class="mb-3">
                <label class="form-label">Usuário</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            
            <div class="g-recaptcha mb-3" data-sitekey="<?= $recaptcha_site_key ?>"></div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</body>
</html>
