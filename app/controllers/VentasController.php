<?php

require_once './repositorios/VentasRepositorio.php';
require_once './repositorios/CriptoRepositorio.php';

require_once "./models/Venta.php";

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response as Response;

class VentasController
{
    public function AgregarVenta(IRequest $req, IResponse $res)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');

        $parametros = $req->getParsedBody();
        $archivos = $req->getUploadedFiles();
        $imagen = $archivos["foto"];
        $header = $req->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = token::ObtenerData($token);
        $venta = new Venta();
        $venta->cantidad = $parametros["cantidad"];
        $venta->criptoMonedaId = $parametros["idMoneda"];
        $venta->clienteId = $data->id;

        if ($imagen->getError() !== UPLOAD_ERR_OK) {
            $res->getBody()->write(json_encode(array("mensaje" => "Hubo un error al obtener la imagen.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (CriptoRepositorio::ObtenerMonedaPorId($venta->criptoMonedaId) == false) {
            $res->getBody()->write(json_encode(array("mensaje" => "El id no corresponde a ninguna criptomoneda registrada.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            VentasRepositorio::AgregarVenta($venta);
            $criptoMoneda = CriptoRepositorio::ObtenerMonedaPorId($venta->criptoMonedaId);
            $dateTime = new DateTime();
            $fecha = $dateTime->format('Y-m-d_H-i-s');

            $this->GuardarFoto("C:\\xampp\\htdocs\\CriptoMarket\\app\\FotosCripto2023", $imagen, ($criptoMoneda->nombre . $data->mail . $fecha));
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "Venta exitosa.")));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function GuardarFoto(string $ubicacion, $foto, $nombre)
    {
        $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
        $nombreArchivo = $nombre . "." . $extension;
        $foto->moveTo($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo);
        return $nombreArchivo;
    }

    public function ObtenerVentasNacionalidad(IRequest $req, IResponse $res, array $args)
    {
        $nacionalidad = $args["nacionalidad"];
        $params = $req->getQueryParams();
        $fechaInicio = $params["fechaInicio"];
        $fechaFin = $params["fechaFin"];

        $fechaInicioFormateada = new DateTime($fechaInicio);
        $fechaInicioFormateada->setTime(0, 0, 0);
        $fechaInicioFormateada = $fechaInicioFormateada->format("Y-m-d H:i:s");

        $fechaFinFormateada = new DateTime($fechaFin);
        $fechaFinFormateada->setTime(23, 59, 59);
        $fechaFinFormateada = $fechaFinFormateada->format("Y-m-d H:i:s");

        try {
            $ventas = VentasRepositorio::ObtenerVentasPorNacionalidad($nacionalidad, $fechaInicioFormateada, $fechaFinFormateada);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("Ventas" => $ventas)));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ObtenerVentasNombreMoneda(IRequest $req, IResponse $res, array $args)
    {
        $nombre = $args["nombre"];

        try {
            $ventas = VentasRepositorio::ObtenerVentasPorNombreMoneda($nombre);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("Usuarios" => $ventas)));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

}
