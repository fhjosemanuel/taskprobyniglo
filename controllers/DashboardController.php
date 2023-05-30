<?php

namespace Controllers;

use EmptyIterator;
use Model\Proyecto;
use Model\Usuario;
use MVC\Router;
use SessionUpdateTimestampHandlerInterface;

class DashboardController {

    public static function index( Router $router ){

        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto( Router $router ){
        session_start();

        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);
            
            // Validacion
            $alertas = $proyecto->validarProyecto();

            if( empty($alertas) ){
                // Generar una URL única
                $hash = md5( uniqid());
                $proyecto->url = $hash;

                // Almacenar creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                // Guardar el Proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?url=' . $proyecto->url);
            }

        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto( Router $router ){

        session_start();
        isAuth();

        $token = $_GET['url'];
        if(!$token) header('Location: /dashboard');

        //Revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        
        if( $proyecto->propietarioId !== $_SESSION['id'] ){
            header('Location: /dashboard');
        }
        

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil( Router $router ){
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
            
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_perfil();

            if( empty($alertas) ){

                //Verificar que el email no este en uso
                $existe = Usuario::where('email', $usuario->email);

                if($existe && $existe->id !== $usuario->id){
                    //Mostrar un mensaje de error
                    Usuario::setAlerta('error', 'Email Invalido, ya Pertenece a Otra Cuenta');
                }else{
                    //Guardar el Usuario
                    $resultado = $usuario->guardar();
                    if($resultado){
                        Usuario::setAlerta('exito', 'Cambio Realizado Correctamente');
                        $_SESSION['nombre'] = $usuario->nombre;
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password( Router $router ){
        session_start();
        isAuth();
        $alertas = [];

        if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
            $usuario = Usuario::find($_SESSION['id']);

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if( empty($alertas) ){
                $resultado = $usuario->comprobar_password();
                if( $resultado ){
                    // Asignar el nuevo password
                    $usuario->password = $usuario->password_nuevo;

                    //Eliminamos los atributos innecesarios 
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    // Hasheamos la contraseña
                    $usuario->hashPassword();

                    // Actualizar el Usuario
                    $resultado = $usuario->guardar();
                    if( $resultado ){
                        Usuario::setAlerta('exito', 'Contraseña actualizada correctamente');
                    }
                }else{
                    Usuario::setAlerta('error', 'Contraseña Incorrecta');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}