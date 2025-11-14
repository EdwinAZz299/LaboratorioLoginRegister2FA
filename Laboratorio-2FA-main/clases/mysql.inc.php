<?php

class mod_db
{
	private $conexion; // Conexión a la base de datos
	private $perpage = 5; // Cantidad de registros por página
	private $total;
	private $pagecut_query;
	private $debug = false; // Cambiado a false para mantener la configuración original

	public function __construct()
	{
		
		##### Setting SQL Vars #####
		$sql_name = getenv('DB_NAME') ?: 'company_info';
		$sql_user = getenv('DB_USER') ?: 'root';	
		$sql_pass = getenv('DB_PASS') ?: '';

		$defaultHost = getenv('DB_HOST') ?: '127.0.0.1';
		$defaultPort = getenv('DB_PORT') ?: '3309';

		$dsnCandidates = [
			["host" => $defaultHost, "port" => $defaultPort],
			["host" => $defaultHost, "port" => '3306'],
			["host" => 'localhost', "port" => '3306'],
		];

		$lastError = '';
		foreach ($dsnCandidates as $candidate) {
			$dsn = sprintf(
				"mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
				$candidate['host'],
				$candidate['port'],
				$sql_name
			);
			try {
				$this->conexion = new PDO($dsn, $sql_user, $sql_pass);
				$this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				if ($this->debug) {
					echo "Conexión exitosa a la base de datos en {$candidate['host']}:{$candidate['port']}<br>";
				}
				return;
			} catch (PDOException $e) {
				$lastError = $e->getMessage();
				if ($this->debug) {
					error_log("Fallo al conectar usando {$candidate['host']}:{$candidate['port']} - " . $lastError);
				}
			}
		}

		echo "Error de conexión: " . $lastError;
		exit;
	}

	public function getConexion (){

		return $this->conexion;
	}

	public function disconnect()
	{
		$this->conexion = null; // Cierra la conexión a la base de datos
	}

	public function insert($tb_name, $cols, $val)
{
    $cols = $cols ? "($cols)" : "";
    $sql = "INSERT INTO $tb_name $cols VALUES ($val)";
    
    try {
        $this->conexion->exec($sql);
    } catch (PDOException $e) {
        echo "Error al insertar: " . $e->getMessage();
    }
}

public function insertSeguro($tb_name, $data)
{
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    $sql = "INSERT INTO $tb_name ($columns) VALUES ($placeholders)";

    try {
        $stmt = $this->conexion->prepare($sql);

        // Asignar valores con bind
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Error en INSERT: " . $e->getMessage();
        return false;
    }
}

	/*public function update($tb_name, $string, $astriction)
	{
		$sql = "UPDATE $tb_name SET $string";
		$this->executeQuery($sql, $astriction);
	}*/

	/*public function del($tb_name, $astriction)
	{
		$sql = "DELETE FROM $tb_name";
		if ($astriction) {
			$sql .= " WHERE $astriction"; // Agrega la restricción si existe
		}
		$this->executeQuery($sql);
	}*/

	public function query($string)
	{
		return $this->executeQuery($string);
	}


	public function log($correo){

	 // Preparar la consulta

		 try {
		 $sql = "SELECT * FROM usuarios WHERE correo = :correo";
		 $stmt = $this->conexion->prepare($sql);
		 $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);

		 // Ejecutar la consulta
		 $stmt->execute();

			// Retornar el objeto directamente
            return $stmt->fetchObject();
		
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
            return null;
		}

	} //log(usuario)


	public function nums($string = "", $stmt = null)
	{
		if ($string) {
			$stmt = $this->query($string);
		}
		$this->total = $stmt ? $stmt->rowCount() : 0; // Cuenta el número de filas
		return $this->total;
	}

	public function objects($string = "", $stmt = null)
	{
		if ($string) {
			$stmt = $this->query($string);
		}
		return $stmt ? $stmt->fetch(PDO::FETCH_OBJ) : null; // Retorna un objeto
	}

	public function insert_id()
	{
		return $this->conexion->lastInsertId(); // Retorna el último ID insertado
	}

	public function page_cut($string, $nowpage = 0)
	{
		$start = $nowpage ? ($nowpage - 1) * $this->perpage : 0; // Calcula el inicio de la página
		$this->pagecut_query = "$string LIMIT $start, $this->perpage";
		return $this->pagecut_query;
	}

	public function show_page_cut($string = "", $num = "", $url = "")
	{
		$nowpage = isset($_REQUEST['nowpage']) ? $_REQUEST['nowpage'] : 1; // Obtiene la página actual
		$this->total = $string ? $this->nums($string) : $num; // Total de registros
		$pages = ceil($this->total / $this->perpage); // Calcula el total de páginas
		$pagecut = "";

		for ($i = 1; $i <= $pages; $i++) {
			if ($nowpage == $i) {
				$pagecut .= $i; // Página actual
			} else {
				$pagecut .= "<a href='$url&nowpage=$i'><font color='336600' style='font-size:10pt'>$i</font></a>";
			}
		}

		return $pagecut; // Retorna el paginador
	}



	private function executeQuery($sql)
	{
			try {
				// **ELIMINAR ESTAS LÍNEAS (porque son peligrosas):**
				// if ($astriction) {
				//     $sql .= " WHERE $astriction"; 
				// }
				
				$stmt = $this->conexion->prepare($sql);
				$stmt->execute();
				
				if ($this->debug) {
					error_log("Query ejecutada: " . $sql); // Usar error_log es mejor
				}
				return $stmt;
			} catch (PDOException $e) {
				// Aquí también deberías usar error_log para debugging en producción
				echo "Error en la consulta: " . $e->getMessage();
				return null;
			}
	}
}
