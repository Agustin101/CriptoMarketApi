<?php

require_once "./models/Venta.php";
require_once "./models/Usuario.php";

class VentasRepositorio
{

    public static function AgregarVenta(Venta $venta)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO VENTAS(CANTIDAD, CLIENTE_ID, CRIPTOMONEDA_ID) VALUES(:cantidad, :cliente, :moneda)");

            $query->bindValue(":cantidad", $venta->cantidad);
            $query->bindValue(":cliente", $venta->clienteId);
            $query->bindValue(":moneda", $venta->criptoMonedaId);

            $query->execute();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerVentasPorNacionalidad($nacionalidad, $fechaInicio, $fechaFin)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT VENTAS.ID AS id, VENTAS.FECHA AS fecha, VENTAS.CANTIDAD AS cantidad, VENTAS.CLIENTE_ID as clienteId, CRIPTOMONEDAS.NOMBRE AS criptoMonedaId FROM VENTAS INNER JOIN CRIPTOMONEDAS ON VENTAS.CRIPTOMONEDA_ID = CRIPTOMONEDAS.ID WHERE CRIPTOMONEDAS.NACIONALIDAD LIKE :nacionalidad AND VENTAS.FECHA >= :fechaInicio AND VENTAS.FECHA <= :fechaFin ");
            $query->bindParam(1, $nacionalidad);
            $query->bindValue(":fechaInicio", $fechaInicio);
            $query->bindValue(":fechaFin", $fechaFin);

            $query->execute();
            return $query->fetchAll(pdo::FETCH_CLASS, "Venta");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerVentasPorNombreMoneda($moneda)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT DISTINCT USUARIOS.MAIL as mail FROM VENTAS INNER JOIN CRIPTOMONEDAS ON VENTAS.CRIPTOMONEDA_ID = CRIPTOMONEDAS.ID INNER JOIN USUARIOS ON VENTAS.CLIENTE_ID = USUARIOS.ID WHERE CRIPTOMONEDAS.NOMBRE = :nombreMoneda ");

            $query->bindValue(":nombreMoneda", $moneda);

            $query->execute();
            return $query->fetchAll(pdo::FETCH_ASSOC);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
