<?php
require __DIR__ . '/includes/auth.php';

#exclusao de produtos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) { #para verificar se a requisicao do formulario foi do tipo POST e se o campo excluir foi enviado, caso sim, realiza a exclusao do produto
    try { 
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?"); #prepara a query para excluir o produto do banco de dados
        $stmt->execute([(int)$_POST['excluir']]); #executa a query para excluir o produto do banco de dados, passando o id do produto como parametro
        $_SESSION['msg'] = 'Produto excluído.'; #informa que o produto foi excluido
    } catch (PDOException $e) {                 #captura erros relacionados ao banco de dados durante a exclusao
        $_SESSION['erro'] = 'Não é possível excluir: este produto já possui vendas registradas.'; #informa que o produto nao pode ser excluido pois ja possui vendas registradas
    }
    header('Location: index.php'); #redireciona para a pagina index.php
    exit;
}

#busca de produtos
$busca = trim($_GET['busca'] ?? '');

if ($busca !== '') {                #verifica se o campo de busca nao esta vazio, caso nao esteja, realiza a busca no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE nome LIKE ? OR categoria LIKE ? ORDER BY nome");
   $stmt->execute(["%$busca%", "%$busca%"]); #executa a query para buscar os produtos no banco de dados, passando o valor do campo de busca como parametro
} else {                                     #caso o campo de busca esteja vazio, busca todos os produtos no banco de dados
    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY nome");  #executa a query para buscar todos os produtos no banco de dados
}
$produtos = $stmt->fetchAll();              #armazena os produtos encontrados na variavel $produtos

require __DIR__ . '/includes/header.php';
?>
<h2>Estoque</h2>

<!-- formulario de busca -->

<form method="get">
    <input type="text" name="busca" placeholder="Buscar por nome ou categoria" value="<?= e($busca) ?>">
    <button type="submit">Buscar</button>
</form>

<!-- criacao de tabela de produtos --> 

<table>
    <tr>
        <th>Produto</th><th>Categoria</th><th>Preço</th>
        <th>Quantidade</th><th>Mínimo</th><th>Situação</th><th>Ações</th>
    </tr>

<!-- verificacao de produtos encontrados -->

    <?php if (!$produtos): ?>
        <tr><td colspan="7">Nenhum produto encontrado.</td></tr>
    <?php endif; ?>
    <?php foreach ($produtos as $p): $baixo = $p['quantidade'] <= $p['quantidade_minima']; ?>
        <tr>
            <td><?= e($p['nome']) ?></td>
            <td><?= e($p['categoria']) ?></td>
            <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
            <td><?= (int)$p['quantidade'] ?></td>
            <td><?= (int)$p['quantidade_minima'] ?></td>
            <td><?= $baixo ? '<span class="alerta">Baixo estoque</span>' : 'OK' ?></td>
            <td>
                <a href="form.php?id=<?= (int)$p['id'] ?>">Alterar</a> |
                <form method="post" style="display:inline" onsubmit="return confirm('Excluir este produto?')">
                    <input type="hidden" name="excluir" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="link-btn">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php echo '</body></html>'; ?>
