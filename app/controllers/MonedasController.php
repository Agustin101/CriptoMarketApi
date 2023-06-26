<?php

require_once './util/Jwt.php';
require_once './models/Criptomoneda.php';
require_once './repositorios/CriptoRepositorio.php';

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response as Response;

class MonedasController
{

    public function AgregarCripto(IRequest $req, IResponse $res)
    {
        $parametros = $req->getParsedBody();
        $moneda = new CriptoMoneda();
        $moneda->precio = $parametros["precio"];
        $moneda->nombre = $parametros["nombre"];
        $moneda->nacionalidad = $parametros["nacionalidad"];
        $archivos = $req->getUploadedFiles();
        $imagen = $archivos["foto"];

        if ($imagen->getError() !== UPLOAD_ERR_OK) {
            $res->getBody()->write(json_encode(array("mensaje" => "Hubo un error al obtener la imagen.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (CriptoRepositorio::ExisteMoneda($moneda->nombre)) {
            $res->getBody()->write(json_encode(array("mensaje" => "Ya existe una moneda con ese nombre.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            CriptoRepositorio::AgregarMoneda($moneda);
            $this->GuardarFoto("C:\\xampp\htdocs\CriptoMarket\app\FotosMonedas", $imagen, $moneda->nombre);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "Moneda creada con exito")));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function GuardarFoto(string $ubicacion, $foto, $monedaNombre)
    {
        $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
        $nombreArchivo = "foto_moneda_" . $monedaNombre . "." . $extension;
        $foto->moveTo($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo);
        return $nombreArchivo;
    }

    public function ObtenerCriptos(IRequest $req, IResponse $res)
    {

        try {
            $monedas = CriptoRepositorio::ObtenerMonedas();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("Monedas" => $monedas)));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ObtenerCriptosnacionalidad(IRequest $req, IResponse $res, array $args)
    {
        $params = $req->getQueryParams();
        $nacionalidad = $params["nacionalidad"];

        try {
            $monedas = CriptoRepositorio::ObtenerMonedasPorNacionalidad($nacionalidad);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("Monedas" => $monedas)));
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ObtenerCriptoPorId(IRequest $req, IResponse $res, array $args)
    {
        $id = $args["id"];

        try {
            $monedas = CriptoRepositorio::ObtenerMonedaPorId($id);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($monedas == false) {
            $res->getBody()->write(json_encode(array("Monedas" => "No hay ninguna moneda registrada con ese ID.")));

        } else {
            $res->getBody()->write(json_encode(array("Monedas" => $monedas)));
        }
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
