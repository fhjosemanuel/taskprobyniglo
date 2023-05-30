<?php

namespace Model;

class Usuario extends ActiveRecord {

    protected static $tabla = "usuarios";
    protected static $columnasDB = ['id','nombre','email','password','token','confirmado'];

    public function __construct( $args = [] )
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // Validacion para cuentas nuevas
    public function validarNuevaCuenta() : array {
        if(!$this->nombre){
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }

        if(!$this->email){
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if(!$this->password){
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatorio';
        }
        
        if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'La Contraseña debe contener 6 caracteres como minimo';
        }

        if($this->password !== $this->password2){
            self::$alertas['error'][] = 'Las Contraseñas no Coinciden';
        }

        return self::$alertas;
    }

    // Validar Login
    public function validarLogin() : array {
        if(!$this->email){
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if(!filter_var($this->email , FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'El Email no es Valido';
        }

        if(!$this->password){
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatorio';
        }

        return self::$alertas;
    }

    // Valida un Email
    public function validarEmail() : array{
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        if(!filter_var($this->email , FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'El Email no es Valido';
        }

        return self::$alertas;
    }
    // Valida el password
    public function validarPassword() : array {

        if(!$this->password){
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatorio';
        }
        
        if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'La Contraseña debe contener 6 caracteres como minimo';
        }

        return self::$alertas;
    }

    public function validar_perfil() : array{
        if(!$this->nombre){
            self::$alertas['error'][] = 'El Nombre es Obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }

        return self::$alertas;
    }

    public function nuevo_password() : array{
        if(!$this->password_actual){
            self::$alertas['error'][] = 'Ingrese su Contraseña Actual';
        };

        if(!$this->password_nuevo){
            self::$alertas['error'][] = 'Debe Ingresar una Nueva Contraseña';
        }

        if(strlen($this->password_nuevo) < 6){
            self::$alertas['error'][] = 'La Contraseña debe tener al menos 6 caracteres';
        }

        return self::$alertas;
    }

    // Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->password);
    }

    // Hashea el password
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Generar un Token
    public function crearToken() :void {
        $this->token = uniqid();
    }


}