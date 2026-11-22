<?php
require 'config.php';
topo('Início');
$tabelas = ['produtos' => 'Produtos', 'enderecos' => 'Endereços', 'transportadores' => 'Transportadores', 'fretes' => 'Fretes'];
echo '<div class="row g-3">';
foreach ($tabelas as $t => $nome) {
    $total = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    echo '<div class="col-6 col-md-3"><a href="' . $t . '.php" class="text-decoration-none">
          <div class="card text-center shadow-sm"><div class="card-body"><div class="display-6">' . $total . '</div>
          <div class="text-muted">' . $nome . '</div></div></div></a></div>';
}
echo '</div><p class="mt-4 text-muted">Cadastre produtos, endereços e transportadores; depois gere um frete e baixe o relatório do dia.</p>';
rodape();
