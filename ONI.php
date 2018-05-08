<?php
declare(strict_types=1);
/**
 * PHP Port of the ONI.rb Toolkit for Ruby.
 * Please note that this is intended for at least PHP7.
 * 
 * @author Sepheus
 * @copyright 2018
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>
 */
 
 class ByteArray extends ArrayIterator {
    private $offset = 1;
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
            case "array":
                assert(gettype(current($type)) == "integer", "Unsupported datatype.");
                foreach($type as $k => $v) {
                    $this->bytes[$k+$this->offset] = $v;
                }
                break;
            default:
                assert(0, "Unsupported datatype");
        }
        $this->position = $this->offset;
    }

    public function rewind() : void {
        $this->position = $this->offset;
    }

    public function current() : int {
        return $this->bytes[$this->position];
    }

    public function key() : int {
        return $this->position - $this->offset;
    }

    public function next() : void {
        ++$this->position;
    }

    public function valid() : bool {
        return isset($this->bytes[$this->position]);
    }
    
    public function offsetGet($index) : int {
        return $this->bytes[$index + $this->offset];
    }
    
    public function offsetSet($index, $newval) : void {
        $this->bytes[$index + $this->offset] = $newval & 0xFF;
    }
    
    public function &iterate() : Iterator {
        foreach($this->bytes as &$v) {
            yield $v;
        }
    }
    
    public function count() : int {
        return count($this->bytes);
    }

    public function __toString() : string {
        return join(
            array_map(
                "chr",
                $this->bytes
        ));
    }
 }
 
 final class ONI extends ByteArray {  
    private function _rot(int &$byte, int $n) : void {
        $n = abs($n % 26);
        if($byte > 64 && $byte < 91) { $byte = ((($byte - 65) + $n) % 26) + 65; }
        else if($byte > 96 && $byte < 123) { $byte = ((($byte - 97) + $n) % 26) + 97; }
        $byte &= 0xFF;
    }
    
    public function rot(int $n) : ONI  {
        $output = new ONI($this);
        foreach($output->iterate() as &$byte) {
            $output->_rot($byte, $n);
        }
        return $output;
    }
    
    public function shift_c(int $n) : ONI {
        $output = new ONI($this);
        foreach($output->iterate() as &$byte) {
            $byte = ($byte + $n) & 0xFF;
        }
        return $output;
    }

    public function shift_p($pattern) : ONI {
        $output = new ONI($this);
        $_pattern = new ByteArray($pattern);
        $patternLength = count($_pattern);
        assert($patternLength > 0, "Empty patterns are not allowed.");
        $i = 0;
        foreach($output->iterate() as &$byte) {
            $byte = ($byte - $_pattern[$i % $patternLength]) & 0xFF;
            $i++;
        }
        return $output;
    }

    public function shift_k($pattern) : ONI {
        return $this->shift_p($pattern);
    }
    
    public function decrypt(string $key) : ONI {
        $_key = new ByteArray($key);
        $keyLength = count($_key);
        $size = count($this) >> 1;
        $output = new ONI($size);
        $i = 0;
        foreach($output->iterate() as &$decrypted) {
            $k = $_key[$i % $keyLength];
            $b = $this[1 + ($i << 1)] & 1;
            $decrypted = ((($this[$i << 1] + ($k & 1)) << 1) - $b - $k) & 0xFF;
            $i++;
        }
        return $output;
    }
    
    public function encrypt(string $key) : ONI {
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
    
    public function reverse() : ONI {
        return new ONI(strrev($this->toString()));
    }

    public function encode64() : ONI {
        return new ONI(base64_encode($this->toString()));
    }

    public function decode64() : ONI {
        return new ONI(base64_decode($this->toString()));
    }
    
    public function toString() : string {
        return $this->__toString();
    }
    
    public function toHex(string $sep = "-") : ONI {
        $hex = join(
                    array_map(
                        "sprintf",
                        array_fill(0, count($this), "%02X"),
                        iterator_to_array($this)
                    ), 
                    $sep
                );
        return new ONI($hex);
    }
    
 }
?>