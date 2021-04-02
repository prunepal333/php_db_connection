<?php
    require_once "init.php";
    class MysqlQueryBuilder implements SQLQueryBuilder
    {
        protected $query;
        public function reset():void
        {
            $this->query = new \stdClass();
            $this->query->values = [];
        }
        
        public function select(string $table, array $fields): SQLQueryBuilder{
            $this->reset();
            $this->query->type = 'select';
            $this->query->base = 'SELECT ';
            for($x = 0, $n = count($fields); $x < $n; ++$x)
            {
                $this->query->base .= $fields[$x];
                if($x !== $n - 1) $this->query->base  .= ', ';
            }
            $this->query->base .= " FROM " . $table ;
            
            return $this;
        }
        public function where(string $field, string $value, string $operator = "="): SQLQueryBuilder{
            if(!in_array($this->query->type, array('select', 'update', 'delete'))){
                throw new \Exception('Where not supported for `'. $this->query->type . '` query type');
            }
            $this->query->where[] = " `$field` $operator ?";
            $this->query->values[] = $value;

            return $this;
        }

        public function limit(int $start, int $offset): SQLQueryBuilder
        {
            if(!in_array($this->query->type, array('select'))){
                throw new \Exception('Limit allowed for select query type only!');
            }
            $this->query->limit = " LIMIT $start, $offset";

            return $this;
        }

        public function getQuery(): array{
            return array(
                'query' => $this->getQueryString(),
                'params' => $this->query->values,
            );
        }

        public function getQueryString(): string{
            if(!in_array($this->query->type, array('select', 'insert', 'update', 'delete'))){
                throw new Exception('Operation '. $this->query->type. ' not supported');
            }
            if($this->query->type === 'delete' && !isset($this->query->where)){
                throw new Exception('Where clause is required for delete query.');
            }
            if(isset($this->query->where)){
                $this->query->base .= " WHERE " . implode(' AND ',  $this->query->where);
            }
            if(isset($this->query->limit)){
                $this->query->base .= $this->query->limit;
            }
            return $this->query->base;
        }

        public function insert(string $table, array $assoc_field): SQLQueryBuilder{
            $this->reset();

            $this->query->type = 'insert';
            $keys = array_keys($assoc_field);
            $this->query->values = array_values($assoc_field);
            $this->query->base = " INSERT INTO $table (";
            $this->query->base .= implode("," , $keys);
            $this->query->base .= ") VALUES( ";
            $this->query->base .= implode(", ", array_map(function($item){return "?";},$keys));
            $this->query->base .= ")";

            return $this;
        }
        public function update(string $table, array $assoc_field): SQLQueryBuilder{
            $this->reset();

            $this->query->type = 'update';

            $this->query->base = "UPDATE ". $table. " SET ";
            
            $keys = array_keys($assoc_field);

            for($x = 0, $n = count($keys); $x < $n;$x++)
            {
                $this->query->base .= $keys[$x]. " = ?";
                if($x < $n - 1){
                    $this->query->base .= ", ";
                }
            }
            $this->query->values = array_values($assoc_field);

            return $this;
        }
        public function delete(string $table): SQLQueryBuilder{
            $this->reset();

            $this->query->type = 'delete';

            $this->query->base = "DELETE FROM $table";
            
            return $this;
        }
    }
?>