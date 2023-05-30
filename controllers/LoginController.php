<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;
use RuntimeException;

class LoginController {

    public static function login( Router $router ){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            
            $alertas = $auth->validarLogin();

            if( empty($alertas) ){
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $auth->email);
                
                if( !$usuario || $usuario->confirmado === '0'){
                    Usuario::setAlerta('error','El usuario no existe o no esta confirmado');
                }else{
                    // El usuario existe
                    if( password_verify($_POST['password'], $usuario->password) ){
                        //Iniciar la Session
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // redireccionar
                        header('Location: /dashboard');
                    }else{
                        Usuario::setAlerta('error','Email o Password Incorrecto');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        //render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesion',
            'alertas' => $alertas
        ]);
    }

    public static function logout(){
        session_start();
        $_SESSION= [];
        header('Location: /');
    }

    public static function crear( Router $router ){

        $usuario = new Usuario;
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if( empty($alertas) ){

                $existeUsuario = Usuario::where('email', $usuario->email);

                if( $existeUsuario ){
                    Usuario::setAlerta('error' , 'El Usuario ya esta Registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();

                    // Eliminar password2
                    unset($usuario->password2);

                    // Generar el token
                    $usuario->crearToken();

                    //Crear el nuevo Usuario
                    $resultado = $usuario->guardar();

                    $email = new Email( $usuario->email, $usuario->nombre, $usuario->token );
                    $email->enviarConfirmacion();
                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
            
        }

        //render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }
    
    public static function olvide( Router $router ){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if( empty($alertas) ){
                //Buscar el Usuario
                $usuario = Usuario::where('email', $usuario->email);
                if( $usuario && $usuario->confirmado === "1"){
                    
                    // Generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    // Actualizar el usuario
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email( $usuario->email, $usuario->nombre, $usuario->token );
                    $email->enviarReestablecer();

                    // Imprimir la alerta
                    Usuario::setAlerta('exito','Hemos enviado las instrucciones a tu email');
                }else{
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                }
                
            }
        }

        $alertas = Usuario::getAlertas();

        //render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer( Router $router ){

        $alertas = [];
        $mostrar = true;
        $token = s($_GET['token']);
        
        if(!$token) header('Location: /');

        //Iedntificar el usuario con este token
        $usuario = Usuario::where('token', $token);
        if( empty($usuario) ){
            Usuario::setAlerta('error','El token no es valido');
            $mostrar = false;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //Añádir el nuevo password
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validarPassword();
 
            if( empty($alertas) ){

                // Hashear el nuevo Password
                $usuario->hashPassword();
                
                // Eliminar el token
                $usuario->token = null;

                // Guardar el usuario en la BD
                $resultado = $usuario->guardar();

                // Redireccionar
                if( $resultado ){
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        //render a la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje( Router $router ){

        //render a la vista
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);
    }

    public static function confirmar( Router $router ){

        $token = s($_GET['token']);

        if(!$token){
            header('Location: /');
        }

        // Encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);
        
        if( empty($usuario) ){
            //No se Encontro un usuario con ese token
            Usuario::setAlerta('error','Token No Válido');
        } else {
            // Confirmar la Cuenta
            $usuario->confirmado = 1;
            unset( $usuario->password2 );
            $usuario->token = null;
            
            //Guardar en la base de datos
            $usuario->guardar();
            Usuario::setAlerta('exito','Cuenta Confirmada Correctamente');
        }

        $alertas = Usuario::getAlertas();

        //render a la vista
        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}