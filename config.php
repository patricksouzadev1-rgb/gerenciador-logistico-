<?php
// Conexão com o banco (XAMPP: usuário root, sem senha) e layout comum das telas
$pdo = new PDO('mysql:host=localhost;dbname=logistica;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function topo($titulo) {
    $menu = ['index.php' => 'Início', 'produtos.php' => 'Produtos', 'enderecos.php' => 'Endereços',
             'transportadores.php' => 'Transportadores', 'fretes.php' => 'Fretes', 'relatorio.php' => 'Relatório'];
    $atual = basename($_SERVER['SCRIPT_NAME']);
    echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . h($titulo) . ' - Gerenciador Logístico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4"><div class="container">
      <a class="navbar-brand" href="index.php">Gerenciador Logístico</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="menu"><ul class="navbar-nav">';
    foreach ($menu as $arq => $nome) {
        echo '<li class="nav-item"><a class="nav-link' . ($arq === $atual ? ' active' : '') . '" href="' . $arq . '">' . $nome . '</a></li>';
    }
    echo '</ul></div></div></nav><div class="container pb-5"><h2 class="mb-4">' . h($titulo) . '</h2>';
}

function rodape() {
    echo '</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';
}

// Busca fretes com transportador, origem, destino e lista de produtos. Se $data for informada, filtra o dia.
function buscarFretes($pdo, $data = null) {
    $sql = "SELECT f.id, f.data_frete, t.nome AS transportador,
                   CONCAT(o.cidade, '/', o.uf) AS origem, CONCAT(d.cidade, '/', d.uf) AS destino,
                   f.valor_frete,
                   GROUP_CONCAT(CONCAT(p.nome, ' (x', fp.quantidade, ')') SEPARATOR ', ') AS produtos
            FROM fretes f
            JOIN transportadores t ON t.id = f.transportador_id
            JOIN enderecos o ON o.id = f.origem_id
            JOIN enderecos d ON d.id = f.destino_id
            LEFT JOIN frete_produtos fp ON fp.frete_id = f.id
            LEFT JOIN produtos p ON p.id = fp.produto_id"
         . ($data ? " WHERE f.data_frete = ?" : "") . " GROUP BY f.id ORDER BY f.data_frete DESC, f.id DESC";
    $st = $pdo->prepare($sql);
    $st->execute($data ? [$data] : []);
    return $st->fetchAll();
}
