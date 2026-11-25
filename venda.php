<?php
require __DIR__ . '/includes/auth.php';

#validacao de envio de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') { #recebe os dados do formulario e realiza a validacao
    $produto_id = (int)($_POST['produto_id'] ?? 0); #recebe o id do produto e converte para inteiro
    $qtd        = (int)($_POST['quantidade'] ?? 0); #recebe a quantidade informada e converte para inteiro

#validaocao de dados recebidos do formulario, caso algum seja invalido, uma mensagem de erro na sessao
    if ($produto_id <= 0 || $qtd <= 0) { #nao permite que seja informado um produto com id menor ou igual a 0 ou uma quantidade menor ou igual a 0
        $_SESSION['erro'] = 'Selecione um produto e uma quantidade maior que zero.';
    } else { 
        try { 
            $pdo->beginTransaction(); #inicia uma transacao no banco de dados para garantir que as operacoes sejam realizadas juntas

            $stmt = $pdo->prepare("SELECT nome, preco, quantidade FROM produtos WHERE id = ? FOR UPDATE"); #busca os dados do produto e bloqueia o registro durante a transacao
            $stmt->execute([$produto_id]); #executa a consulta passando o id do produto
            $p = $stmt->fetch(); #armazena os dados do produto encontrado

            if (!$p) { #verifica se o produto existe no banco de dados
                throw new Exception('Produto não encontrado.'); #interrompe o processo e gera uma mensagem de erro
            }
            if ($p['quantidade'] < $qtd) { #verifica se a quantidade disponivel no estoque e suficiente para a venda
                throw new Exception("Estoque insuficiente de {$p['nome']} (disponível: {$p['quantidade']})."); #informa que o estoque do produto e insuficiente
            }

            $total = $p['preco'] * $qtd; #calcula o valor total da venda multiplicando o preco pela quantidade
            if ($p['preco'] <= 0 || $total <= 0) { #verifica se o preco ou o valor total sao menores ou iguais a zero
                throw new Exception('Produto com preço zero não pode ser vendido.'); #impede a venda e gera uma mensagem de erro
            }

            $stmt = $pdo->prepare("INSERT INTO vendas (produto_id, quantidade, valor_total) VALUES (?,?,?)"); #prepara a query para registrar a venda no banco de dados
            $stmt->execute([$produto_id, $qtd, $total]); #executa a query passando o produto, a quantidade e o valor total

            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?"); #prepara a query para atualizar a quantidade disponivel no estoque
            $stmt->execute([$qtd, $produto_id]); #executa a query retirando a quantidade vendida do estoque

            $pdo->commit(); #confirma a transacao e efetiva as operacoes no banco de dados
            $_SESSION['msg'] = 'Venda registrada com sucesso.'; #armazena uma mensagem de sucesso na sessao
        } catch (Exception $e) { #captura possiveis erros durante o processamento da venda
            if ($pdo->inTransaction()) { #verifica se ainda existe uma transacao ativa
                $pdo->rollBack(); #desfaz as operacoes realizadas na transacao
            }
            $_SESSION['erro'] = $e->getMessage(); #armazena a mensagem de erro na sessao
        }
    }

    header('Location: venda.php'); #redireciona para a pagina de vendas
    exit; #encerra a execucao do codigo
}

#busca os produtos disponiveis e as ultimas 20 vendas registradas
$produtos = $pdo->query("SELECT id, nome, quantidade FROM produtos ORDER BY nome")->fetchAll(); #busca os produtos e organiza em ordem alfabetica
$vendas   = $pdo->query("SELECT v.quantidade, v.valor_total, v.data_venda, p.nome
                         FROM vendas v JOIN produtos p ON p.id = v.produto_id
                         ORDER BY v.data_venda DESC, v.id DESC LIMIT 20")->fetchAll(); #busca as ultimas 20 vendas e relaciona os dados dos produtos

require __DIR__ . '/includes/header.php'; #carrega o cabecalho da pagina
?>
<h2>Registrar venda</h2>

<!-- formulario para registrar uma nova venda -->

<form method="post">
    <label>Produto
        <select name="produto_id" required> <!-- cria uma lista para selecionar o produto, tornando o campo obrigatorio -->
            <option value="">-- selecione --</option>
            <?php foreach ($produtos as $p): ?> <!-- percorre os produtos encontrados no banco de dados -->
                <option value="<?= (int)$p['id'] ?>" <?= $p['quantidade'] <= 0 ? 'disabled' : '' ?>> <!-- define o id do produto e desabilita a opcao caso nao exista estoque -->
                    <?= e($p['nome']) ?> (estoque: <?= (int)$p['quantidade'] ?>) <!-- exibe o nome do produto e sua quantidade disponivel -->
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Quantidade
        <input type="number" name="quantidade" min="1" value="1" required> <!-- campo para informar a quantidade que sera vendida -->
    </label>
    <button type="submit">Registrar venda</button> <!-- botao para enviar o formulario e registrar a venda -->
</form>

<h3>Últimas 20 vendas</h3>
<table> <!-- tabela para exibir o historico das ultimas vendas -->
    <tr><th>Data</th><th>Produto</th><th>Quantidade</th><th>Valor total</th></tr>
    <?php if (!$vendas): ?> <!-- verifica se nao existem vendas registradas -->
        <tr><td colspan="4">Nenhuma venda registrada.</td></tr>
    <?php endif; ?>
    <?php foreach ($vendas as $v): ?> <!-- percorre as vendas encontradas para exibir seus dados na tabela -->
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?></td> <!-- exibe a data e o horario da venda no formato brasileiro -->
            <td><?= e($v['nome']) ?></td> <!-- exibe o nome do produto -->
            <td><?= (int)$v['quantidade'] ?></td> <!-- exibe a quantidade vendida -->
            <td>R$ <?= number_format($v['valor_total'], 2, ',', '.') ?></td> <!-- exibe o valor total formatado em reais -->
        </tr>
    <?php endforeach; ?>
</table>
<?php echo '</body></html>'; ?> <!-- encerra as tags body e html abertas pelo header.php -->