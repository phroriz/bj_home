<?php
session_start();

// Se não estiver autenticado, redireciona para login
if (!isset($_SESSION["autenticado"]) || $_SESSION["autenticado"] !== true) {
    header("Location: login.php");
    exit;
}
?>

<?php
// Configuração do WebUI
$host = "http://host.docker.internal:8181";
$username = "admin";
$password = "admin";

// Função para obter o token do WebUI
function getToken($host, $username, $password) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$host/gui/token.html");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $response = curl_exec($ch);

    if (!$response) {
        echo "Erro ao recuperar o token: " . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    // Extrai o token do HTML
    if (preg_match("/<div id='token' style='display:none;'>(.*?)<\/div>/", $response, $matches)) {
        return $matches[1];
    }

    return null;
}

// Testar obtenção do token
$token = getToken($host, $username, $password);
if ($token) {
    echo "Token recuperado com sucesso: " . $token;
} else {
    echo "Falha ao recuperar o token.";
}
?>
