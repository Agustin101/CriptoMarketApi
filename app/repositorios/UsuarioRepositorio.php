<?php

require_once "./db/AccesoDatos.php";

class UsuarioRepositorio
{
    public static function ObtenerUsuario($mail)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ID, CLAVE, MAIL, TIPO, ACTIVO FROM USUARIOS WHERE MAIL = :mail");
        $query->bindValue(':mail', $mail, PDO::PARAM_STR);
        $query->execute();
    
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function ExisteUsuario(string $mail)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM USUARIOS WHERE MAIL = ?");
        $query->bindParam(1, $mail);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }
}
