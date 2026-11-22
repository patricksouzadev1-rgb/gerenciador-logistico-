<?php
require 'config.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        try {
            $pdo->prepare('DELETE FROM enderecos WHERE id = ?')->execute([$_POST['id']]);
        } catch (PDOException $e) {
            $erro = 'Este endereço já está em um frete e não pode ser excluído.';
        }
    } else {
        $d = [$_POST['apelido'], $_POST['cep'], $_POST['logradouro'], $_POST['numero'], $_POST['bairro'], $_POST['cidade'], strtoupper($_POST['uf'])];
        if (!empty($_POST['id'])) {
            $pdo->prepare('UPDATE enderecos SET apelido=?, cep=?, logradouro=?, numero=?, bairro=?, cidade=?, uf=? WHERE id=?')->execute([...$d, $_POST['id']]);
        } else {
            $pdo->prepare('INSERT INTO enderecos (apelido, cep, logradouro, numero, bairro, cidade, uf) VALUES (?,?,?,?,?,?,?)')->execute($d);
        }
    }
    if (!$erro) { header('Location: enderecos.php'); exit; }
}

$edit = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM enderecos WHERE id = ?');
    $st->execute([$_GET['editar']]);
    $edit = $st->fetch();
}
$lista = $pdo->query('SELECT * FROM enderecos ORDER BY apelido')->fetchAll();

topo('Endereços');
if ($erro) echo '<div class="alert alert-danger">' . h($erro) . '</div>';
?>
<form method="post" class="card card-body shadow-sm mb-4">
  <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Nome do local (ex.: Depósito Central)</label>
      <input class="form-control" name="apelido" required value="<?= h($edit['apelido'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">CEP</label>
      <input class="form-control" name="cep" maxlength="9" value="<?= h($edit['cep'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Rua</label>
      <input class="form-control" name="logradouro" required value="<?= h($edit['logradouro'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Número</label>
      <input class="form-control" name="numero" value="<?= h($edit['numero'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Bairro</label>
      <input class="form-control" name="bairro" value="<?= h($edit['bairro'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Cidade</label>
      <input class="form-control" name="cidade" required value="<?= h($edit['cidade'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">UF</label>
      <input class="form-control" name="uf" maxlength="2" required value="<?= h($edit['uf'] ?? '') ?>"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $edit ? 'Salvar alterações' : 'Cadastrar endereço' ?></button>
    <?php if ($edit): ?><a href="enderecos.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
  </div>
</form>

<div class="table-responsive"><table class="table table-striped bg-white align-middle">
  <thead><tr><th>ID</th><th>Local</th><th>Endereço</th><th>Cidade/UF</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $e): ?>
    <tr><td><?= $e['id'] ?></td><td><?= h($e['apelido']) ?></td>
      <td><?= h($e['logradouro']) ?>, <?= h($e['numero']) ?> - <?= h($e['bairro']) ?></td>
      <td><?= h($e['cidade']) ?>/<?= h($e['uf']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?editar=<?= $e['id'] ?>">Editar</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Excluir este endereço?')">
          <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $e['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Excluir</button></form>
      </td></tr>
  <?php endforeach; if (!$lista): ?><tr><td colspan="5" class="text-center text-muted">Nenhum endereço cadastrado ainda. Cadastre uma origem e um destino.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php rodape();
