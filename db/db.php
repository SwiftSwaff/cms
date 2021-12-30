<?php
/*
 * DB class extends default PDO class, allows it to serve as the database object
 *      preparedQuery ($sql, $param): establishes and executes a prepared statement
 */
class DB extends \PDO {
    protected static $_instance = null;

    public function __construct() {
        $isLive = false;
        $xml = json_decode(json_encode(simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/dbConfig.xml")), true);
        $xmlTable = ($isLive) ? $xml["live-database"] : $xml["staging-database"]; 
        $config = array(
            "host" => !empty($xmlTable["host"]) ? $xmlTable["host"] : "",
            "user" => !empty($xmlTable["user"]) ? $xmlTable["user"] : "",
            "pass" => !empty($xmlTable["pass"]) ? $xmlTable["pass"] : "",
            "name" => !empty($xmlTable["name"]) ? $xmlTable["name"] : ""
        );

        $connection = "mysql:host={$config["host"]};dbname={$config["name"]};charset=UTF8";

        parent::__construct($connection, $config["user"], $config["pass"]);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function preparedQuery($sql, $params=[]) {
        $stmt = $this->prepare($sql);
        foreach ($params as $symbol => $param) {
            $stmt->bindParam($symbol, $param["value"], $param["type"]);
        }
        $stmt->execute();
        return $stmt;
    }
}
?>