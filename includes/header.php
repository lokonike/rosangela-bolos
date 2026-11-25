<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rosangela Bolos - Estoque</title>
<style>
    /*estilização*/
body { font-family: Arial, sans-serif; margin: 20px; background: #fafafa; color: #222; } /*aparencia geral da pagina*/
nav { margin-bottom: 20px; } /*aparencia de navegacao*/
nav a { margin-right: 15px; color: #333; } /*aparencia de navegacao*/
table { border-collapse: collapse; width: 100%; background: #fff; } /*aparencia das tabelas*/
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }  /*aparencia das celulas cabecalho */
th { background: #eee; } /*aparencia das celulas */
.alerta { color: #b00000; font-weight: bold; } /*destaca texto em vermelh */
.msg { padding: 10px; background: #e8f5e9; border: 1px solid #a5d6a7; margin-bottom: 15px; } /*estilzia msg com fundo verde */
.erro { padding: 10px; background: #ffebee; border: 1px solid #ef9a9a; margin-bottom: 15px; } /*estilzia msg com fundo vermelho*/
form label { display: block; margin-top: 10px; } /*estilização de rótulos de formulário*/
input, select { padding: 6px; width: 300px; max-width: 100%; } /*faz ocupar uma linha propria/select estiliza as linhas da sessao */
button { padding: 7px 14px; margin-top: 12px; cursor: pointer; } /*estiliza os botao */ /*altera cursor pra ficar clicavel no elemento */
.link-btn { background: none; border: none; color: #0645ad; cursor: pointer; padding: 0; margin: 0; text-decoration: underline; font: inherit; }  /*cria aparencia de link pra botao */
    /*estilização*/
</style>
</head>
<body>
<?php if (!empty($_SESSION['usuario_id'])): ?>
<nav>
<!--menu de navegaçao-->
    <strong>Rosangela Bolos</strong> |
    <a href="index.php">Estoque</a>
    <a href="form.php">Novo produto</a>
    <a href="venda.php">Registrar venda</a>
    <a href="usuarios.php">Usuários</a>
    <a href="login.php?sair=1">Sair (<?= e($_SESSION['usuario_nome']) ?>)</a>
</nav>
<?php endif; ?> 
<?php if (!empty($_SESSION['msg'])): ?> <!--armazena e exibe msg temporaria-->
    <div class="msg"><?= e($_SESSION['msg']) ?></div> 
    <?php unset($_SESSION['msg']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['erro'])): ?> <!--mostra msg de erro-->
    <div class="erro"><?= e($_SESSION['erro']) ?></div>
    <?php unset($_SESSION['erro']); ?>
<?php endif; ?>
