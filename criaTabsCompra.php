<?php

$dbnome = "bd_ateliweb";
$conexao = mysqli_connect('localhost', 'root', '') or die("Erro de conexão");

mysqli_query($conexao, "CREATE DATABASE IF NOT EXISTS $dbnome");
mysqli_query($conexao, "USE $dbnome");

/* Primeiro apaga-se a tabela FILHA. Ela possui uma FK apontando para tbl_compras */
mysqli_query($conexao, "DROP TABLE IF EXISTS tbl_itens_compra");

/* Depois podemos apagar a tabela PAI */
mysqli_query($conexao, "DROP TABLE IF EXISTS tbl_compras");


/* Cria tbl_compras*/

$sqlCompras = "CREATE TABLE tbl_compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    endereco TEXT NOT NULL,
    itens JSON NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conexao, $sqlCompras)) {
    echo "Tabela tbl_compras criada com sucesso<br>";
} else {
    echo "Erro ao criar tbl_compras: " . mysqli_error($conexao) . "<br>";
}


/* CRIA tbl_itens_compra */

$sqlItens = "CREATE TABLE tbl_itens_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    produto VARCHAR(150) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_itens_compra
        FOREIGN KEY (compra_id)
        REFERENCES tbl_compras(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
)";

if (mysqli_query($conexao, $sqlItens)) {
    echo "Tabela tbl_itens_compra criada com sucesso<br>";
} else {
    echo "Erro ao criar tbl_itens_compra: " . mysqli_error($conexao) . "<br>";
}

mysqli_close($conexao);

?>