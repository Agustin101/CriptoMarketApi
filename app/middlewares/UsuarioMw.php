<?php

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Psr7\Response as Response;

require_once "./repositorios/UsuarioRepositorio.php";
require_once "./util/Jwt.php";

class UsuarioMw
{

    public function ValidarCampos(IRequest $request, IRequestHandler $handler): Response
    {
        $reqBody = $request->getParsedBody();
        $mail = $reqBody["mail"];
        $password = $reqBody["clave"];
        $msg = "";

        if ($mail == "" || $mail == null) {
            $msg = $msg . "Verifique el campo mail. ";
        }

        if ($password == "" || $password == null) {
            $msg = $msg . "Verifique el campo de la clave.";
        }

        if ($msg != "") {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => $msg)));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        return $handler->handle($request);
    }

    public function VerificarUsuarioExistente(IRequest $request, IRequestHandler $handler): Response
    {
        $reqBody = $request->getParsedBody();
        $mail = $reqBody["mail"];
        $existe = UsuarioRepositorio::ExisteUsuario($mail);
        if (!$existe) {
            $response = new Response();
            $response->getBody()->write("El usuario indicado no se encuentra registrado.");
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $handler->handle($request);
    }
}
