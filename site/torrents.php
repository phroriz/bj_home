<?php
session_start();

// Se não estiver autenticado, redireciona para login
if (!isset($_SESSION["autenticado"]) || $_SESSION["autenticado"] !== true) {
    header("Location: login.php");
    exit;
}

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
        http_response_code(500);
        echo json_encode(["error" => "Erro ao recuperar token: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    // Extrair o token
    if (!preg_match("/<div id='token' style='display:none;'>(.*?)<\/div>/", $response, $matches)) {
        http_response_code(500);
        echo json_encode(["error" => "Token não encontrado"]);
        exit;
    }
    $token = $matches[1];

    // Extrair o cookie GUID
    if (preg_match('/Set-Cookie: GUID=(.*?);/', $response, $cookieMatches)) {
        $guid = $cookieMatches[1];
    } else {
        http_response_code(500);
        echo json_encode(["error" => "GUID não encontrado"]);
        exit;
    }

    return [$token, $guid];
}

// Função para obter a lista de torrents
function getTorrents($host, $username, $password) {
    list($token, $guid) = getTokenAndCookie($host, $username, $password);
    if (!$token || !$guid) {
        http_response_code(500);
        echo json_encode(["error" => "Falha na autenticação"]);
        exit;
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
        http_response_code(500);
        echo json_encode(["error" => "Erro ao recuperar torrents: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(["error" => "Erro ao decodificar JSON: " . json_last_error_msg()]);
        exit;
    }

    $torrents = $data["torrents"] ?? [];

    $result = [];
    foreach ($torrents as $torrent) {
        $result[] = [
            "name" => $torrent[2], // Nome do torrent
            "progress" => $torrent[4] / 10, // Progresso em porcentagem
            "status" => $torrent[18] // Status do torrent
        ];
    }

    echo json_encode($result);
}

// Retornar torrents em JSON
header("Content-Type: application/json");
getTorrents($host, $username, $password);
?>
