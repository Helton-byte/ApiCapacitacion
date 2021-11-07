<?php
//En esta clase hacemos todas las operaciones de la Base de Datos
//Agregamos el archivo config que tienen las variables de conexion
require('config.php');
class MySql
{
	//Creamos la variable de la conexion
	private $conn;
	//Esta funcion establece la conexion a la BD
	public function connect()
	{
		try {
			$this->conn = new PDO("mysql:host=" . Config::SERVERNAME . ";dbname=" . Config::DATABASE, Config::USERNAME, Config::PASSWORD);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return true;
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
			return false;
		}
	}

	//Esta funcion sirve para consultar información a la base de datos sin pasar parametros
	public function Buscar($sql)
	{
		try {
			if ($this->connect()) { //se establece la conexión
				$stmt = $this->conn->prepare($sql); //preparamos la consulta
				$stmt->execute(); //se ejecuta
				$stmt->setFetchMode(PDO::FETCH_ASSOC); //la convertimos a una arreglo
				$res = $stmt->fetchAll(); //Guardamos el arreglo en una variable
				//Este metodo recorreo un arreglo y lo utilizamos para codificar todo lo que obtuvimos a UTF-8 
				array_walk_recursive($res, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
				$this->conn = null;
				return $res; //regresamos el resultado
			} else {
				return array();
			}
		} catch (PDOEXception $e) {
			$error = $sql . "<br />" . $e->getMessage();
			return $error;
		}
	}
	//Esta funcion cierra la conexion
	public function close()
	{
		$this->conn = null;
	}

	//Esta funcion sirve para ejecutar las operaciones INSERT, UPDATE o DELETE y que no tienen parametros
	public function Ejecutar($sql)
	{
		try {
			if ($this->connect()) {
				$res = $this->conn->exec($sql);
				$this->conn = null;
				return $res;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			return $sql . "<br>" . $e->getMessage();
		}
	}

	//Esta funcion sirve para ejecutar las operaciones INSERT, UPDATE o DELETE y pasan parametros 
	//Se usan sentencias preparadas 
	public function Ejecutar_Seguro($sql, $bind)
	{
		try {
			if (count($bind) > 0 && $this->connect()) {
				$stmt = $this->conn->prepare($sql);
				$c = 1;
				for ($i = 0; $i < count($bind); $i++) {

					$validation = FALSE;

					if (is_int($bind[$i])) {
						$validation = PDO::PARAM_INT;
					} else if (is_bool($bind[$i])) {
						$validation = PDO::PARAM_BOOL;
					} else if (is_null($bind[$i])) {
						$validation = PDO::PARAM_NULL;
					} else {
						$validation = PDO::PARAM_STR;
					}

					if (is_null($bind[$i]) || $bind[$i] == "NULL")
						$validation = \PDO::PARAM_NULL;
					$stmt->bindValue($c, $bind[$i], $validation);
					$c++;
				}
				$stmt->execute();
				$this->conn = null;
				return "200";
			} else {
				return "500";
			}
		} catch (PDOException $e) {
			return $sql . "<br>" . $e->getMessage();
		}
	}

	//Esta funcion ejecuta un SELECT que pasa parametros 
	//Usa sentencias preparadas
	public function Buscar_Seguro($sql, $bind)
	{
		try {
			if (count($bind) > 0 && $this->connect()) {
				$stmt = $this->conn->prepare($sql);
				$c = 1;
				for ($i = 0; $i < count($bind); $i++) {

					$validation = FALSE;

					if (is_int($bind[$i])) {
						$validation = PDO::PARAM_INT;
					} else if (is_bool($bind[$i])) {
						$validation = PDO::PARAM_BOOL;
					} else if (is_null($bind[$i])) {
						$validation = PDO::PARAM_NULL;
					} else {
						$validation = PDO::PARAM_STR;
					}

					if (is_null($bind[$i]) || $bind[$i] == "NULL")
						$validation = \PDO::PARAM_NULL;

					$stmt->bindParam($c, $bind[$i], $validation);
					$c++;
				}
				$stmt->execute();
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				array_walk_recursive($res, function (&$item, $key) {
					if (!mb_detect_encoding($item, 'utf-8', true)) {
						$item = utf8_encode($item);
					}
				});
				$this->conn = null;
				return $res;
			} else {
				return "500";
			}
		} catch (PDOException $e) {
			return $sql . "<br>" . $e->getMessage();
		}
	}
}
