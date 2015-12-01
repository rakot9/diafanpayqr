<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PayqrModuleDb 
{
    public static function getUserTable()
    {
        return PayqrModuleDbConfig::$prefix . "payqr_user";
    }
    
    public static function getInvoiceTable()
    {
        return PayqrModuleDbConfig::$prefix . "payqr_invoice";
    }
    
    public static function getLogTable()
    {
        return PayqrModuleDbConfig::$prefix . "payqr_log";
    }

    private static $instance;
    
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }        
        return static::$instance;
    }
    protected function connect() {
            return new mysqli(PayqrModuleDbConfig::$host, PayqrModuleDbConfig::$username, PayqrModuleDbConfig::$password, PayqrModuleDbConfig::$database);
    }
    public function query($query) {
            $db = $this->connect();
            $result = $db->query($query);

            if($result->num_rows == 1)
            {
                $results = $result->fetch_object();
            }            
            elseif($result->num_rows == 0)
            {
                $results = FALSE;
            }
            else
            {                
                while ($row = $result->fetch_object()) {
                        $results[] = $row;
                }
            }

            return $results;
    }
    public function multiQuery($query)
    {
        $db = $this->connect();
        $result = $db->multi_query($query);
        if($db->errno != 0)
        {
            echo $db->error;
        }
        return $result;
    }

    public function insert($table, $data, $format) {
            // Check for $table or $data not set
            if ( empty( $table ) || empty( $data ) ) {
                    return false;
            }

            // Connect to the database
            $db = $this->connect();

            // Cast $data and $format to arrays
            $data = (array) $data;
            $format = (array) $format;

            // Build format string
            $format = implode('', $format); 
            $format = str_replace('%', '', $format);

            list( $fields, $placeholders, $values ) = $this->prep_query($data);

            // Prepend $format onto $values
            array_unshift($values, $format); 
            // Prepary our query for binding
            $stmt = $db->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");
            // Dynamically bind values
            call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

            // Execute the query
            $r = $stmt->execute();

            // Check for successful insertion
            if ( $stmt->affected_rows ) {
                    return $stmt->insert_id;
            }

            return false;
    }
    public function update($table, $data, $format, $where, $where_format) {
            // Check for $table or $data not set
            if ( empty( $table ) || empty( $data ) ) {
                    return false;
            }

            // Connect to the database
            $db = $this->connect();

            // Cast $data and $format to arrays
            $data = (array) $data;
            $format = (array) $format;

            // Build format array
            $format = implode('', $format); 
            $format = str_replace('%', '', $format);
            $where_format = implode('', $where_format); 
            $where_format = str_replace('%', '', $where_format);
            $format .= $where_format;

            list( $fields, $placeholders, $values ) = $this->prep_query($data, 'update');

            //Format where clause
            $where_clause = '';
            $where_values = '';
            $count = 0;

            foreach ( $where as $field => $value ) {
                    if ( $count > 0 ) {
                            $where_clause .= ' AND ';
                    }

                    $where_clause .= $field . '=?';
                    $where_values[] = $value;

                    $count++;
            }
            // Prepend $format onto $values
            array_unshift($values, $format);
            $values = array_merge($values, $where_values);
            
            // Prepary our query for binding
            $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");

            // Dynamically bind values
            call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

            // Execute the query
            $stmt->execute();

            // Check for successful insertion
            if ( $stmt->affected_rows ) {
                    return true;
            }

            return false;
    }
    
    /**
     * 
     * @param type $query
     * @param type $data
     * @param type $format
     * @return type
     * 
     * http://php.net/manual/ru/mysqli-stmt.bind-param.php описание параметров
     */
    public function select($query, $data, $format) {
        $results = array();
            // Connect to the database
            $db = $this->connect();

            //Prepare our query for binding
            //var_dump($query);exit;
            $stmt = $db->prepare($query);

            //Normalize format
            $format = implode('', $format);

            // Prepend $format onto $values
            array_unshift($data, $format);

            //Dynamically bind values
            call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));

            //Execute the query
            $stmt->execute();


//            //Fetch results
//            $result = $stmt->get_result();
//            
//            if($result->num_rows == 1)
//            {
//                $results = $result->fetch_object();
//            }
//            else
//            {                
//                while ($row = $result->fetch_object()) {
//                        $results[] = $row;
//                }
//            }
//            return $results;
            
        $stmt->store_result();
        $result = array();
        while($assoc_array = $this->fetchAssocStatement($stmt))
        {
            $result[] = $assoc_array;
        }
        if(count($result) == 1)
        {
            $result = $result[0];
        }
        $stmt->close();
        $result = json_decode(json_encode($result));
        return $result;
    }
    
    function fetchAssocStatement($stmt)
    {
        if($stmt->num_rows>0)
        {
            $result = array();
            $md = $stmt->result_metadata();
            $params = array();
            while($field = $md->fetch_field()) {
                $params[] = &$result[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $params);
            if($stmt->fetch())
                return $result;
        }

        return null;
    }
    
    public function delete($table, $id) {
            // Connect to the database
            $db = $this->connect();

            // Prepary our query for binding
            $stmt = $db->prepare("DELETE FROM {$table} WHERE ID = ?");

            // Dynamically bind values
            $stmt->bind_param('d', $id);

            // Execute the query
            $stmt->execute();

            // Check for successful insertion
            if ( $stmt->affected_rows ) {
                    return true;
            }
    }
    private function prep_query($data, $type='insert') {
            // Instantiate $fields and $placeholders for looping
            $fields = '';
            $placeholders = '';
            $values = array();

            // Loop through $data and build $fields, $placeholders, and $values			
            foreach ( $data as $field => $value ) {
                    $fields .= "{$field},";
                    $values[] = $value;

                    if ( $type == 'update') {
                            $placeholders .= $field . '=?,';
                    } else {
                            $placeholders .= '?,';
                    }

            }

            // Normalize $fields and $placeholders for inserting
            $fields = substr($fields, 0, -1);
            $placeholders = substr($placeholders, 0, -1);

            return array( $fields, $placeholders, $values );
    }
    private function ref_values($array) {
            $refs = array();
            foreach ($array as $key => $value) {
                    $refs[$key] = &$array[$key]; 
            }
            return $refs; 
    }
}