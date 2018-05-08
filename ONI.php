<?php

/**
 * @author Sepheus
 * @copyright 2018
 */
 
 class ByteArray extends ArrayIterator {
    private $position = 1;
    private $bytes;

    public function __construct($type) {
        switch(gettype($type)) {
            case "string":
                $this->bytes = unpack("C*", $type);
                break;
            case "integer":
                $this->bytes =  unpack("C*", str_repeat("\x00", $type));
                break;
            case "object":
                if(get_parent_class($type) == "ByteArray") {
                    $this->bytes = $type->bytes;
                }
                break;
            default:
                assert(0);
        }
        $this->position = 1;
    }

    public function rewind() {
        $this->position = 1;
    }

    public function current() {
        return $this->bytes[$this->position];
    }

    public function key() {
        return $this->position-1;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->bytes[$this->position]);
    }
    
    public function offsetGet($index) {
        return $this->bytes[$index+1];
    }
    
    public function offsetSet($index, $newval) {
        $this->bytes[$index+1] = $newval & 0xFF;
    }
    
    public function count() {
        return count($this->bytes);
    }
 }
 
 class ONI extends ByteArray {  
    private function _rot($byte, $n) {
        $n %= 26;
        if($byte > 64 && $byte < 91) { return ((($byte - 65) + $n) % 26) + 65; }
        else if($byte > 96 && $byte < 123) { return ((($byte - 97) + $n) % 26) + 97; }
        return $byte;
    }
    
    public function rot($n) {
        $output = new ONI($this);
        foreach($this as $index => $byte) {
            $output[$index] = $output->_rot($byte, $n);
        }
        return $output;
    }
    
    public function shift_c($n) {
        $output = new ONI($this);
        foreach($this as $index => $byte) {
            $output[$index] = ($byte + $n);
        }
        return $output;
    }
    
    public function decrypt($key) {
        $_key = new ByteArray($key);
        $keyLength = count($_key);
        $size = count($this) >> 1;
        $output = new ONI($size);
        for($i = 0; $i < $size; $i++) {
            $k = $_key[$i % $keyLength];
            $b = $this[1 + ($i << 1)] & 1;
            $output[$i] = (($this[$i << 1] + ($k & 1)) << 1) - $b - $k;
        }
        return $output;
    }
    
    public function encrypt($key) {
        $_key = new ByteArray($key);
        $keyLength = count($_key);
        $size = count($this);
        $output = new ONI($size << 1);
        for($i = 0; $i < $size; $i++) {
            $k = $_key[$i % $keyLength];
            $d = $this[$i];
            $a = $k & 1;
            $b = $d & 1;
            $output[$i << 1] = ($d + $k + (!$a&$b) - ($a&$b)) >> 1;
            $output[1 + ($i << 1)] = (rand() & 0xFE | ($a^$b));
        }
        return $output;        
    }
    
    public function reverse() {
        return new ONI(strrev($this->toString()));
    }
    
    public function toString() {
        return join(
            array_map("chr",
            iterator_to_array($this))
        );
    }
    
    public function toHex($sep = "-") {
        return join(
            array_map("sprintf",
            array_fill(0, count($this), "%02X"),
            iterator_to_array($this)), $sep
        );
    }
    
 }
?>