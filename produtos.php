<?php
require 'config.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        try {
            $pdo->prepare('DELETE FROM produtos WHERE id = ?')->execute([$_POST['id']]);
        } catch (PDOException $e) {
            $erro = 'Este produto já está em um frete e não pode ser excluído.';
        }
    } else {
        $d = [$_POST['nome'], $_POST['descricao'], $_POST['peso'], $_POST['valor']];
        if (!empty($_POST['id'])) {
            $pdo->prepare('UPDATE produtos SET nome=?, descricao=?, peso=?, valor=? WHERE id=?')->execute([...$d, $_POST['id']]);
        } else {
            $pdo->prepare('INSERT INTO produtos (nome, descricao, peso, valor) VALUES (?,?,?,?)')->execute($d);
        }
    }
    if (!$erro) { header('Location: produtos.php'); exit; }
}

$edit = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $st->execute([$_GET['editar']]);
    $edit = $st->fetch();
}
$lista = $pdo->query('SELECT * FROM produtos ORDER BY nome')->fetchAll();

topo('Produtos');
if ($erro) echo '<div class="alert alert-danger">' . h($erro) . '</div>';
?>
<form method="post" class="card card-body shadow-sm mb-4">
  <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Nome</label>
      <input class="form-control" name="nome" required value="<?= h($edit['nome'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Descrição</label>
      <input class="form-control" name="descricao" value="<?= h($edit['descricao'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Peso (kg)</label>
      <input class="form-control" type="number" step="0.01" min="0" name="peso" required value="<?= h($edit['peso'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Valor (R$)</label>
      <input class="form-control" type="number" step="0.01" min="0" name="valor" required value="<?= h($edit['valor'] ?? '') ?>"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $edit ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
    <?php if ($edit): ?><a href="produtos.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
  </div>
</form>

<div class="table-responsive"><table class="table table-striped bg-white align-middle">
  <thead><tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Peso</th><th>Valor</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $p): ?>
    <tr><td><?= $p['id'] ?></td><td><?= h($p['nome']) ?></td><td><?= h($p['descricao']) ?></td>
      <td><?= number_format($p['peso'], 2, ',', '.') ?> kg</td><td>R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?editar=<?= $p['id'] ?>">Editar</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Excluir este produto?')">
          <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Excluir</button></form>
      </td></tr>
  <?php endforeach; if (!$lista): ?><tr><td colspan="6" class="text-center text-muted">Nenhum produto cadastrado ainda.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php rodape();
