<?php

require_once "./models/CriptoMoneda.php";

class CriptoRepositorio
{

    public static function AgregarMoneda(CriptoMoneda $moneda)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO CRIPTOMONEDAS(PRECIO, NOMBRE, NACIONALIDAD) VALUES(:precio, :nombre, :nacionalidad)");

            $query->bindValue(":precio", $moneda->precio);
            $query->bindValue(":nombre", $moneda->nombre);
            $query->bindValue(":nacionalidad", $moneda->nacionalidad);

            $query->execute();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerMonedas()
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT ID AS id, PRECIO as precio, NOMBRE as nombre, NACIONALIDAD AS nacionalidad FROM CRIPTOMONEDAS");

            $query->execute();
            return $query->fetchAll(pdo::FETCH_CLASS, "CriptoMoneda");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerMonedasPorNacionalidad($nacionalidad)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT ID AS id, PRECIO as precio, NOMBRE as nombre, NACIONALIDAD AS nacionalidad FROM CRIPTOMONEDAS WHERE NACIONALIDAD = :nacionalidad");
            $query->bindValue(":nacionalidad", $nacionalidad);
            $query->execute();
            return $query->fetchAll(pdo::FETCH_CLASS, "CriptoMoneda");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerMonedaPorId($id)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT ID AS id, PRECIO as precio, NOMBRE as nombre, NACIONALIDAD AS nacionalidad FROM CRIPTOMONEDAS WHERE ID = :id");
            $query->bindValue(":id", $id);
            $query->execute();
            return $query->fetchObject("CriptoMoneda");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ExisteMoneda(string $monedaNombre)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM CRIPTOMONEDAS WHERE NOMBRE = ?");
        $query->bindParam(1, $monedaNombre);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }

}
