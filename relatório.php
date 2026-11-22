<?php
require 'config.php';
$data = $_GET['data'] ?? date('Y-m-d');
$fretes = buscarFretes($pdo, $data);

// Download do relatório em CSV (abre direto no Excel)
if (isset($_GET['baixar'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="relatorio_fretes_' . $data . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM para acentos no Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Frete', 'Data', 'Transportador', 'Origem', 'Destino', 'Produtos', 'Valor (R$)'], ';');
    $total = 0;
    foreach ($fretes as $f) {
        fputcsv($out, [$f['id'], date('d/m/Y', strtotime($f['data_frete'])), $f['transportador'], $f['origem'],
                       $f['destino'], $f['produtos'], number_format($f['valor_frete'], 2, ',', '')], ';');
        $total += $f['valor_frete'];
    }
    fputcsv($out, ['', '', '', '', '', 'Total', number_format($total, 2, ',', '')], ';');
    fclose($out);
    exit;
}

topo('Relatório de fretes');
$total = array_sum(array_column($fretes, 'valor_frete'));
?>
<form method="get" class="row g-2 mb-4 align-items-end">
  <div class="col-auto"><label class="form-label">Dia</label>
    <input class="form-control" type="date" name="data" value="<?= h($data) ?>"></div>
  <div class="col-auto"><button class="btn btn-primary">Filtrar</button></div>
  <div class="col-auto"><a class="btn btn-success<?= $fretes ? '' : ' disabled' ?>" href="?data=<?= h($data) ?>&baixar=1">Baixar relatório (CSV)</a></div>
</form>

<div class="table-responsive"><table class="table table-striped bg-white align-middle">
  <thead><tr><th>Nº</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Produtos</th><th>Valor</th></tr></thead>
  <tbody>
  <?php foreach ($fretes as $f): ?>
    <tr><td><?= $f['id'] ?></td><td><?= h($f['transportador']) ?></td><td><?= h($f['origem']) ?></td>
      <td><?= h($f['destino']) ?></td><td><?= h($f['produtos']) ?></td><td>R$ <?= number_format($f['valor_frete'], 2, ',', '.') ?></td></tr>
  <?php endforeach; if (!$fretes): ?><tr><td colspan="6" class="text-center text-muted">Nenhum frete em <?= date('d/m/Y', strtotime($data)) ?>. Escolha outro dia.</td></tr><?php endif; ?>
  </tbody>
  <?php if ($fretes): ?><tfoot><tr><th colspan="5" class="text-end">Total do dia</th><th>R$ <?= number_format($total, 2, ',', '.') ?></th></tr></tfoot><?php endif; ?>
</table></div>
<?php rodape();
