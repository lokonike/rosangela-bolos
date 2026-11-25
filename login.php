<?php
require __DIR__ . '/includes/config.php'; #carregar as configuracoes

#condicao de logout do usuario 
if (isset($_GET['sair'])) { 
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['msg'] = 'Você saiu do sistema.';
    header('Location: login.php');
    exit;
}

#verifica se o usuario ja ta logado, caso sim redireciona para a pagina inicial
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

#verifricar se existe algum usuario cadastrado, caso nao exista, o sistema permite criar o primeiro usuario
$total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$erro = '';

#verifica se a pagina receu dados do formulario, caso sim, realiza a validacao e o login do usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Primeiro acesso: só funciona se ainda não existir nenhum usuário
    if ($total === 0 && ($_POST['acao'] ?? '') === 'primeiro') { #verifica se o total de usuarios cadastrados é 0 e se a acao do formulario é 'primeiro', caso sim, cria o primeiro usuario
        $nome      = trim($_POST['nome'] ?? ''); #recebe o nome informado
        $login     = trim($_POST['login'] ?? ''); #recebe o login informado
        $senha     = $_POST['senha'] ?? ''; #recebe a senha informada
        $confirmar = $_POST['confirmar'] ?? ''; #recebe a confimacao da senha

        if ($nome === '' || $login === '') { #verifica se o nome ou login estao vazios, caso sim, exibe msg de erro
            $erro = 'Preencha nome e login.';
        } elseif (strlen($senha) < 6) { #verifica se a senha tem menos de 6 caracteres, caso sim, exibe msg de erro
            $erro = 'A senha precisa ter pelo menos 6 caracteres.';
        } elseif ($senha !== $confirmar) { #verifica se a senha e a confirmacao da senha sao diferentes, caso sim, exibe msg de erro
            $erro = 'As senhas não conferem.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, login, senha_hash) VALUES (?,?,?)"); #prepara a query para inserir o usuario no banco de dados
            $stmt->execute([$nome, $login, password_hash($senha, PASSWORD_DEFAULT)]); #executa a query para inserir o usuario no banco de dados, com a senha criptografada
            $_SESSION['msg'] = 'Usuário criado. Agora faça o login.';  #informa que usuario foi criado
            header('Location: login.php'); #redireciona para a pagina de login
            exit;
        }

    // Login normal/autenticacao de usuarios que ja tem cadastro
    } elseif ($total > 0) {
        $login = trim($_POST['login'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $stmt = $pdo->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE login = ?");
        $stmt->execute([$login]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            header('Location: index.php');
            exit;
        }

        $erro = 'Usuário ou senha inválidos.';
    }
}
require __DIR__ . '/includes/header.php';
#exibicao de mensagem de erro
?>
<?php if ($erro): ?>
    <div class="erro"><?= e($erro) ?></div>
<?php endif; ?>

<!-- formulario de primeiro acesso-->
<?php if ($total === 0): ?>
    <h2>Primeiro acesso</h2>
    <p>Nenhum usuário cadastrado. Crie o primeiro usuário do sistema.</p>
    <form method="post">
        <input type="hidden" name="acao" value="primeiro">
        <label>Nome <input type="text" name="nome" required></label>
        <label>Login <input type="text" name="login" required></label>
        <label>Senha (mínimo 6 caracteres) <input type="password" name="senha" required></label>
        <label>Confirmar senha <input type="password" name="confirmar" required></label>
        <button type="submit">Criar usuário</button>
    </form>
<?php else: ?>
    <h2>Entrar</h2>
    <form method="post">
        <label>Login <input type="text" name="login" required autofocus></label>
        <label>Senha <input type="password" name="senha" required></label>
        <button type="submit">Entrar</button>
    </form>
<?php endif; ?>
<?php echo '</body></html>'; ?>
