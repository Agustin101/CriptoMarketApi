<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once './middlewares/UsuarioMW.php';
require_once "./controllers/AutorizacionController.php";
require_once "./controllers/MonedasController.php";
require_once "./controllers/VentasController.php";
require_once "./middlewares/JwtMw.php";
require_once "./middlewares/AdminMw.php";

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

$app->setBasePath('/CriptoMarket/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->post("/login", \AutorizacionController::class . ":GenerarToken")
    ->add(\UsuarioMw::class . ":VerificarUsuarioExistente")
    ->add(\UsuarioMw::class . ":ValidarCampos");

$app->group("/monedas", function (RouteCollectorProxy $group) {
    $group->post("[/]", \MonedasController::class . ":AgregarCripto")
        ->add(\AdminMw::class . ":ValidarAdmin")
        ->add(\JwtMw::class . ":ValidarToken");

    $group->get("[/]", \MonedasController::class . ":ObtenerCriptos");
    $group->get("/filtro", \MonedasController::class . ":ObtenerCriptosNacionalidad");
    $group->get("/{id}", \MonedasController::class . ":ObtenerCriptoPorId")
        ->add(\JwtMw::class . ":ValidarToken");

});

$app->group("/ventas", function (RouteCollectorProxy $group) {
    $group->post("[/]", \VentasController::class . ":AgregarVenta")
        ->add(\JwtMw::class . ":ValidarToken");

    $group->get("/{nacionalidad}", \VentasController::class . ":ObtenerVentasNacionalidad")
        ->add(\AdminMw::class . ":ValidarAdmin")
        ->add(\JwtMw::class . ":ValidarToken");

    $group->get("/moneda/{nombre}", \VentasController::class . ":ObtenerVentasNombreMoneda")
        ->add(\AdminMw::class . ":ValidarAdmin")
        ->add(\JwtMw::class . ":ValidarToken");

});

$app->run();
