<?php


//!BOM á
/*AUTOR:ERICK BRISEÑO
 *erick1saradmon@gmail.com
 *+525565770551
 *Class GenValidator
 *LIBRE DISTRIBUCION, USO Y MODIFICACION
 *SIEMPRE Y CUANDO SE MANTENGA ESTA LEYENDA COMPLETA E INTEGRA BAJO CUALQUIER CIRCUNSTANCIA
 *USO
 *$rules='Type(clabebanco)|Required(true)|Range([18:18])';
 *$GenValidator->isValidStr($rules,$value,'NOMBREDEVARIABLE');
 *$rules='decDigits([0:0])|Required(true)|Range([0:10000000])';
 *$GenValidator->isValidDigits($rules,$value,'NOMBREDEVARIABLE');
 *$rules='Format(yyyy-mm-dd)|Required(true)|Range([-60:1])';
 *$GenValidator->isValidDate($rules,$value,'NOMBREDEVARIABLE');
 */
class GenValidator{

    private $MAX_ARRAY_ALLOW = 1000;
    private $MAX_ARRAY_DEPTH_ALLOW = 50;
    private $MAX_FUNCTION_DEPTH_ALLOW = 50;

    /**
     *
     */
    function __construct() {

    }


    /*** IN mixed (any value),OUT (string)maxDepth ***/
    /*** Retorna la profundidad maxima de un array ***/
    public function getArrayMaxDepth($_input) {

        if ( !$this->canVarLoop( $_input ) ) {
            return "0";
        }
        $ArrayIter = new RecursiveArrayIterator($_input);
        $Iteriter  = new RecursiveIteratorIterator($ArrayIter);
        foreach ( $Iteriter as $_value ) {
            //getDepth() start is 0, I use 0 for not iterable values
            $_depth       = $Iteriter->getDepth() + 1;
            $_arr_depth[] = $_depth;
        }

        return max( $_arr_depth );
    }

    /*** IN mixed (any value),OUT (bool)true/false, CHECK if can be used by foreach ***/
    /*** Revisa si puede ser iterado con foreach ***/
    public function canVarLoop($_input) {

        return (is_array( $_input ) || $_input instanceof Traversable) ? true : false;
    }

