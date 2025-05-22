<?php
session_start();

// Se não estiver autenticado, redireciona para login
if (!isset($_SESSION["autenticado"]) || $_SESSION["autenticado"] !== true) {
  //  header("Location: login.php");
   // exit;
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de Torrents</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { max-width: 900px; margin: auto; }
        
        /* Estilizando os balões */
        .action-box {
        width: 150px;
        height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-radius: 15px;
        box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease-in-out;
        background-color: white; /* Cor branca */
        color: #333; /* Texto escuro */
        cursor: pointer;
    }

    .action-box:hover {
        transform: scale(1.05);
        box-shadow: 4px 6px 15px rgba(0, 0, 0, 0.3);
    }

    .action-box i {
        font-size: 60px; /* Ícone maior */
    }

    .action-box p {
        margin-top: 10px;
        font-size: 16px;
        font-weight: bold;
    }

        /* Estilizando a tabela */
        table {
            margin-top: 20px;
        }

        .progress {
            width: 100px;
            height: 20px;
        }
    </style>

    <script>
        function atualizarTorrents() {
            fetch("torrents.php")
                .then(response => response.json())
                .then(data => {
                    let tabela = document.getElementById("torrents");
                    tabela.innerHTML = "";

                    data.forEach(torrent => {
                        let row = tabela.insertRow();
                        row.insertCell(0).textContent = torrent.name;
                        row.insertCell(1).textContent = torrent.status;
                        row.insertCell(2).textContent = (torrent.progress).toFixed(2) + "%";
                        
                        let progressCell = row.insertCell(3);
                        let progressBar = document.createElement("div");
                        progressBar.className = "progress";
                        let progressFill = document.createElement("div");
                        progressFill.className = "progress-bar bg-success";
                        progressFill.style.width = (torrent.progress) + "%";
                        progressFill.setAttribute("role", "progressbar");
                        progressBar.appendChild(progressFill);
                        progressCell.appendChild(progressBar);
                    });
                })
                .catch(error => console.error("Erro ao atualizar torrents:", error));
        }

        setInterval(atualizarTorrents, 5000);
        window.onload = atualizarTorrents;
    </script>
    
</head>
<body>
    <div class="container mt-4">

     <!-- Balões de ação -->
<div class="d-flex justify-content-center gap-4 mt-4">
    <a href="http://roriz.ddns.net:8085/" class="text-decoration-none" target="_blank" rel="noopener noreferrer">
        <div class="action-box">
            <i class="bi bi-download"></i> <!-- Ícone de Download -->
            <p>Baixar Arquivos</p>
        </div>
    </a>

    <a href="http://roriz.ddns.net:8081/guacamole/#/" class="text-decoration-none" target="_blank" rel="noopener noreferrer">
        <div class="action-box">
            <i class="bi bi-pc"></i> <!-- Ícone de PC -->
            <p>Abrir RDP</p>
        </div>
    </a>
</div>

        <!-- Tabela de Torrents -->
        <table class="table table-striped table-hover mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Progresso</th>
                    <th>Barrinha</th>
                </tr>
            </thead>
            <tbody id="torrents">
                <!-- Torrents serão carregados aqui -->
            </tbody>
        </table>
    </div>

    <!-- Ícones do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>
</html>
