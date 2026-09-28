<?php

header('Content-Type: application/json; charset=utf-8');

require_once("conecta.php");

try {

    // 1. Verifica o método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método de requisição inválido');
    }

    // 2. Recebe os dados do cliente
    $nome = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['phone'] ?? '');
    $endereco = trim($_POST['address'] ?? '');

    // Recebe o carrinho em JSON
    $itens = $_POST['cart'] ?? '';

    // 3. Validação dos dados
    if ($nome === '' || $email === '' || $telefone === '' || $endereco === '') {
        throw new Exception('Preencha todos os campos obrigatórios');
    }

    if ($itens === '') {
        throw new Exception('O carrinho está vazio');
    }

    // 4. Converte o JSON do carrinho para array PHP
    $cartArray = json_decode($itens, true);

    if (!is_array($cartArray) || count($cartArray) === 0) {
        throw new Exception('Carrinho inválido');
    }

    // Verifica se o JSON recebido realmente é válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON do carrinho inválido: ' . json_last_error_msg());
    }

    // 5. Inicia a transação
    mysqli_begin_transaction($conexao);

    // 6. Calcula o total no servidor
    $total = 0;

    foreach ($cartArray as $item) {
        $preco = floatval($item['price'] ?? 0);
        $quantidade = intval($item['quantity'] ?? 0);

        if ($preco < 0 || $quantidade <= 0) {
            throw new Exception('Item inválido no carrinho.');
        }

        $subtotal = $preco * $quantidade;

        $total += $subtotal;
    }

    // 7. Insere a compra
    // Agora também salvamos o JSON na coluna "itens"
    $sqlCompra = "INSERT INTO tbl_compras(nome, email, telefone, endereco, itens, total) VALUES(?, ?, ?, ?, ?, ?)";

    $stmtCompra = mysqli_prepare($conexao, $sqlCompra);

    if (!$stmtCompra) {
        throw new Exception('Erro ao preparar a compra: ' . mysqli_error($conexao));
    }

    /*
     * $itens continua sendo o JSON original recebido.
     * Como a coluna do banco é JSON, o MySQL fará a validação.
     */
    mysqli_stmt_bind_param($stmtCompra, "sssssd", $nome, $email, $telefone, $endereco, $itens, $total);

    if (!mysqli_stmt_execute($stmtCompra)) {
        throw new Exception('Erro ao salvar a compra: ' . mysqli_stmt_error($stmtCompra));
    }

    // ID da compra criada
    $compraId = mysqli_insert_id($conexao);

    mysqli_stmt_close($stmtCompra);

    // 8. Prepara o INSERT dos itens
    $sqlItem = "INSERT INTO tbl_itens_compra(compra_id, produto, preco, quantidade, subtotal) VALUES (?, ?, ?, ?, ?)";

    $stmtItem = mysqli_prepare($conexao, $sqlItem);

    if (!$stmtItem) {
        throw new Exception('Erro ao preparar os itens: ' . mysqli_error($conexao));
    }

    // 9. Insere cada produto
    foreach ($cartArray as $item) {
        $produto = trim($item['name'] ?? '');
        $preco = floatval($item['price'] ?? 0);
        $quantidade = intval($item['quantity'] ?? 0);

        if ($produto === '') {
            throw new Exception('Produto inválido');
        }

        if ($preco < 0) {
            throw new Exception('Preço inválido');
        }

        if ($quantidade <= 0) {
            throw new Exception('Quantidade inválida');
        }

        $subtotal = $preco * $quantidade;

        mysqli_stmt_bind_param($stmtItem, "isdid", $compraId, $produto, $preco, $quantidade, $subtotal);

        if (!mysqli_stmt_execute($stmtItem)) {
            throw new Exception('Erro ao salvar o item: ' . mysqli_stmt_error($stmtItem));
        }
    }

    mysqli_stmt_close($stmtItem);

    // 10. Confirma a transação
    mysqli_commit($conexao);

    // 11. Retorna sucesso
    echo json_encode(['sucesso' => true, 'mensagem' => 'Pedido enviado com sucesso!', 'id' => $compraId]);

} catch (Exception $e) {
    // Se alguma coisa der errado, desfaz tudo
    mysqli_rollback($conexao);

    http_response_code(400);

    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}

?>