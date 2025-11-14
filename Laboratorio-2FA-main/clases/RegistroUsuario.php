<?php

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class RegistroUsuario
{
    private mod_db $db;
    private GoogleAuthenticator $authenticator;

    public function __construct(mod_db $db, GoogleAuthenticator $authenticator)
    {
        $this->db = $db;
        $this->authenticator = $authenticator;
    }

    /**
     * Prepara y sanitiza los datos provenientes del formulario.
     */
    public function prepararDatos(array $input): array
    {
        return [
            'nombre' => SanitizarEntrada::limpiarCadena($input['nombre'] ?? ''),
            'apellido' => SanitizarEntrada::limpiarCadena($input['apellido'] ?? ''),
            'correo' => SanitizarEntrada::limpiarEmail($input['correo'] ?? ''),
            'contrasena' => (string) ($input['contrasena'] ?? ''),
            'confirm_contrasena' => (string) ($input['confirm_contrasena'] ?? ''),
            'sexo' => SanitizarEntrada::limpiarCadena($input['sexo'] ?? ''),
        ];
    }

    /**
     * Valida reglas de negocio del formulario.
     * Retorna un arreglo de mensajes de error (vacío si todo es válido).
     */
    public function validarDatos(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['correo']) ||
            empty($datos['contrasena']) || empty($datos['confirm_contrasena']) || empty($datos['sexo'])) {
            $errores[] = 'Todos los campos son obligatorios.';
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        }

        if ($datos['contrasena'] !== $datos['confirm_contrasena']) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (strlen($datos['contrasena']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        return $errores;
    }

    public function correoDisponible(string $correo): bool
    {
        $stmt = $this->db->getConexion()->prepare('SELECT id FROM usuarios WHERE correo = :correo');
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    public function generarHash(string $contrasena): string
    {
        $options = ['cost' => 13];
        return password_hash($contrasena, PASSWORD_BCRYPT, $options);
    }

    public function crearUsuario(array $datos): array
    {
        $secret = $this->authenticator->generateSecret();
        $fecha = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');

        $payload = [
            'Nombre' => $datos['nombre'],
            'Apellido' => $datos['apellido'],
            'Usuario' => $datos['correo'],
            'Correo' => $datos['correo'],
            'HashMagic' => $this->generarHash($datos['contrasena']),
            'sexo' => $datos['sexo'],
            'secret_2fa' => $secret,
            'FechaSistema' => $fecha,
        ];

        $resultado = $this->db->insertSeguro('usuarios', $payload);

        return [
            'exito' => (bool) $resultado,
            'secret' => $secret,
        ];
    }
}


