# verificador
Verificador, Validador PHP

    //VALIDACION DE NUMEROS
    /*
    decDigits([0:0])
            [>=0:x>0]
            ![>=0:x>0] EXPRESION NEGATIVA
    Required(true)
         required=true/false;
    Range([0:10000000])
         range=[+-x,+-y];
        range=![+-x,+-y]; EXPRESION NEGATIVA


	 
	 
    $rules='decDigits([0:0])|Required(true)|Range([0:10000000])';
    if(!$this->GenValidator->isValidNumeric($rules,$value,'NUMERODECOBRO')){
        return false;
    }
     */

    //VALIDACION DE FECHA
    /*
    Format(yyyy-mm-dd)
        case 'dd-mm-yyyy':
            return 'd-m-Y h:i:s';
            break;
        case 'mm/dd/yyyy':
            return 'm/d/Y h:i:s';
            break;
        case 'yyyy-mm-dd':
            return 'Y-m-d h:i:s';
    Required(true)
            required=true/false;
    Range([-720:720])
        X=DIAS [-X:Y>X]
    Range([30-01-2020,30-01-2021])
        range=![+-x,+-y]; EXPRESION NEGATIVA

    $rules='Format(yyyy-mm-dd)|Required(true)|Range([-720:720])';
    if(!$this->GenValidator->isValidDate($rules,$value,'FECHADECOBRO')){
        return false;
    }
    */



    //VALIDACION DE STRINGS
    /*
    FORZOSO UTF-8
    Type(Full)
        $MinMay = 'A-Za-z';
        $MinMayNum = 'A-Za-z0-9';
        $Latin1Cuotes = 'áéíóúÁÉÍÓÚüÜñÑ';
        $BasicSymbols = '\¡\!\¿\?\;\,\:\.';
        $MathSymbols = '\=\+\-\#';
        $TelefonoGlobal=$MathSymbols,$MinMayNum,$Space = ' '
		$OtherSymbols = '°\~\@\*';
        $Escapes = '\/\(\)\[\]\{\}\|\^';
        $MoneySymbols = '\€\$';
        $EscapeOthers = '\©\®\¼\½\¾';
        $SeveralMeaning = '\'\"\\';
        $MysqlMeaning = '\%\_';
        $HtmlMeaning = '\<\>';
        $AllSpaces = '\s';
        $Space = ' ';
        $BasicLatin1 = $MinMayNum . $Latin1Cuotes;
        $BasicWords = $BasicLatin1 . $Space;
        $SimpleWords = $MinMayNum . $Space;

        switch ($sub_type) {
                case 'process':
                    return "/^[" . $MinMayNum . "\-\_]+$/";
                    break;
                case 'alpha':
                    return "/^[" . $MinMay . "]+$/";
                    break;
                case 'alphanum':
                    return "/^[" . $MinMayNum . "]+$/";
                    break;
                case 'simplewords':
                    return "/^[" . $SimpleWords . "]+$/";
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
                    return '/^[0-9a-z][0-9a-z\-\_\.]*[0-9a-z]*@[0-9a-z\-]+\.[a-z]+(\.[a-z]+)?$/';
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
                    $this->doError('UNDEFINED_PATTERN', $sub_type);
                    return false;
                    break;
            }
    Required(false)
            required=true/false;
    Range([1:500])
           range=[+x,+y];
    }
