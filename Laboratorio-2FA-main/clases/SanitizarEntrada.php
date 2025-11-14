<?PHP
class SanitizarEntrada {

    // Sanitiza una cadena eliminando espacios y etiquetas HTML
    public static function limpiarCadena($cadena) {
        return trim(strip_tags((string) $cadena));
    }

    // Sanitiza correos electrónicos dejando solo caracteres válidos
    public static function limpiarEmail($correo) {
        $correo = filter_var((string) $correo, FILTER_SANITIZE_EMAIL);
        return strtolower(trim($correo));
    }

    // Sanitiza números enteros devolviendo 0 si no es válido
    public static function limpiarEntero($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false
            ? (int) $valor
            : 0;
    }

    // Sanitiza textos largos normalizando espacios
    public static function limpiarTextoPlano($texto) {
        $texto = strip_tags((string) $texto);
        $texto = preg_replace('/\s+/u', ' ', $texto);
        return trim($texto);
    }

}//SanitizarEntrada

//$nombre = "<b>Juan</b> ";
//$nombreLimpio = SanitizarEntrada::limpiarCadena($nombre);  
//echo "la salida es: ".$nombre."<br>";
?>