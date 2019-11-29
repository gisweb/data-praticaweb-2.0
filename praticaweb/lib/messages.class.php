<?php
require_once LIB.'messages.class.php';
class messages extends generalMessages{
    static $localMessages=Array(
        "AVVERTIMENTO_PROTOCOLLO_USCITA"=>"111"
    );
    static function initMessages(){
        foreach(self::$localMessages as $k=>$v){
            print "$k : $v\n";
            self::$messages[$k] = $v;
        }
        return $self::$messages;
    }
}
?>
