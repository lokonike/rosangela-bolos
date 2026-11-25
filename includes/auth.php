<?php

#importa as configuracoes do sistema e cria uma funcao de controle de acesso
require_once __DIR__ . '/config.php';

function exigir_login() {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

exigir_login();
