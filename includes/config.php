<?php
#verificar se existe uma sessao ativa (iniciar caso nao haja)
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

#definição para conexao ao banco dedados
$host       = 'localhost';          #endereco do banco
$db         = 'rosangela_bolos';    #nome do banco
$usuario    = 'root';               #usuario de acesso ao banco
$senha      = '';                   #senha do usuario
$dsn        = "mysql:host=$host;dbname=$db;charset=utf8mb4"; #string para conexao

$opcoes = [
    PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION, #determina erros na conenxao PDO sejam excessoes
    PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,       #determina que resultados de consulta seja retornado como arrays 
    PDO::ATTR_EMULATE_PREPARES      => false,                  #desativa simulacao de consultas do PDO
];

#try tenta realizar conexao e caso de erro, catch apresenta mesnagem de erro
try {
    $pdo =new PDO($dsn, $usuario, $senha, $opcoes);
} catch (PDOException $e) {
    die("Erro ao conectar no banco: " . $e->getMessage());
}

#tratar texto antes de exibir no html, protecao de HTML injection
function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}
