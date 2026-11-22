<?php
require 'config.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        $pdo->prepare('DELETE FROM fretes WHERE id = ?')->execute([$_POST['id']]); // itens saem por ON DELETE CASCADE
        header('Location: fretes.php'); exit;
    }
    $produtos = array_filter($_POST['produto'] ?? []);
    if ($_POST['origem_id'] === $_POST['destino_id']) {
        $erro = 'Origem e destino devem ser endereços diferentes.';
    } elseif (!$produtos) {
        $erro = 'Adicione pelo menos um produto ao frete.';
    } else {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO fretes (transportador_id, origem_id, destino_id, data_frete, valor_frete, observacao) VALUES (?,?,?,?,?,?)')
            ->execute([$_POST['transportador_id'], $_POST['origem_id'], $_POST['destino_id'], $_POST['data_frete'], $_POST['valor_frete'], $_POST['observacao']]);
        $freteId = $pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO frete_produtos (frete_id, produto_id, quantidade) VALUES (?,?,?)');
        foreach ($_POST['produto'] as $i => $prodId) {
            if ($prodId) $ins->execute([$freteId, $prodId, max(1, (int)$_POST['qtd'][$i])]);
        }
        $pdo->commit();
        header('Location: fretes.php'); exit;
    }
}

$transp = $pdo->query('SELECT id, nome FROM transportadores ORDER BY nome')->fetchAll();
$ends   = $pdo->query('SELECT id, apelido, cidade, uf FROM enderecos ORDER BY apelido')->fetchAll();
$prods  = $pdo->query('SELECT id, nome FROM produtos ORDER BY nome')->fetchAll();
$fretes = buscarFretes($pdo);

$optProd = '<option value="">Selecione o produto</option>';
foreach ($prods as $p) $optProd .= '<option value="' . $p['id'] . '">' . h($p['nome']) . '</option>';

function opcoesEnd($ends) {
    $o = '<option value="">Selecione</option>';
    foreach ($ends as $e) $o .= '<option value="' . $e['id'] . '">' . h($e['apelido'] . ' - ' . $e['cidade'] . '/' . $e['uf']) . '</option>';
    return $o;
}

topo('Fretes');
if ($erro) echo '<div class="alert alert-danger">' . h($erro) . '</div>';
if (!$transp || count($ends) < 2 || !$prods) {
    echo '<div class="alert alert-warning">Para gerar um frete, cadastre antes ao menos 1 produto, 1 transportador e 2 endereços (origem e destino).</div>';
}
?>
<form method="post" class="card card-body shadow-sm mb-4">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Transportador</label>
      <select class="form-select" name="transportador_id" required>
        <option value="">Selecione</option>
        <?php foreach ($transp as $t): ?><option value="<?= $t['id'] ?>"><?= h($t['nome']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Origem</label>
      <select class="form-select" name="origem_id" required><?= opcoesEnd($ends) ?></select></div>
    <div class="col-md-4"><label class="form-label">Destino</label>
      <select class="form-select" name="destino_id" required><?= opcoesEnd($ends) ?></select></div>
    <div class="col-md-3"><label class="form-label">Data do frete</label>
      <input class="form-control" type="date" name="data_frete" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-3"><label class="form-label">Valor do frete (R$)</label>
      <input class="form-control" type="number" step="0.01" min="0" name="valor_frete" required></div>
    <div class="col-md-6"><label class="form-label">Observação</label>
      <input class="form-control" name="observacao"></div>
  </div>

  <h5 class="mt-4">Produtos transportados</h5>
  <div id="itens"></div>
  <div class="mt-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="addItem">+ Adicionar produto</button></div>
  <div class="mt-3"><button class="btn btn-primary">Gerar frete</button></div>
</form>

<script>
// JavaScript: inclui e remove linhas de produto dinamicamente
const opcoes = <?= json_encode($optProd) ?>;
const itens = document.getElementById('itens');

function novaLinha() {
  const linha = document.createElement('div');
  linha.className = 'row g-2 mb-2 align-items-end';
  linha.innerHTML =
    '<div class="col-7 col-md-6"><select class="form-select" name="produto[]">' + opcoes + '</select></div>' +
    '<div class="col-3 col-md-2"><input class="form-control" type="number" min="1" value="1" name="qtd[]" aria-label="Quantidade"></div>' +
    '<div class="col-2"><button type="button" class="btn btn-outline-danger btn-sm remover">Remover</button></div>';
  itens.appendChild(linha);
}
document.getElementById('addItem').addEventListener('click', novaLinha);
itens.addEventListener('click', function (ev) {
  if (ev.target.classList.contains('remover') && itens.children.length > 1) ev.target.closest('.row').remove();
});
novaLinha();
</script>

<h5>Fretes gerados</h5>
<div class="table-responsive"><table class="table table-striped bg-white align-middle">
  <thead><tr><th>Nº</th><th>Data</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Produtos</th><th>Valor</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($fretes as $f): ?>
    <tr><td><?= $f['id'] ?></td><td><?= date('d/m/Y', strtotime($f['data_frete'])) ?></td><td><?= h($f['transportador']) ?></td>
      <td><?= h($f['origem']) ?></td><td><?= h($f['destino']) ?></td><td><?= h($f['produtos']) ?></td>
      <td>R$ <?= number_format($f['valor_frete'], 2, ',', '.') ?></td>
      <td><form method="post" onsubmit="return confirm('Excluir este frete?')">
        <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $f['id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Excluir</button></form></td></tr>
  <?php endforeach; if (!$fretes): ?><tr><td colspan="8" class="text-center text-muted">Nenhum frete gerado ainda.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php rodape();
