<?php

require_once './util/Jwt.php';
require_once './repositorios/UsuarioRepositorio.php';

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response as Response;

class AutorizacionController
{

    public function GenerarToken(IRequest $req, IResponse $res): IResponse
    {
        $params = $req->getParsedBody();
        $mail = $params["mail"];
        $clave = $params["clave"];
        $usuario = $this->ValidarCredenciales($mail, $clave);

        if ($usuario === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "Credenciales invalidas.")));
            return $res
                ->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $token = Token::CrearToken($usuario);
        $tipoUsuario = $usuario["tipo"] == 1 ? "ADMIN" : "CLIENTE";

        $res->getBody()->write(json_encode(array("jwtcripto" => $token, "Tipo de usuario: " => $tipoUsuario)));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function ValidarCredenciales(string $mail, string $clave)
    {
        $usuario = UsuarioRepositorio::ObtenerUsuario($mail);

        if (!password_verify($clave, $usuario["CLAVE"])) {
            return false;
        }

        $datos = array("id" => $usuario["ID"], "mail" => $usuario["MAIL"], "tipo" => $usuario["TIPO"]);
        return $datos;
    }

}
