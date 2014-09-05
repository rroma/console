<?php

namespace Dba;

class Dba implements \Iterator
{
    protected $dbPath;
    protected $mode;
    protected $db;
    protected $curKey;
    
    public function __construct($dbPath, $mode)
    {
        $this->dbPath = $dbPath;
        $this->mode = $mode;
    }
    
    protected function open()
    {
        $this->db = dba_open($this->dbPath, $this->mode);
        if (!$this->db) {
           throw new \RuntimeException("Connection to {$this->dbPath} failed"); 
        }
        
        $this->curKey = dba_firstkey($this->db);
        
        return $this;
    }
    
    public function close()
    {
        if ($this->dbaId) {
            dba_close($this->db);
        }
        
        return $this;
    }
    
    public function fetch($key)
    {
        if (!$this->db) {
            $this->open();
        }
        
        return dba_fetch($key, $this->db);
    }
    
    public function insert($key, $value)
    {
        if (!$this->db) {
            $this->open();
        }
        
        return dba_insert($key, $value, $this->db);
    }
    
    public function delete($key)
    {
        if (!$this->db) {
            $this->open();
        }
        
        return dba_delete($key, $this->db);
    }
    
    public function hasKey($key)
    {
        if (!$this->db) {
            $this->open();
        }
        
        return dba_exists($key, $this->db);
    }
    
    public function replace($key, $value)
    {
        if (!$this->db) {
            $this->open();
        }
        
        return dba_replace($key, $value, $this->db);
    }
  
 //####### Iterator methods ######### 
 
    public function rewind() {
        if (!$this->db) {
            $this->open();
        }

        $this->curKey = dba_firstkey($this->db);
    }
    
    public function current() {
        return $this->fetch($this->curKey);
    }
    
    public function key() {
        if (!$this->curKey) {
            $this->rewind();
        }

        return $this->curKey;
    }
    
    public function next() {
        if (!$this->db) {
            $this->open();
        }

        $this->curKey = dba_nextkey($this->db);
    }
    
    public function valid() {
        return $this->hasKey($this->curKey);
    }
}

