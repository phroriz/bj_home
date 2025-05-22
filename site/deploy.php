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

// Função para obter o token do WebUI e o cookie GUID
function getTokenAndCookie($host, $username, $password) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$host/gui/token.html");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HEADER, 1); // Capturar cabeçalhos

    $response = curl_exec($ch);

    if (!$response) {
        echo "Erro ao recuperar token: " . curl_error($ch) . "\n";
        curl_close($ch);
        return [null, null];
    }

    curl_close($ch);

    // Extrair o token
    if (!preg_match("/<div id='token' style='display:none;'>(.*?)<\/div>/", $response, $matches)) {
        echo "Token não encontrado!\n";
        return [null, null];
    }
    $token = $matches[1];

    // Extrair o cookie GUID
    if (preg_match('/Set-Cookie: GUID=(.*?);/', $response, $cookieMatches)) {
        $guid = $cookieMatches[1];
    } else {
        echo "GUID não encontrado!\n";
        return [$token, null];
    }

    return [$token, $guid];
}

// Função para obter a lista de torrents
function getTorrents($host, $username, $password) {
    list($token, $guid) = getTokenAndCookie($host, $username, $password);
    if (!$token || !$guid) {
        echo "Erro: Não foi possível recuperar o token ou GUID.\n";
        return;
    }

    // Criar cabeçalhos com o cookie GUID
    $headers = [
        "Cookie: GUID=$guid"
    ];

    // Fazer requisição para listar torrents
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$host/gui/?token=$token&list=1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $response = curl_exec($ch);

    if (!$response) {
        echo "Erro ao recuperar torrents: " . curl_error($ch) . "\n";
        curl_close($ch);
        return;
    }

    curl_close($ch);

    // Depuração: imprimir a resposta bruta para análise
    echo "Resposta bruta da API:\n$response\n";

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erro ao decodificar JSON: " . json_last_error_msg() . "\n";
        return;
    }

    $torrents = $data["torrents"] ?? [];

    if (empty($torrents)) {
        echo "Nenhum torrent encontrado!\n";
        return;
    }

    echo "=== Lista de Torrents ===\n";
    foreach ($torrents as $torrent) {
        echo "Nome: " . $torrent[2] . "\n";
        echo "Progresso: " . ($torrent[4] / 10) . "%\n";
        echo "-------------------------\n";
    }
}

// Executar no console
getTorrents($host, $username, $password);
?>
