<?php

// Dados para conexão
$servidor = "localhost";
$dbnome = "bd_ateliweb";
$usuario = "root";
$senha = "";

// Conectando
$conexao = mysqli_connect($servidor, $usuario, $senha, $dbnome);

if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");

?>