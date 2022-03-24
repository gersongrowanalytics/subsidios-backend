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
$router->post('/cerrar-session', 'Validaciones\Login\LoginController@ValCerrarSession');

$router->get('/seed', 'Prueba\PruebaController@EjecutarSeeds');
$router->post('/enviar-correo', 'Validaciones\RecuperarContrasenia\RecuperarContraseniaController@ValRecuperarContrasenia');
$router->get('/asignarfacturasfsi', 'SalvacionController@AsignarPedidoFacturas');
$router->post('/cambiar-contrasenia', 'Validaciones\RecuperarContrasenia\RecuperarContraseniaController@ValCambiarContrasenia');

$router->post('/crear-ambiente', 'Configuracion\CrearAmbienteHomeController@CrearAmbiente');
$router->post('/crear-usuario', 'SalvacionController@CrearUsuario');

$router->post('/aceptar-terminos-condiciones', 'Metodos\Modulos\TerminosCondiciones\AceptarTerminosController@AceptarTerminos');

// $router->group(['middleware' => ['permisos']], function() use($router) {

    $router->group(['prefix' => 'modulo'], function () use ($router) {

        $router->get('/salvacion/asignar-zonas', 'SalvacionController@HabilitarZonas');
        $router->get('/salvacion/reiniciar-subsidos-data-dt/{fecid}', 'SalvacionController@ReinicarSubDtYReal');
        $router->get('/salvacion/cambiar-validados/{fecid}', 'SalvacionController@CambiarValidados');

        $router->post('/salvacion/enviar-correo', 'SalvacionController@EnviarCorreo');
        $router->post('/salvacion/mostrar-pedidos-repetidos', 'SalvacionController@MostrarPedidosRepetidos');

        $router->group(['prefix' => 'perfil'], function () use ($router) {
            $router->post('/editar', 'Validaciones\Modulos\Perfil\EditarPerfilController@ValEditarPerfil');
            $router->post('/editar/imagen', 'Validaciones\Modulos\Perfil\EditarPerfilController@ValEditarImagenPerfil');
            
        });

        $router->group(['prefix' => 'cargaArchivos'], function () use ($router) {
            $router->post('/facturas', 'Validaciones\Modulos\CargaArchivos\CargarFacturasController@CargarFacturas');
            $router->post('/productos', 'Validaciones\Modulos\CargaArchivos\CargarMaestraProductosController@CargarMaestraProductos');
            $router->post('/clientes', 'Validaciones\Modulos\CargaArchivos\CargarMaestraClientesController@CargarMaestraClientes');
            $router->post('/clientes-bloqueados', 'Validaciones\Modulos\CargaArchivos\CargarMaestraClientesController@CargarMaestraClientesBloqueados');
            $router->post('/clientes/sac', 'Validaciones\Modulos\CargaArchivos\CargarClienteSacController@ValCargarClienteSac');
            $router->post('/fechas', 'Validaciones\Modulos\CargaArchivos\CargarMaestraFechasController@CargarMaestraFechas');
            $router->post('/asdasd', 'Validaciones\Modulos\CargaArchivos\CargarMaestraFechasController@CargarMaestraFechas');

            $router->post('/so/subsidios-no-aprobados', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsiduosNoAprobadosController@ValCargarSubsiduosNoAprobados');
            $router->post('/so/subsidios-sac', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsiduosNoAprobadosController@ValCargarSubsiduosSac');
            $router->post('/so/so', 'Validaciones\Modulos\CargaArchivos\SO\CargarSOController@ValCargarSO');

            $router->post('/si/facturas', 'Validaciones\Modulos\CargaArchivos\SI\CargarFacturasSiController@ValCargarFacturasSi');
            $router->post('/si/estado-sunat-facturas', 'Validaciones\Modulos\CargaArchivos\SI\CargarEstadoSunatSiController@ValCargarEstadoSunatSi');

            $router->post('/so/subsidios-so-plantilla', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsidiosController@ValCargarSubsidiosPlantilla');
            $router->post('/so/subsidios-so-automaticos-manuales', 'Validaciones\Modulos\CargaArchivos\SO\CargarSubsidiosController@ValCargarSubsidios');

            $router->post('/clientes-so', 'Validaciones\Modulos\CargaArchivos\CargarClientesSoController@ValCargarClientesSo');
            $router->post('/tipo-cambio', 'Validaciones\Modulos\CargaArchivos\CargarTipoCambioController@ValCargarTipoCambio');
            $router->post('/costo-x-bulto', 'Validaciones\Modulos\CargaArchivos\CargarCostoXBultoController@ValCargarCostoXBulto');

            $router->post('/subsidios-si-formato-ventas', 'Metodos\Modulos\CargaArchivos\SI\SubsidiosSIFormatoVentasController@metSubsidiosSIFormatoVentas');

        });

        $router->group(['prefix' => 'subsidiosSo'], function () use ($router) {
            $router->get('/logica/{fecid}', 'Metodos\Modulos\CargaArchivos\SO\MetCargarSOController@Alinear');
            $router->post('/mostrar', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarSubsidiosSoController@ValMostrarSubsidiosSo');
            $router->post('/mostrar-subsidios-descarga', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarSubsidiosSoController@ValDescargableSubsidiosSo');
            $router->post('/editar-bultos', 'Validaciones\Modulos\SubsidiosSo\Editar\EditarSubsidiosSoController@ValEditarBultosSubsidiosSo');

            $router->post('/mostrar-filtros', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarFiltrosController@ValMostrarFiltros');

            $router->post('/cargar/excepciones', 'Validaciones\Modulos\SubsidiosSo\Cargar\CargarExcepcionesController@ValCargarExcepciones');

            $router->post('/volver-armar-excel', 'Validaciones\Modulos\SubsidiosSo\Mostrar\MostrarSubsidiosSoController@ValVolverArmarExcel');
        });

        $router->group(['prefix' => 'SubsidiosSi'], function () use ($router) {
            $router->post('/logica', 'Validaciones\Modulos\SubsidiosSi\LogicaSubsidiosSiController@ValLogicaSubsidiosSi');
            $router->post('/logica-nueva', 'Metodos\Modulos\SubsidiosSi\MetNuevaLogicaSubsidiosSiController@MetNuevaLogicaSubsidiosSi');
            $router->post('/logica-solic', 'Validaciones\Modulos\SubsidiosSi\LogicaSubsidiosSiController@ValLogicaSubsidiosSiSolic');
            $router->post('/logica-solic-nueva', 'Metodos\Modulos\SubsidiosSi\MetNuevaLogicaSubsidiosSiController@MetNuevaLogicaSubsidiosSiSolic');
            $router->post('/logica-pendientes', 'Validaciones\Modulos\SubsidiosSi\LogicaSubsidiosSiController@ValLogicaSubsidiosSiPendientes');

            $router->post('/mostrar', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarSubsidiosSiController@ValMostrarSubsidiosSi');
            $router->post('/mostrar-subsidios-descarga', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarSubsidiosSiController@ValMostrarDescargaSubsidiosSi');
            $router->post('/mostrar-facturas-asignadas', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarSubsidiosSiController@ValMostrarFacturasAsignadas');

            $router->post('/mostrar/notascreditos', 'Validaciones\Modulos\SubsidiosSi\Mostrar\MostrarNotasCreditoFacturaController@ValMostrarNotasCreditoFactura');

            $router->post('/mostrar-subsidios-si-ventas', 'Metodos\Modulos\SubsidiosSi\Mostrar\MetMostrarSubsidiosSiVentasController@MetMostrarSubsidiosSiVentas');

            $router->post('/mostrar-link-subsidios-si-formato-ventas', 'Metodos\Modulos\CargaArchivos\SI\SubsidiosSIFormatoVentasController@ObtenerLinkSubsidiosSIVentas');

        });

        $router->group(['prefix' => 'regularizacion-so'], function () use ($router) {
            $router->post('/mostrar', 'Validaciones\Modulos\Regularizacion\ValMostrarRegularizacionesController@ValMostrarRegularizaciones');
            $router->post('/asignar-facturas', 'Validaciones\Modulos\Regularizacion\ValMostrarRegularizacionesController@ValMetAsignarFacturas');
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

        $router->group(['prefix' => 'bigdata'], function () use ($router) {
            $router->post('/mostrar-facturassi', 'Validaciones\Modulos\BigData\MostrarBigDataController@ValMostrarFacturasSi');
            $router->post('/mostrar-facturasso', 'Validaciones\Modulos\BigData\MostrarBigDataController@ValMostrarFacturasSo');
            $router->post('/mostrar-materiales', 'Validaciones\Modulos\BigData\MostrarBigDataController@ValMostrarMateriales');
            $router->post('/mostrar-clientes', 'Validaciones\Modulos\BigData\MostrarBigDataController@ValMostrarClientes');
        });

        $router->group(['prefix' => 'home'], function () use ($router) {

            $router->post('/mostrar/estados-pendientes', 'Validaciones\Modulos\Home\Mostrar\MostrarEstadosPendientesController@ValMostrarEstadosPendientes');

        });

        $router->group(['prefix' => 'nota-credito'], function () use ($router) {
            $router->post('/generar/excel-nota-credito', 'Validaciones\Modulos\NotasCredito\GenerarExcelNcController@ValGenerarExcelNc');
            $router->post('/mostrar/data-distribuidores', 'Validaciones\Modulos\NotasCredito\MostrarDataController@ValMostrarDataDistribuidoresNc');
            // $router->get('/generar/excel-nota-credito', 'Validaciones\Modulos\NotasCredito\GenerarExcelNcController@ValGenerarExcelNc');
        });

        $router->group(['prefix' => 'control-panel'], function () use ($router) {
            $router->post('/mostrar', 'Validaciones\Modulos\ControlPanel\Mostrar\MostrarControlPanelController@ValMostrarControlPanel');
        });

        $router->group(['prefix' => 'administrador'], function () use ($router) {
            $router->group(['prefix' => 'mostrar'], function () use ($router) {
                $router->post('/tipos-usuarios', 'Validaciones\Modulos\Administrador\TiposUsuarios\MostrarTiposUsuariosController@ValMostrarTiposUsuarios');
                $router->post('/usuarios', 'Validaciones\Modulos\Administrador\Usuarios\MostrarUsuariosController@ValMostrarUsuarios');

                $router->post('/control-archivos', 'Validaciones\Modulos\Administrador\ControlArchivos\MostrarControlArchivosController@ValMostrarControlArchivos');
            });
        });

    });

// });

$router->get('/asignar-bultos-acidos', 'SalvacionController@AsignarBultosAcidos');
$router->get('/limpiar-sde/{fecid}', 'SalvacionController@LimpiarSde');
$router->get('/asignar-detalle-factura-sfs', 'SalvacionController@AgregarDetalleFacturaSfs');
$router->get('/asignar-ids-factura-sfs', 'SalvacionController@AsignarIdFdsFsiASfs');
$router->get('/treinta-por-ciento-sfs/{fecid}', 'SalvacionController@TreintaPorCientoSfs');
$router->get('/alerta-facturas-asiganadas-clientes/{fecid}', 'SalvacionController@AlertaAsignacionFacturas');
$router->get('/alerta-validar-notas-creidtos-asignadas/{fecid}', 'SalvacionController@ValidarNcAsignadas');
$router->get('/alerta-estados-facturas-asignadas/{fecid}', 'SalvacionController@AlertaEstadoFacturasAsignadas');
$router->get('/alerta-clientes-bloqueados-facturas-asignadas/{fecid}', 'SalvacionController@AlertaClientesBloqueados');
$router->get('/contar-numero-sunat', 'SalvacionController@MostrarSunatXMes');
$router->get('/alerta-diferencia-monto-subsidiar-monto-subsidiar/{fecid}', 'SalvacionController@AlertaRestarMontoSubsidiarXMontoSubsidiado');
$router->get('/alerta-diferencia-so-vs-si/{fecid}', 'SalvacionController@AlertaValidarDiferenciaSOSI');

$router->get('/obtener-subsidios-pendientes/{fecid}', 'SalvacionController@ObtenerSubsidiosPendientes');

$router->get('/asignar-csoid-a-subsidios/{fecid}', 'SalvacionController@AsignarCsoidASubsidios');
$router->get('/convertir-dolares-bultos-total/{fecid}', 'SalvacionController@ConvertirDolaresBultosTotal');

$router->get('/sumar-directo-indirecto-calcular-total-soles-y-dolares/{fecid}', 'SalvacionController@CalcularCostosBultoDolares');