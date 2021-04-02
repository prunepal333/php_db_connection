<?php
    interface SQLQueryBuilder
    {
        public function select(string $table, array $fields): SQLQueryBuilder;
        public function where(string $field, string $value, string $operator): SQLQueryBuilder;
        public function limit(int $start, int $offset): SQLQueryBuilder;

        public function insert(string $table, array $assoc_field): SQLQueryBuilder;
        public function update(string $table, array $assoc_field): SQLQueryBuilder;
        public function delete(string $table): SQLQueryBuilder;
        public function getQueryString(): string;

        /**
         * @return Associative-Array
         * @return_format array('query' => 'string_value', 
         *                      'params' => array('value1', 'value2', 'value3')
         * )
         * @return_value_example array(
         *              'query' => 'SELECT username, password_hash, salt FROM `user` WHERE user_id = ?,
         *              'params' => array('10090402330'))
         */
        public function getQuery(): array;
    }
?>