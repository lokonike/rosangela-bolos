<?php
require __DIR__ . '/includes/auth.php'; 

#inicializa a variavel usuario com valores padrao, caso seja um novo cadastro
$usuario = ['id' => '', 'nome' => '', 'login' => ''];

#verifica se foi recebido um id pela URL, caso sim, busca o usuario correspondente no banco de dados
if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT id, nome, login FROM usuarios WHERE id = ?"); #prepara a query para buscar os dados do usuario pelo id
    $stmt->execute([(int)$_GET['id']]); #executa a query passando o id do usuario como parametro
    $encontrado = $stmt->fetch(); #armazena o usuario encontrado na variavel $encontrado
    if ($encontrado) { #verifica se o usuario foi encontrado, caso sim, armazena seus dados na variavel $usuario
        $usuario = $encontrado;
    }
}

#verifica se a pagina recebeu dados do formulario, caso sim, recebe e realiza a validacao dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)($_POST['id'] ?? 0); #recebe o id do usuario, caso nao exista, utiliza 0 como valor padrao
    $nome      = trim($_POST['nome'] ?? ''); #recebe o nome informado e remove os espacos em branco das extremidades
    $login     = trim($_POST['login'] ?? ''); #recebe o login informado e remove os espacos em branco das extremidades
    $senha     = $_POST['senha'] ?? ''; #recebe a senha informada, caso nao exista, utiliza uma string vazia
    $confirmar = $_POST['confirmar'] ?? ''; #recebe a confirmacao da senha, caso nao exista, utiliza uma string vazia
    $erro      = ''; #inicializa a variavel de erro vazia

    #verifica se o nome ou o login estao vazios, caso sim, armazena uma mensagem de erro
    if ($nome === '' || $login === '') {
        $erro = 'Preencha nome e login.';
    } elseif ($id === 0 && strlen($senha) < 6) { #verifica se e um novo usuario e se a senha possui menos de 6 caracteres
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif ($senha !== '' && strlen($senha) < 6) { #verifica se foi informada uma nova senha e se ela possui menos de 6 caracteres
        $erro = 'A nova senha precisa ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar) { #verifica se a senha e a confirmacao sao diferentes
        $erro = 'As senhas não conferem.';
    }

    #verifica se nao existe nenhum erro, caso sim, tenta realizar o cadastro ou a atualizacao do usuario
    if ($erro === '') {
        try {
            #verifica se o id e maior que 0, caso sim, atualiza um usuario existente
            if ($id > 0) {
                #verifica se foi informada uma nova senha, caso sim, atualiza tambem a senha do usuario
                if ($senha !== '') {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, login=?, senha_hash=? WHERE id=?"); #prepara a query para atualizar os dados do usuario e sua senha
                    $stmt->execute([$nome, $login, password_hash($senha, PASSWORD_DEFAULT), $id]); #executa a query passando os dados e a senha protegida por hash
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, login=? WHERE id=?"); #prepara a query para atualizar o nome e o login sem alterar a senha
                    $stmt->execute([$nome, $login, $id]); #executa a query passando o nome, login e id do usuario
                }
                $_SESSION['msg'] = 'Usuário alterado com sucesso.'; #armazena uma mensagem de sucesso na sessao
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, login, senha_hash) VALUES (?,?,?)"); #prepara a query para cadastrar um novo usuario
                $stmt->execute([$nome, $login, password_hash($senha, PASSWORD_DEFAULT)]); #executa a query passando os dados e a senha protegida por hash
                $_SESSION['msg'] = 'Usuário incluído com sucesso.'; #armazena uma mensagem de sucesso na sessao
            }
            header('Location: usuarios.php'); #redireciona para a lista de usuarios
            exit; #encerra a execucao do codigo
        } catch (PDOException $e) { #captura erros relacionados a operacao com o banco de dados
            $erro = 'Esse login já existe.'; #armazena uma mensagem informando que o login ja esta cadastrado
        }
    }

    $usuario = ['id' => $id, 'nome' => $nome, 'login' => $login]; #armazena os dados informados para manter os campos preenchidos caso ocorra algum erro
    $_SESSION['erro'] = $erro; #armazena a mensagem de erro na sessao
    header('Location: usuario_form.php' . ($id ? '?id=' . $id : '')); #redireciona para o formulario, mantendo o id caso seja uma edicao
    exit; #encerra a execucao do codigo
}

require __DIR__ . '/includes/header.php'; #carrega o cabecalho da pagina
?>
<h2><?= $usuario['id'] ? 'Alterar usuário' : 'Incluir usuário' ?></h2> <!-- verifica se existe um id, caso sim, exibe alterar usuario, caso contrario, exibe incluir usuario -->

<!-- formulario de cadastro e edicao de usuarios -->

<form method="post">
    <input type="hidden" name="id" value="<?= e($usuario['id']) ?>"> <!-- campo oculto que armazena o id do usuario para identificar qual sera atualizado -->
    <label>Nome
        <input type="text" name="nome" value="<?= e($usuario['nome']) ?>" required> <!-- campo para informar o nome do usuario, preenchido automaticamente caso seja uma edicao -->
    </label>
    <label>Login
        <input type="text" name="login" value="<?= e($usuario['login']) ?>" required> <!-- campo para informar o login do usuario -->
    </label>
    <label><?= $usuario['id'] ? 'Nova senha (deixe em branco para manter)' : 'Senha (mínimo 6 caracteres)' ?> <!-- verifica se existe um id, caso sim, solicita uma nova senha opcional, caso contrario, solicita uma senha obrigatoria -->
        <input type="password" name="senha" <?= $usuario['id'] ? '' : 'required' ?>> <!-- campo para informar a senha do usuario, sendo obrigatorio apenas no cadastro -->
    </label>
    <label>Confirmar senha
        <input type="password" name="confirmar" <?= $usuario['id'] ? '' : 'required' ?>> <!-- campo para confirmar a senha informada, sendo obrigatorio apenas no cadastro -->
    </label>
    <button type="submit">Salvar</button> <!-- botao para enviar o formulario e salvar os dados do usuario -->
</form>
<p><a href="usuarios.php">Voltar para a lista</a></p> <!-- link para voltar a lista de usuarios -->
<?php echo '</body></html>'; ?> <!-- encerra as tags body e html abertas pelo header.php -->