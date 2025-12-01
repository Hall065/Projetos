<?php
require_once __DIR__ . '/../Config/conexao.php';

class Usuario {

    public static function cadastrar($nome, $email, $telefone, $senha) {
        $con = Conexao::getConexao();

        $sql = $con->prepare("INSERT INTO usuarios (nome, email, telefone, senha)
                              VALUES (?, ?, ?, ?)");

        return $sql->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
    }

    public static function buscarPorEmail($email) {
        $con = Conexao::getConexao();

        $sql = $con->prepare("SELECT * FROM usuarios WHERE email = ?");
        $sql->execute([$email]);

        return $sql->fetch(PDO::FETCH_ASSOC);
    }
}
