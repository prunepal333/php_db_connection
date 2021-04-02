<?php
    require_once "init.php";
    class DB{
        private SQLQueryBuilder $queryBuilder;
        private PDO $conn;
        private static $config = array(
            'host' => 'localhost',
            'db' => 'db',
            'user' => 'root',
            'pass' => ''
        );
        private static DB $instance;
        private function __construct(SQLQueryBuilder $queryBuilder){
            $this->queryBuilder = $queryBuilder;

            $this->conn = new PDO("mysql:host=".self::$config['host'].";dbname=".self::$config['db'], self::$config['user'], self::$config['pass']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        public static function getInstance(){
            if(!isset(self::$instance)){
                self::$instance = new DB(new MysqlQueryBuilder());
            }
            return self::$instance;
        }
        public function select(string $table, array $fields, array $where_arr=array()){
            $this->queryBuilder = $this->queryBuilder->select($table, $fields);
            $this->where($where_arr);
            $stmt = $this->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        }

        public function insert($table, $fields){
            $this->queryBuilder->insert($table, $fields);
            $stmt = $this->execute();
            return true;
        }
        protected function where($where_arr){
            if(!empty($where_arr) && !is_array($where_arr[0])){
                $where_arr = array(
                    0 => $where_arr
                );
            }
            foreach($where_arr as $where){
                if(count($where) == 2){
                    $op = "=";
                }else if(count($where) === 3){
                    $op = $where[2];    
                }else{
                    die('2 or 3 fields expected in where clause but '. count($where). ' fields passed for ' . var_dump($where));
                }
                $this->queryBuilder = $this->queryBuilder->where($where[0], $where[1], $op);
            }
        }
        public function delete($table, $where_arr){
            $this->queryBuilder = $this->queryBuilder->delete($table);
            $this->where($where_arr);
            $this->execute();
            return true;
        }
        public function update($table, $fields, $where_arr){
            $this->queryBuilder->update($table, $fields);
            $this->where($where_arr);
            $this->execute();
            return true;
        }
        public function execute(){
            $query = $this->queryBuilder->getQuery();
            $stmt = $this->conn->prepare($query['query']);
            $stmt->execute($query['params']);
            return $stmt;
        }
        public static function configure($host, $db, $user, $pass){
            self::configureHost($host);
            self::configureDB($db);
            self::configureUser($user);
            self::configurePass($pass);
        }
        public static function configureUser($user){self::$config['user'] = $user;}
        public static function configurePass($pass){self::$config['pass'] = $pass;}
        public static function configureDB($db){self::$config['db'] = $db;}
        public static function configureHost($host){self::$config['host'] = $host;}
    }
?>