    /*** IN input (any value),OUT (bool)true/false, CHECK structure $var[][]='string'
    ¡¡NOT ACCEPT var[]=='string' OR var[][]=='array'!! ***/
    /*** Revisa la estructura de array (util cuando recibes array desde el usuario) ***/
    public function isValidLevelArray($_input , $_depth) {

        if ( !$this->isValidNumericRange( $_depth , '[1:2]' ) ) {
            return false;
        }
        if ( !$this->canVarLoop( $_input ) ) {
            return false;
        }
        foreach ( $_input as $_value ) {
            if ( $_depth >= 2 ) {
                if ( !$this->canVarLoop( $_value ) ) {
                    return false;
                }
                foreach ( $_value as $_value2 ) {
                    if ( $this->canVarLoop( $_value2 ) ) {
                        return false;
                    }
                }
            } else {
                if ( $this->canVarLoop( $_value2 ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /*** WHITE LIST >>***/
    public function isWhiteListText($_string , $_pattern) {

        return preg_match( '/^[^' . $_pattern . ']+$/u' , $_string ) ? false : true;
    }

    /**
     * @param $_string
     * @param $_pattern
     *
     * @return bool
     */
    public function hasBlackListText($_string , $_pattern) {

        return preg_match( '/[' . $_pattern . ']+/u' , $_string ) ? true : false;
    }

    /**
     * @param     $_string
     * @param     $_pattern
     * @param int $_depth
     *
     * @return mixed|string
     */
    public function doForceRemoveBlackListText($_string , $_pattern , $_depth = 0) {

        if ( !is_string( $_string ) or !is_string( $_pattern ) ) {
            return 'error_arguments';
        }
        if ( $_depth > $this->MAX_FUNCTION_DEPTH_ALLOW ) {
            return 'error_depth';
        }
        $_new_string = preg_replace( '/[' . $_pattern . ']+/u' , "" , $_string );
        if ( $this->hasBlackListText( $_new_string , $_pattern ) ) {
            $_depth++;
            $_new_string = $this->doForceRemoveBlackListText( $_new_string , $_pattern , $_depth );
        }

        return $_new_string;
    }

    /**
     * @param     $_string
     * @param     $_pattern
     * @param int $_depth
     *
     * @return mixed|string
     */
    public function doForceWhiteListText($_string , $_pattern , $_depth = 0) {

        if ( !is_string( $_string ) or !is_string( $_pattern ) ) {
            return 'error_arguments';
        }
        if ( $_depth > $this->MAX_FUNCTION_DEPTH_ALLOW ) {
            return 'error_depth';
        }
        $_white_string = preg_replace( '/[^' . $_pattern . ']+/u' , "" , $_string );
        if ( !$this->isWhiteListText( $_white_string , $_pattern ) ) {
            $_depth++;
            $_white_string = $this->doForceWhiteListText( $_white_string , $_pattern , $_depth );
        }

        return $_white_string;
    }

    /*** WHITE LIST <<***/
    public function hasRegExpValue($_pattern , $_value) {

        if ( !$this->isValidUTF8( $_value ) ) {
            return false;
        }
        if ( preg_match( $_pattern , $_value ) ) {
            return true;
        }

        return false;
    }

    /**
     * @param     $_pattern
     * @param     $_value
     * @param int $_depth
     *
     * @return bool|mixed
     * ?<=\$this\->doError\(.+\'
     */
    public function doForceRemoveRegExp($_pattern , $_value , $_depth = 0) {

        if ( $_depth > $this->MAX_FUNCTION_DEPTH_ALLOW ) {
            echo "'BAD_STRING' , $_value";

            return false;
        }
        $_value = preg_replace( $_pattern , '' , $_value );
        if ( $this->hasRegExpValue( $_pattern , $_value ) ) {
            $_depth++;
            $_value = $this->doForceRemoveRegExp( $_pattern , $_value , $_depth );
        }

        return $_value;
    }

    /**
     * @param $_definitions
     *
     * @return bool
     */
    private function doExplodeStrDefinitions($_definitions) {

        if ( !$this->isValidUTF8( $_definitions ) ) {
            return false;
        }
        if ( !preg_match( "/^[a-zA-Z0-9\(\)\!\:\[\] ]+|$/" , $_definitions ) ) {
            echo "'badDefinition' , $_definitions";

            return false;
        }
        $_definitions     = $this->doForceRemoveRegExp( '/[\p{Z}\p{C}]/u' , $_definitions );
        $_arr_definitions = explode( '|' , $_definitions );
        if ( count( $_arr_definitions ) != 3 ) {
            echo "'badDefinitions' , $_definitions";

            return false;
        }
        foreach ( $_arr_definitions as $_definition ) {
            $_definition=strtolower($_definition);
            //'SubType(Full)|Required(true)|Range(![0:])'
            if ( substr( $_definition , 0 , 8 ) == 'required' ) {
                $_definition             = str_replace( array( 'required(' , ')' ) , "" , $_definition );
                $_arr_result['required'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 4 ) == 'type' ) {
                $_definition            = str_replace( array( 'type(' , ')' ) , "" , $_definition );
                $_arr_result['type'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 5 ) == 'range' ) {
                $_definition          = str_replace( array( 'range(' , ')' ) , "" , $_definition );
                $_arr_result['range'] = $_definition;
                continue;
            }
        }
        if ( !isset($_arr_result['type']) or
            !isset($_arr_result['required']) or
            !isset($_arr_result['range'])
        ) {
            echo "'BAD_STR_DEFINITIONS' , $_definitions";

            return false;
        }

        return $_arr_result;
    }

    //'Type(String)|SubType(Full)|Required(true)|Range(![0:])')
    /*** PATRONES PSRE >>***/
    private function getStrRegEx($sub_type) {
        $sub_type=strtolower($sub_type);
        $MinMay       = 'A-Za-z';
        $MinMayNum    = 'A-Za-z0-9';
        $Latin1Cuotes = 'áéíóúÁÉÍÓÚüÜñÑ';
        $BasicSymbols   = '\¡\!\¿\?\;\,\:\.';
        $MathSymbols    = '\=\+\-\#';
        $OtherSymbols   = '°\~\@\*';
        $Escapes        = '\/\(\)\[\]\{\}\|\^';
        $MoneySymbols   = '\€\$';
        $EscapeOthers   = '\©\®\¼\½\¾';
        $SeveralMeaning = '\'\"\\';
        $MysqlMeaning   = '\%\_';
        $HtmlMeaning    = '\<\>';
        $AllSpaces      = '\s';
        $Space          = ' ';
        $BasicLatin1  = $MinMayNum . $Latin1Cuotes;
        $BasicWords  = $BasicLatin1 . $Space;
        switch ( $sub_type ) {
            case 'process':
                return "/^[" . $MinMayNum . "\-\_]+$/";
                break;
            case 'alpha':
                return "/^[" . $MinMay . "]+$/";
                break;
            case 'alphanum':
                return "/^[" . $MinMayNum . "]+$/";
                break;
            case 'alphalatin1':
                return "/^[" . $BasicLatin1 . "]+$/";
                break;
            case 'basicwords':
                return "/^[" . $BasicWords . "]+$/";
                break;
            case 'basicwords2':
                return "/^[" . $BasicWords . "\-]+$/";
                break;
            case 'basictext':
                return "/^[" . $BasicLatin1 . $Space . $BasicSymbols . "]+$/";
                break;
            case 'bancaccount':
                return "/^[0-9\-" . $Space . "]+$/";
                break;
            case 'clabebanco':
                return "/^[0-9]+$/";
                break;
            case 'referenciabanco':
                return '/^[' . $BasicWords . '\-\.]+$/';
                break;
            case 'email':
                return '/^[0-9a-z][0-9a-z\-\_\.]*[0-9a-z]*@[0-9a-z]+\.[a-z]+(\.[a-z]+)?$/';
                break;
            case 'full':
                return "//u";
                break;
            case 'sesion':
                return "/^[" . $MinMayNum . "]{100}$/";
                break;
            case 'password':
                return '/^((?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z0-9\-\_\@]+)$/';
                break;


            default:
                echo "'UNDEFINED_PATTERN' , $sub_type";

                return false;
                break;
        }
    }
    /*** PATRONES PSRE <<***/


    /**
     * @param $_value
     *
     * @return mixed
     */
    public function doRemoveSpaces($_value) {

        return preg_replace( '/\p{Z}+/u' , '' , $_value );
    }

    /**
     * @param $_value
     *
     * @return mixed
     */
    public function doSingleSpaces($_value) {

        return preg_replace( '/\p{Z}+/u' , ' ' , $_value );
    }

    /**
     * @param $_definitions
     * @param $_value
     *
     * @return bool
     */
    public function isValidStr($_definitions , $_value , $name_value="") {

        $_arr_definitions = $this->doExplodeStrDefinitions( $_definitions );
        if ( !$_arr_definitions ) {
            return false;
        }
        $_required = $_arr_definitions['required'];
        if ( $this->isValidRequired( $_required , $_value , $name_value) === false ) {
            return false;
        }
        if ( $this->isValidRequired( $_required , $_value , $name_value) === true){
            return true;
        }
        if ( !$this->isValidUTF8( $_value , $name_value) ) {
            return false;
        }
        $_sub_type = $_arr_definitions['type'];
        if ( !$this->isValidStrPatt( $_sub_type , $_value , $name_value) ) {
            return false;
        }
        $_range = $_arr_definitions['range'];
        if ( !$this->isValidStrRange( $_range , $_value , $name_value) ) {
            return false;
        }

        return true;
    }

    /**
     * @param $_definedPatt
     * @param $_value
     *
     * @return bool
     */
    public function isValidStrPatt($_definedPatt , $_value , $name_value="") {

        if ( !$this->isValidUTF8( $_value ) ) {
            return false;
        }
        $_pattern = $this->getStrRegEx( $_definedPatt );
        if ( !$_pattern ) {
            return false;
        }
        if ( !preg_match( $_pattern , $_value ) ) {
            echo "'VALUE_DONT_MATCH_IN_PATERN_'.$_definedPatt , $_value, $name_value";
            return false;
        }

        return true;
    }

    /**
     * @param $_range
     * @param $_value
     *
     * @return bool
     */
    public function isValidStrRange($_range , $_value , $name_value="") {

        if ( !$this->isValidUTF8( $_value ) ) {
            return false;
        }
        if ( !$this->isValidRangeStruc( $_range ) ) {
            return false;
        }
        $_range = str_replace( array( '!' , '[' , ']' , '-' ) , "" , $_range );
        $_range = explode( ':' , $_range );
        if ( ($_range['0'] > $_range['1']) or  $_range['0'] < 0 ) {
            echo "'BAD_STRING_RANGE' , $_value, $name_value";

            return false;
        }
        if ( (mb_strlen( $_value , 'UTF-8' ) < $_range['0']) or
            (mb_strlen( $_value , 'UTF-8' ) > $_range['1'])
        ) {
            echo "'NOT_IN_STRING_RANGE' , $_value, $name_value";

            return false;
        }

        return true;
    }

    /**
     * @param $_value
     *
     * @return bool
     */
    public function isValidUTF8($_value , $name_value="") {

        if ( !$this->isString( $_value , $name_value) ) {
            return false;
        }
        if ( !preg_match( '//u' , $_value ) ) {
            echo "'MALFORMED_UTF8' , $_value, $name_value";

            return false;
        }
        if ( !preg_match( '/^[\x{0000}-\x{D7FF}]+$/u' , $_value ) ) {
            if ( $_value != "" ) {
                echo "'OUT_OF_UNICODE_RANGE' , $_value, $name_value";

                return false;
            }
        }

        return true;
    }



    /**
     * @param $_definitions
     * @param $_value
     *
     * @return bool
     */
    public function isValidNumeric($_definitions , $_value , $name_value="") {

        $_arr_definitions = $this->doExplodeNumDefinitions( $_definitions );
        if ( !$_arr_definitions ) {
            return false;
        }
        $_required = $_arr_definitions['required'];
        if ( $this->isValidRequired( $_required , $_value , $name_value) === false ) {
            return false;
        }
        if ( $this->isValidRequired( $_required , $_value , $name_value) === true){
            return true;
        }
        if( !$this->isNumeric($_value)){
            return false;
        }

        $dec_digits = $_arr_definitions['decdigits'];

        if ( !$this->isValidDigits( $dec_digits , $_value , $name_value) ) {
            return false;
        }

        $_range = $_arr_definitions['range'];
        if ( !$this->isValidNumericRange( $_range , $_value , $name_value) ) {
            return false;
        }

        return true;
    }


    private function doExplodeNumDefinitions($_definitions) {

        if ( !$this->isValidUTF8( $_definitions ) ) {
            return false;
        }
        if ( !preg_match( "/^[a-zA-Z0-9\(\)\!\:\[\] ]+|$/" , $_definitions ) ) {
            echo "'badDefinition' , $_definitions";

            return false;
        }
        $_definitions     = $this->doForceRemoveRegExp( '/[\p{Z}\p{C}]/u' , $_definitions );
        $_arr_definitions = explode( '|' , $_definitions );
        if ( count( $_arr_definitions ) != 3 ) {
            echo "'badDefinitions' , $_definitions";

            return false;
        }
        foreach ( $_arr_definitions as $_definition ) {
            $_definition=strtolower($_definition);
            if ( substr( $_definition , 0 , 8 ) == 'required' ) {
                $_definition             = str_replace( array( 'required(' , ')' ) , "" , $_definition );
                $_arr_result['required'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 9 ) == 'decdigits' ) {
                $_definition            = str_replace( array( 'decdigits(' , ')' ) , "" , $_definition );
                $_arr_result['decdigits'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 5 ) == 'range' ) {
                $_definition          = str_replace( array( 'range(' , ')' ) , "" , $_definition );
                $_arr_result['range'] = $_definition;
                continue;
            }
        }
        if ( !isset($_arr_result['required']) or
            !isset($_arr_result['decdigits']) or
            !isset($_arr_result['range'])
        ) {
            echo 'badDefinitions';

            return false;
        }

        return $_arr_result;
    }
    /**
     * @param $str
     *
     * @return bool
     */
    public function isNumeric($str) {
        return (bool)preg_match( "/^[\-+]?[0-9]+(\.[0-9]+)?$/" , $str );
    }

    private function isValidDigits($_digits_range , $_num , $name_value=""){
        //SIMBOLO SEPARADOR DE DECIMALES.
        $num=explode('.',$_num);
        //SIMBOLO SEPARADOR DE DECIMALES.
        If(count($num)>2){
            echo "wrong_number_format , {$_num}, {$name_value}";
            return false;
        }
        if(isset($num[1])){
            $num_digits=strlen($num[1]);
        }else{
            $num_digits=0;
        }

        if(!$this->isValidRangeStruc($_digits_range)){
            echo "'bad_range_struct' , {$_digits_range}";
            return false;
        }

        $_range = str_replace( array( '!' , '[' , ']' , '-' ) , "" , $_digits_range );
        $_range = explode( ':' , $_range );
        if ( ($_range['0'] > $_range['1']) or  $_range['0'] < 0 ) {
            echo "'bad_digits_range' , $_digits_range, $name_value";
            return false;
        }

        if($num_digits < $_range['0'] or $num_digits > $_range['1']){
            echo "do_not_have_digits_required' , $_num, $name_value";
            return false;
        }

        return true;
    }


    /**
     * @param $_value
     * @param $_range
     *
     * @return bool|string
     */
    public function isValidNumericRange($_range,$_value , $name_value="") { //tested +++
        $_exclude   = (substr( $_range , 0 , 1 ) == '!') ? true : false;
        $_range     = str_replace( array( '!' , '[' , ']' ) , "" , $_range );
        $_arr_range = explode( ':' , $_range );
        if ( $_arr_range['0'] > $_arr_range['1'] ) {
            echo "WRONG_RANGE' , $_range";
            return false;
        }
        if ( $_exclude ){
            if($_value < $_arr_range['0'] or $_value > $_arr_range['1']){
                return true;
            }else{
                echo "'IS_IN_EXLUSION_RANGE' , $_value, $name_value";
                return false;
            }
        }

        if($_arr_range['0'] <= $_value  and $_arr_range['1'] >= $_value){
            return true;
        }else{
            echo "IS_OUT_OF_RANGE' , $_value, $name_value";
            return false;
        }
    }


    public function isValidDate($_definitions , $_value , $name_value="") {

        $_arr_definitions = $this->doExplodeDateDefinitions( $_definitions );
        if ( !$_arr_definitions ) {
            return false;
        }
        $_required = $_arr_definitions['required'];
        if ( $this->isValidRequired( $_required , $_value , $name_value) === false ) {
            return false;
        }
        if ( $this->isValidRequired( $_required , $_value , $name_value) === true){
            return true;
        }
        $format = $_arr_definitions['format'];
        if( !$this->existsDate($format,$_value, $name_value)){
            return false;
        }
        $_range = $_arr_definitions['range'];
        $type_range=$this->whichDateRangeStrucIs($format,$_range);
        if($type_range==false){
            return false;
        }
        if($type_range=='DINAMIC' and !$this->isValidDateRangeDinamic($format,$_value,$_range, $name_value)){
            return false;
        }
        if($type_range=='STATIC' and !$this->isValidDateRange($format,$_value,$_range, $name_value)){
            return false;
        }

        return true;
    }


    private function doExplodeDateDefinitions($_definitions) {

        if ( !$this->isValidUTF8( $_definitions ) ) {
            echo "'badDefinition1' , $_definitions";
            return false;
        }
        if ( !preg_match( "/^[a-zA-Z0-9\(\)\!\:\[\] ]+|$/" , $_definitions ) ) {
            echo "'badDefinition2' , $_definitions";

            return false;
        }
        $_definitions     = $this->doForceRemoveRegExp( '/[\p{Z}\p{C}]/u' , $_definitions );
        $_arr_definitions = explode( '|' , $_definitions );
        if ( count( $_arr_definitions ) != 3 ) {
            echo "'badDefinitions3' , $_definitions";

            return false;
        }

        foreach ( $_arr_definitions as $_definition ) {
            $_definition=strtolower($_definition);
            if ( substr( $_definition , 0 , 8 ) == 'required' ) {
                $_definition             = str_replace( array( 'required(' , ')' ) , "" , $_definition );
                $_arr_result['required'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 6 ) == 'format' ) {
                $_definition            = str_replace( array( 'format(' , ')' ) , "" , $_definition );
                $_arr_result['format'] = $_definition;
                continue;
            }
            if ( substr( $_definition , 0 , 5 ) == 'range' ) {
                $_definition          = str_replace( array( 'range(' , ')' ) , "" , $_definition );
                $_arr_result['range'] = $_definition;
                continue;
            }
        }

        if ( !isset($_arr_result['required']) or
            !isset($_arr_result['format']) or
            !isset($_arr_result['range'])
        ) {
            echo "'badDefinitions' , $_definitions";

            return false;
        }

        return $_arr_result;
    }

    private function isValidPatternDate($_format , $_recibed_date , $name_value="") {

        switch ( $_format ) {
            case 'dd-mm-yyyy':
                return (preg_match( '/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/i' , $_recibed_date ) === 0) ? false : true;
                break;
            case 'mm/dd/yyyy':
                return (preg_match( '/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i' , $_recibed_date ) === 0) ? false : true;
                break;
            case 'yyyy-mm-dd':
                return (preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{1,2}$/i' , $_recibed_date ) === 0) ? false : true;
                break;
        }
        echo "'UNDEFINED_DATE_PATTERN' , $_format";
        return false;
    }

    private function getDateTimeFormat($_format){
        switch ( $_format ) {
            case 'dd-mm-yyyy':
                return 'd-m-Y h:i:s';
                break;
            case 'mm/dd/yyyy':
                return 'm/d/Y h:i:s';
                break;
            case 'yyyy-mm-dd':
                return 'Y-m-d h:i:s';
                break;
        }
        echo "'UNDEFINED_DATE_FORMAT' , $_format";
        return false;
    }

    private function existsDate($_format , $_recibed_date , $name_value="") {
        //exit($name_value);
        if ( !$this->isValidPatternDate( $_format , $_recibed_date ) ) {
            echo 'BAD_DATE' , $_recibed_date , $name_value;
            return false;
        }
        $_format = $this->getDateTimeFormat($_format);

        $date = DateTime::createFromFormat( $_format , $_recibed_date . ' 00:00:00' );
        $date2=$date->format("Y-m-d");

        if(!$date){
            echo "'WRONG_DATE' , $_recibed_date  , $name_value";
            return false;
        }else{
            if($date2 != $_recibed_date){
                echo "'WRONG_DATE' , $_recibed_date  , $name_value";
                return false;
            }
            return true;
        }
    }

    public function isValidDateRangeDinamic($_format_date , $_recibed_date , $_range , $name_value="") {

        $timezone = new DateTimeZone('America/Mexico_City');

        if(!$this->existsDate($_format_date , $_recibed_date)){
            return false;
        }
        $_format_date = $this->getDateTimeFormat($_format_date);

        $_exclude   = (substr( $_range , 0 , 1 ) == '!') ? true : false;
        $_range     = str_replace( array( '!' , '[' , ']' ) , "" , $_range );
        $_arr_range = explode( ':' , $_range );

        if ( $_arr_range['0'] > $_arr_range['1'] ) {
            echo "'WRONG_RANGE' , $_range";
            return false;
        }

        $addOrSub1 = ($_arr_range[0] < 0) ? "sub" : "add";
        $addOrSub2 = ($_arr_range[1] < 0) ? "sub" : "add";
        $_min_limit = abs( $_arr_range[0] );
        $_max_limit = abs( $_arr_range[1] );

        $DateAnt = new DateTime('now',$timezone);
        $DateAnt->$addOrSub1( new DateInterval("P" . $_min_limit . "D") );
        $DateAnt = $DateAnt->format('Y-m-d');
        $DateAnt = DateTime::createFromFormat( 'Y-m-d h:i:s' , $DateAnt . ' 00:00:00' );

        $DatePost = new DateTime('now',$timezone);
        $DatePost->$addOrSub2( new DateInterval("P" . $_max_limit . "D") );
        $DatePost = $DatePost->format('Y-m-d');
        $DatePost = DateTime::createFromFormat( 'Y-m-d h:i:s' , $DatePost . ' 00:00:00' );

        $DateRecibed = DateTime::createFromFormat( $_format_date , $_recibed_date . ' 00:00:00' );
        $DateRecibed->format($_format_date);

        if ( $_exclude ){
            if($DateRecibed < $DateAnt or $DateRecibed > $DatePost){
                return true;
            }else{
                echo "'DATE_IS_IN_EXCLUSION_RANGE_DINAMIC' ,$_recibed_date , $name_value";
                return false;
            }
        }

        if($DateAnt <= $DateRecibed and $DatePost >= $DateRecibed){
            return true;
        }else{
            echo "'DATE_IS_OUT_INCLUSION_RANGE_DINAMIC' ,$_recibed_date , $name_value";
            return false;
        }
    }

    public function isValidDateRange($_format_date , $_recibed_date , $_range , $name_value="") {
        if(!$this->existsDate($_format_date , $_recibed_date)){
            return false;
        }

        $_exclude   = (substr( $_range , 0 , 1 ) == '!') ? true : false;
        $_range     = str_replace( array( '!' , '[' , ']' ) , "" , $_range );
        $_arr_range = explode( ':' , $_range );

        if(!$this->existsDate($_format_date , $_arr_range['0'])){
            return false;
        }
        if(!$this->existsDate($_format_date , $_arr_range['1'])){
            return false;
        }

        $_format_date = $this->getDateTimeFormat($_format_date);

        $DateRecibed = DateTime::createFromFormat( $_format_date , $_recibed_date . ' 00:00:00' );
        $DateAnt   = DateTime::createFromFormat( $_format_date , $_arr_range['0'] . ' 00:00:00' );
        $DatePost = DateTime::createFromFormat( $_format_date , $_arr_range['1'] . ' 00:00:00' );

        if ( $DateAnt > $DatePost ) {
            echo "'WRONG_DATE_RANGE' ,$_range";
            return false;
        }

        if ( $_exclude ){
            if($DateRecibed < $DateAnt or $DateRecibed > $DatePost){
                return true;
            }else{
                echo "DATE_IS_IN_EXCLUSION_RANGE_STATIC' , $_recibed_date  , $name_value";
                return false;
            }
        }

        if($DateAnt <= $DateRecibed and $DatePost >= $DateRecibed){
            return true;
        }else{
            echo "DATE_IS_OUT_INCLUSION_RANGE_STATIC' , $_recibed_date  , $name_value";
            return false;
        }

    }

    private function whichDateRangeStrucIs($_format_date,$_range) {

        if ( preg_match( "/^(!)?\[(-)?[0-9]+\:(-)?[0-9]+\]$/" , $_range ) ) {
            return 'DINAMIC';
        }

        $_range     = str_replace( array( '!' , '[' , ']' ) , "" , $_range );
        $_arr_range = explode( ':' , $_range );

        if(!$this->existsDate($_format_date , $_arr_range['0'])){
            return false;
        }
        if(!$this->existsDate($_format_date , $_arr_range['1'])){
            return false;
        }

        return 'ESTATIC';
    }
    /**
     * @param $_range
     *
     * @return bool
     */
    private function isValidRangeStruc($_range) {

        if ( !preg_match( "/^(!)?\[(-)?[0-9]+\:(-)?[0-9]+\]$/" , $_range ) ) {
            echo "BAD_RANGE_STRUCTURE' , $_range ";

            return false;
        }

        return true;
    }

    /**
     * @param $_value
     *
     * @return bool
     */
    private function isString($_value , $name_value="") {

        if ( !is_string( $_value ) ) {
            echo "'ESPECTED_STRING' , $_value , $name_value";

            return false;
        }

        return true;
    }

    /**
     * @param $_value
     *
     * @return bool
     */
    private function isEmpty($_value) {

        if ( $this->doForceRemoveRegExp( '/[\p{Z}\p{C}]/u' , $_value ) == "" ) {
            return true;
        }

        return false;
    }

    private function isValidRequired($_required , $_value , $name_value="") {
        switch ( $_required ) {
            case 'true':
                if ( $this->isEmpty( $_value ) ) {
                    echo "ESPECTED_VALUE , $_value , $name_value";
                    return false;
                }
                break;
            case 'false':
                if ( $this->isEmpty( $_value ) ) {
                    return true;
                }
                break;
            default:
                echo "'BAD_REQUIRED_ARGUMENT' , $_required , $name_value";

                return false;
                break;
        }
        return null;
    }
}
