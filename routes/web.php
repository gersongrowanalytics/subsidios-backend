<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/propro', function () use ($router) {
    echo "hola";
});


$router->post('/login', 'Validaciones\Login\LoginController@ValLogin');

$router->get('/seed', 'Prueba\PruebaController@EjecutarSeeds');

// $router->group(['middleware' => ['permisos']], function() use($router) {

    $router->group(['prefix' => 'modulo'], function () use ($router) {

        $router->group(['prefix' => 'cargaArchivos'], function () use ($router) {
            $router->post('/facturas', 'Validaciones\Modulos\CargaArchivos\CargarFacturasController@CargarFacturas');
            $router->post('/productos', 'Validaciones\Modulos\CargaArchivos\CargarMaestraProductosController@CargarMaestraProductos');
            $router->post('/clientes', 'Validaciones\Modulos\CargaArchivos\CargarMaestraClientesController@CargarMaestraClientes');
            $router->post('/clientes/sac', 'Validaciones\Modulos\CargaArchivos\CargarClienteSacController@ValCargarClienteSac');
            $router->post('/fechas', 'Validaciones\Modulos\CargaArchivos\CargarMaestraFechasController@CargarMaestraFechas');

            $router->post('/so/subsidios-no-aprobados', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsiduosNoAprobadosController@ValCargarSubsiduosNoAprobados');
            $router->post('/so/subsidios-sac', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsiduosNoAprobadosController@ValCargarSubsiduosSac');
            $router->post('/so/so', 'Validaciones\Modulos\CargaArchivos\SO\CargarSOController@ValCargarSO');

            $router->post('/si/facturas', 'Validaciones\Modulos\CargaArchivos\SI\CargarFacturasSiController@ValCargarFacturasSi');
            $router->post('/si/estado-sunat-facturas', 'Validaciones\Modulos\CargaArchivos\SI\CargarEstadoSunatSiController@ValCargarEstadoSunatSi');

            $router->post('/so/subsidios-so-plantilla', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsidiosController@ValCargarSubsidiosPlantilla');
            $router->post('/so/subsidios-so-automaticos-manuales', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsidiosController@ValCargarSubsidios');

        });

        $router->group(['prefix' => 'subsidiosSo'], function () use ($router) {
            $router->get('/logica/{fecid}', 'Metodos\Modulos\CargaArchivos\SO\MetCargarSOController@Alinear');
            $router->post('/mostrar', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarSubsidiosSoController@ValMostrarSubsidiosSo');

            $router->post('/mostrar-filtros', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarFiltrosController@ValMostrarFiltros');
        });

        $router->group(['prefix' => 'SubsidiosSi'], function () use ($router) {
            $router->post('/logica', 'Validaciones\Modulos\SubsidiosSi\LogicaSubsidiosSiController@ValLogicaSubsidiosSi');
            $router->post('/mostrar', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarSubsidiosSiController@ValMostrarSubsidiosSi');

            $router->post('/mostrar/notascreditos', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarNotasCreditoFacturaController@ValMostrarNotasCreditoFactura');
        });

        $router->group(['prefix' => 'SubsidiosPendientes'], function () use ($router) {
            $router->post('/mostrar', 'Validaciones\Modulos\SubsidiosPendientes\Mostrar\MostrarSubsidiosPendientesController@ValMostrarSubsidiosPendientes');

            $router->post('/mostrar/facturas', 'Validaciones\Modulos\SubsidiosPendientes\Mostrar\MostrarFacturasSubsidiosPendientesController@ValMostrarFacturasSubsidiosPendientes');

            $router->post('/asignar-facturas', 'Validaciones\Modulos\SubsidiosPendientes\AsignarFacturasController@ValAsignarFacturas');
            $router->post('/eliminar-facturas', 'Validaciones\Modulos\SubsidiosPendientes\EliminarFacturasController@ValEliminarFacturas');
        });

        $router->group(['prefix' => 'facturas'], function () use ($router) {
            $router->post('/mostrar', 'Validaciones\Modulos\Facturas\MostrarFacturasController@ValMostrarFacturas');
            $router->post('/mostrar/reconocimiento', 'Validaciones\Modulos\Facturas\MostrarSubsidiosAsignadosController@ValMostrarSubsidiosAsignados');
        });

        $router->group(['prefix' => 'home'], function () use ($router) {

            $router->post('/mostrar/estados-pendientes', 'Validaciones\Modulos\Home\Mostrar\MostrarEstadosPendientesController@ValMostrarEstadosPendientes');

        });


        $router->group(['prefix' => 'control-panel'], function () use ($router) {
            $router->post('/mostrar', 'Validaciones\Modulos\ControlPanel\Mostrar\MostrarControlPanelController@ValMostrarControlPanel');
        });

    });

// });
