<?php
require __DIR__ . '/includes/auth.php'; 

#inicializa a variavel produto com valores padrao, caso seja um novo cadastro
$produto = ['id' => '', 'nome' => '', 'categoria' => '', 'preco' => '', 'quantidade' => 0, 'quantidade_minima' => 5];

#verifica se foi recebido um id pela URL, caso sim, busca o produto correspondente no banco de dados
if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?"); #prepara a query para buscar o produto pelo id
    $stmt->execute([(int)$_GET['id']]); #executa a query passando o id do produto como parametro
    $encontrado = $stmt->fetch(); #armazena o produto encontrado na variavel $encontrado
    if ($encontrado) { #verifica se o produto foi encontrado, caso sim, armazena seus dados na variavel $produto
        $produto = $encontrado;
    }
}

#verifica se a pagina recebeu dados do formulario, caso sim, recebe e realiza a validacao dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0); #recebe o id do produto, caso nao exista, utiliza 0 como valor padrao
    $nome       = trim($_POST['nome'] ?? ''); #recebe o nome informado e remove os espacos em branco das extremidades
    $categoria  = trim($_POST['categoria'] ?? ''); #recebe a categoria informada e remove os espacos em branco das extremidades
    $preco      = str_replace(',', '.', trim($_POST['preco'] ?? '')); #recebe o preco informado, remove os espacos e substitui a virgula por ponto
    $quantidade = (int)($_POST['quantidade'] ?? 0); #recebe a quantidade em estoque e converte o valor para inteiro
    $minimo     = (int)($_POST['quantidade_minima'] ?? 0); #recebe a quantidade minima e converte o valor para inteiro

    #verifica se os dados informados atendem as condicoes necessarias, caso alguma seja verdadeira, impede o cadastro ou a atualizacao
    if ($nome === '' || !is_numeric($preco) || $preco <= 0 || $quantidade < 0 || $minimo < 0) {
        $_SESSION['erro'] = 'Preencha o nome e um preço maior que zero. Quantidades não podem ser negativas.'; #armazena uma mensagem de erro na sessao
        $produto = [ #armazena os dados informados para manter os campos preenchidos caso ocorra algum erro
            'id' => $id, 'nome' => $nome, 'categoria' => $categoria,
            'preco' => $_POST['preco'] ?? '', 'quantidade' => $quantidade, 'quantidade_minima' => $minimo
        ];
    } else {
        #verifica se o id e maior que 0, caso sim, atualiza um produto existente
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE produtos SET nome=?, categoria=?, preco=?, quantidade=?, quantidade_minima=? WHERE id=?"); #prepara a query para atualizar os dados do produto no banco de dados
            $stmt->execute([$nome, $categoria, $preco, $quantidade, $minimo, $id]); #executa a query passando os novos dados do produto como parametros
            $_SESSION['msg'] = 'Produto atualizado.'; #informa que o produto foi atualizado
        } else {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, categoria, preco, quantidade, quantidade_minima) VALUES (?,?,?,?,?)"); #prepara a query para cadastrar um novo produto no banco de dados
            $stmt->execute([$nome, $categoria, $preco, $quantidade, $minimo]); #executa a query passando os dados do novo produto como parametros
            $_SESSION['msg'] = 'Produto cadastrado.'; #informa que o produto foi cadastrado
        }
        header('Location: index.php'); #redireciona para a pagina inicial do estoque
        exit; #encerra a execucao do codigo
    }
}

require __DIR__ . '/includes/header.php'; #carrega o cabecalho da pagina
?>
<h2><?= $produto['id'] ? 'Editar produto' : 'Novo produto' ?></h2> <!-- verifica se existe um id, caso sim, exibe editar produto, caso contrario, exibe novo produto -->

<!-- formulario de cadastro e edicao de produtos -->

<form method="post">
    <input type="hidden" name="id" value="<?= e($produto['id']) ?>"> <!-- campo oculto que armazena o id do produto para identificar qual sera atualizado -->
    <label>Nome
        <input type="text" name="nome" value="<?= e($produto['nome']) ?>" required> <!-- campo para informar o nome do produto, preenchido automaticamente caso seja uma edicao -->
    </label>
    <label>Categoria
        <input type="text" name="categoria" value="<?= e($produto['categoria']) ?>"> <!-- campo para informar a categoria do produto -->
    </label>
    <label>Preço (R$)
        <input type="text" name="preco" value="<?= e($produto['preco']) ?>" required> <!-- campo para informar o preco do produto -->
    </label>
    <label>Quantidade em estoque
        <input type="number" name="quantidade" min="0" value="<?= e($produto['quantidade']) ?>" required> <!-- campo para informar a quantidade em estoque, impedindo valores negativos pelo formulario -->
    </label>
    <label>Quantidade mínima (alerta de baixo estoque)
        <input type="number" name="quantidade_minima" min="0" value="<?= e($produto['quantidade_minima']) ?>" required> <!-- campo para definir a quantidade minima para gerar o alerta de baixo estoque -->
    </label>
    <button type="submit">Salvar</button> <!-- botao para enviar o formulario e salvar os dados do produto -->
</form>
<?php echo '</body></html>'; ?> <!-- encerra as tags body e html abertas pelo header.php -->