<?php
require __DIR__ . '/includes/auth.php'; 

#verifica se o formulario foi enviado pelo metodo POST e se existe uma solicitacao de exclusao
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $id = (int)$_POST['excluir']; #recebe o id do usuario que sera excluido e converte para inteiro

    #impede que o usuario exclua a propria conta
    if ($id === (int)$_SESSION['usuario_id']) {
        $_SESSION['erro'] = 'Você não pode excluir o usuário que está logado.';
    } else {
        #prepara e executa o comando para excluir o usuario do banco de dados
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['msg'] = 'Usuário excluído.';
    }
    #redireciona para a pagina de usuarios e encerra a execucao
    header('Location: usuarios.php');
    exit;
}

#consulta todos os usuarios cadastrados e organiza pelo nome
$usuarios = $pdo->query("SELECT id, nome, login, criado_em FROM usuarios ORDER BY nome")->fetchAll();

require __DIR__ . '/includes/header.php'; #carrega o cabecalho da pagina
?>
<!-- titulo da pagina -->
<h2>Usuários</h2>

<!-- link para acessar o formulario de cadastro de usuario -->
<p><a href="usuario_form.php">Incluir usuário</a></p>

<!-- tabela para apresentar os usuarios cadastrados -->
<table>
    <tr><th>Nome</th><th>Login</th><th>Criado em</th><th>Ações</th></tr>
    <?php if (!$usuarios): ?>
        <tr><td colspan="4">Nenhum usuário cadastrado.</td></tr>
    <?php endif; ?>

    <!-- percorre os usuarios encontrados e apresenta seus dados na tabela -->
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= e($u['nome']) ?></td>
            <td><?= e($u['login']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
            <td>
                <!-- link para alterar os dados do usuario selecionado -->
                <a href="usuario_form.php?id=<?= (int)$u['id'] ?>">Alterar</a> |
                <!-- formulario responsavel pela exclusao do usuario -->
                <form method="post" style="display:inline" onsubmit="return confirm('Excluir este usuário?')">
                    <input type="hidden" name="excluir" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="link-btn">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php echo '</body></html>'; ?> 
