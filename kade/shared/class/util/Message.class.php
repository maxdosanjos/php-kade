<?php
/**
 * Message
 * Classe que gerencia mensagens trocadas pela aplicação e a interface grafica
 */
class Message {
    private $type = null;
    private $desc = null;
    const ERR      = "error";
    const SUCCESS  = "sucess";
    const WARN  = "warn";
    const INFO  = "info";
    
    public function __construct($desc='',$type=0) {
    	$this->setType($type);
    	$this->setDesc($desc);
    	
    }
    
    public function getDesc(){
    	return $this->desc;
    }
    
    public function setDesc($desc){
    	$desc = trim($desc ?? "");
    	$this->desc = nl2br($desc);
    	
    }
    
    public function getType(){
    	return $this->type;
    }
    
    public function setType($type){
    	$this->type = trim($type ?? "");
    }
    
    public function __destruct(){
    	$this->type = null;
    	$this->desc = null;
    }
}
?>