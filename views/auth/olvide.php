<div class="contenedor olvide">
    <?php @include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>

    <div class="contenedor-sm">
        <?php @include_once __DIR__ . '/../templates/alertas.php'; ?>

        <p class="descripcion-pagina">Recuperar Password</p>
        <form class="formulario" method="POST" action="/olvide">
            
            <div class="campo">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Tu Email"
                    name="email"
                />
            </div>

            <input type="submit" class="boton" value="Recuperar">

            <div class="acciones">
                <a href="/">¿Ya tienes cuenta? Iniciar Sesión</a>
                <a href="/crear">¿Aún no tienes una cuenta? Obtener una</a>
            </div>
        </form> <!-- Contenedor SM -->
    </div>
</div>