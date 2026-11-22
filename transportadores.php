<?php
require 'config.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        try {
            $pdo->prepare('DELETE FROM transportadores WHERE id = ?')->execute([$_POST['id']]);
        } catch (PDOException $e) {
            $erro = 'Este transportador já está em um frete e não pode ser excluído.';
        }
    } else {
        $d = [$_POST['nome'], $_POST['documento'], $_POST['telefone'], $_POST['veiculo']];
        if (!empty($_POST['id'])) {
            $pdo->prepare('UPDATE transportadores SET nome=?, documento=?, telefone=?, veiculo=? WHERE id=?')->execute([...$d, $_POST['id']]);
        } else {
            $pdo->prepare('INSERT INTO transportadores (nome, documento, telefone, veiculo) VALUES (?,?,?,?)')->execute($d);
        }
    }
    if (!$erro) { header('Location: transportadores.php'); exit; }
}

$edit = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM transportadores WHERE id = ?');
    $st->execute([$_GET['editar']]);
    $edit = $st->fetch();
}
$lista = $pdo->query('SELECT * FROM transportadores ORDER BY nome')->fetchAll();

topo('Transportadores');
if ($erro) echo '<div class="alert alert-danger">' . h($erro) . '</div>';
?>
<form method="post" class="card card-body shadow-sm mb-4">
  <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Nome / Razão social</label>
      <input class="form-control" name="nome" required value="<?= h($edit['nome'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">CPF ou CNPJ</label>
      <input class="form-control" name="documento" maxlength="18" value="<?= h($edit['documento'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Telefone</label>
      <input class="form-control" name="telefone" value="<?= h($edit['telefone'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Veículo (ex.: Caminhão baú)</label>
      <input class="form-control" name="veiculo" value="<?= h($edit['veiculo'] ?? '') ?>"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $edit ? 'Salvar alterações' : 'Cadastrar transportador' ?></button>
    <?php if ($edit): ?><a href="transportadores.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
  </div>
</form>

<div class="table-responsive"><table class="table table-striped bg-white align-middle">
  <thead><tr><th>ID</th><th>Nome</th><th>Documento</th><th>Telefone</th><th>Veículo</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $t): ?>
    <tr><td><?= $t['id'] ?></td><td><?= h($t['nome']) ?></td><td><?= h($t['documento']) ?></td>
      <td><?= h($t['telefone']) ?></td><td><?= h($t['veiculo']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?editar=<?= $t['id'] ?>">Editar</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Excluir este transportador?')">
          <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $t['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Excluir</button></form>
      </td></tr>
  <?php endforeach; if (!$lista): ?><tr><td colspan="6" class="text-center text-muted">Nenhum transportador cadastrado ainda.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php rodape();
