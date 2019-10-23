<?php

/**
 * @class         Class Sanitizer
 * @brief         Sanitizer
 *
 * @author        kevin.liu@century21cn.com
 * @copyright (C) 2017 Century21cn All Rights Reserved.
 * @version       1.0
 * @date          2017-10-02
 */
class Sanitizer
{

    /**
     * @var
     */
    public $request;

    /**
     * @var array
     */
    public $allowed_filters = array(
        FILTER_SANITIZE_EMAIL,
        FILTER_SANITIZE_ENCODED,
        FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        FILTER_SANITIZE_MAGIC_QUOTES,
        FILTER_SANITIZE_NUMBER_FLOAT,
        FILTER_SANITIZE_NUMBER_INT,
        FILTER_SANITIZE_SPECIAL_CHARS,
        FILTER_SANITIZE_STRING,
        FILTER_SANITIZE_STRIPPED,
        FILTER_SANITIZE_URL
    );

    /**
     * Sanitizer constructor.
     *
     * @param $request
     */
    public function __construct($request = [])
    {
        $this->request = $request;
    }

    /**
     * @name  booleanValue
     * @brief   booleanValue
     *
     * @author  kevin.liu@century21cn.com
     * @param $boolean_value
     * @return bool
     * @retval
     * @version 1.0
     * @date    2017-10-02
     * @test   \SanitizerTest::testBooleanValue
     */
    public function booleanValue($boolean_value)
    {

        $return_value = false;

        if (is_bool($boolean_value)) {

            $return_value = $boolean_value;

        } else {

            if (is_numeric($boolean_value)) {

                if ($boolean_value > 0) {

                    $return_value = true;

                } else {

                    $return_value = false;

                }

            }

            if (is_string($boolean_value)) {

                $boolean_value = trim(strtolower($boolean_value));

                $options = [
                    'yes',
                    'no',
                    'true',
                    'false'
                ];

                if (in_array($boolean_value, $options)) {

                    if ($boolean_value == 'yes' || $boolean_value == 'true') {

                        $return_value = true;

                    } else {

                        $return_value = false;

                    }

                } else {

                    if (strlen($boolean_value) > 0) {

                        $return_value = true;

                    } else {

                        $return_value = false;

                    }

                }

            }

        }

        return $return_value;

    }

    /**
     * @name  getIntValue
     * @brief   getIntValue
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @return mixed
     * @retval
     * @version 1.2
     * @date    2017-06-06
     * @test    \SanitizerTest::testGetIntValue
     */
    public function getIntValue($name, $default = 0)
    {
        if (!isset($this->request[$name])) {
            return $default;
        }

        return $this->intValue($this->request[$name], $default);
    }

    /**
 * @name  intValue
 * @brief   intValue
 *
 * @author  kevin.liu@century21cn.com
 * @param $int
 * @param $default
 * @return int
 * @retval
 * @version 1.2
 * @date    2017-06-06
 * @test    \SanitizerTest::testIntValue
 */
    public function intValue($int, $default = 0)
    {
        $original = $int;

        $int = filter_var($int, FILTER_SANITIZE_NUMBER_INT);
        if ( (string) $int === (string) $original && (string) $int !== '' ) {
            return (int) $int;
        } else {
            return $default;
        }
    }

    /**
     * @name  floatValue
     * @author  kevin.liu@century21cn.com
     *
     * @param int|float|string input
     * @return float
     * @retval
     * @version 1.0
     * @date    2017-07-26
     */
    public function floatValue($float, $default = 0.0)
    {
        if (is_numeric($float) && !is_infinite($float) && !is_nan($float)) {
            return (float) $float;
        }
        return $default;
    }

    /**
     * @name  getFloatValue
     * @brief   getFloatValue
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @return mixed
     * @version 1.1
     * @date    2017-06-06
     * @test    \SanitizerTest::testGetFloatValue
     */
    public function getFloatValue($name, $default)
    {
        if (!isset($this->request[$name])) {
            return $default;
        } else {

            $value = (float) $this->request[$name];

            if ( (string) $value !== (string) $this->request[$name] ) {
                return $default;
            }

            return $value;
        }
    }

    /**
     * @name  getStringValue
     * @brief   getStringValue
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @param  string $pattern
     * @return mixed
     * @retval
     * @version 1.3
     * @date    2017-04-09
     * @test    \SanitizerTest::testGetStringValue
     */
    public function getStringValue($name, $default, $pattern="")
    {
        if (!isset($this->request[$name])) {
            return $default;
        } else {
            if ($this->request[$name] == "") {
                return $default;
            }

            if($pattern == ""){
                return $this->request[$name];
            }
            elseif($pattern == FILTER_SANITIZE_STRING){
                $return = $this->filterSanitizeString( $this->request[$name] );
            }
            elseif(in_array($pattern, $this->allowed_filters)){
                $return = filter_var($this->request[$name], $pattern);
            }else {
                $return = filter_var($this->request[$name], FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $pattern)));
            }

            if($return == false){
                return $default;
            }else{
                return $return;
            }

        }
    }

    /**
     * @name filterSanitizeString
     *
     * @author kevin.liu@century21cn.com
     * @param string $value
     * @return string
     * @version 1.0
     * @date    2017-06-03
     */
    public function filterSanitizeString($value) {
        $value = preg_replace("/(<)\s*(\d)/", "DSRLESSTHAN$2", $value);
        $return = filter_var($value, FILTER_SANITIZE_STRING);
        return preg_replace("/DSRLESSTHAN(\d)/", "<$1", $return);
    }


    /**
     * @name getEscapedStringValue
     * @brief    getEscapedStringValue
     *
     * @author   kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @return string
     * @retval
     * @version  1.0
     * @date     2018-02-06
     * @test    \SanitizerTest::testGetEscapedStringValue
     */
    public function getEscapedStringValue($name, $default)
    {
        if (!isset($this->request[$name])) {
            return htmlentities($default);
        } else {
            if ($this->request[$name] == "") {
                return htmlentities($default);
            }

            return htmlentities($this->request[$name]);
        }
    }

    /**
     * @name  stringValue
     * @brief   stringValue
     *
     * @author  kevin.liu@century21cn.com
     * @param string $string
     * @param string $default
     * @param bool $secure
     * @return string
     * @retval
     * @version 1.2
     * @date    2017-04-15
     * @test    \SanitizerTest::testStringValue
     */
    public function stringValue($string, $default = '', $secure = false)
    {
        $string = (string) $string;

        if (empty($string)) {
            $string = $default;
        } elseif ($secure) {
            $string = htmlspecialchars_decode( urldecode($string), ENT_QUOTES );
            $string = trim( filter_var($string, FILTER_SANITIZE_STRING) );
        }

        return $string;
    }

    /**
     * @name  arrayOfInts
     * @brief
     *
     * @author  Andrey Koryak andrew.koryak@evidencepartners.com
     * @param array $values
     * @return array
     * @version 1.1
     * @date    2017-04-17
     */
    public function arrayOfInts(array $values)
    {
        foreach ($values as $key => $item) {
            $int = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
            if (is_numeric($int)) {
                $values[$key] = (int)$int;
            } else {
                unset($values[$key]);
            }
        }

        return $values;
    }

    /**
     * @name  getArrayOfIntsgetArrayOfInts
     * @brief
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @return array
     * @retval
     * @version 1.1
     * @date    2017-10-02
     * @test    \SanitizerTest::testGetArrayOfInts
     */
    public function getArrayOfInts($name)
    {
        $toreturn = [];

        if (isset($this->request[$name]) && is_array($this->request[$name])) {
            $toreturn = $this->arrayOfInts($this->request[$name]);
        }

        return $toreturn;
    }

    /**
     * @name  getArrayOfInts2
     * @brief   getArrayOfInts2
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @return array
     * @retval
     * @version 1.1
     * @date    2017-04-17
     * @test \SanitizerTest::testGetArrayOfInts2
     * @todo    change $_REQUEST to $this->request
     */
    public function getArrayOfInts2($name, $default)
    {
        $return_string = $default;

        if (array_key_exists($name, $_REQUEST)) {

            $toreturn = [];

            if (is_array($_REQUEST[$name])) {

                foreach ($_REQUEST[$name] as $item) {

                    $num = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
                    $toreturn[] = (int)$num;

                }

                $return_string = $toreturn;

            }

        }

        return $return_string;
    }

    /**
     * @name    arrayOfStrings
     * @brief   Sanitize input array of strings. Not based on $_REQUEST
     *
     * @author  kevin.liu@century21cn.com
     *
     * @param   array $value
     * @param   bool  $strict
     * @param   bool  $preserveKeys
     * @return  array
     *
     * @version 1.1
     * @date    2017-06-06
     */
    public function arrayOfStrings(array $value, $strict = true, $preserveKeys = false)
    {
        $result = [];

        foreach ($value as $key => $item) {
            $item = (string)$item;
            if ($strict) {
                $item = htmlspecialchars(strip_tags($item), ENT_QUOTES);
            }

            if ($preserveKeys) {
                $result[$key] = $item;
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @name stripTagsKeepLessMore
     *
     * @author  kevin.liu@century21cn.com
     *
     * @param string $value
     * @param bool $htmlspecialchars
     * @return string
     *
     * @version 1.0
     * @date    2017-07-04
     *
     * Example:
     *      (new Sanitizer)->stripTagsKeepLessMore($value);
     *
     * Results:
     *      Kids <10                                                   ==> Kids <10
     *      <b>Kids<20</b>                                             ==> Kids< 20
     *      10<test<date<20                                            ==> 10<test<date<20
     *      <b>10<test<=date<20</b>                                    ==> 10<test<=date<20
     *      10 < test1 <= test2 < 20                                   ==> 10 < test1 <= test2 < 20
     *      <s>10 < test1 < test2 > < 20</s>                           ==> 10 < test1 < test2 > < 20
     *      <b>10 < <i>test1</i> < <anything>test2any <<<br/> < 20</b> ==> 10 < test1 < test2any << < 20
     */
    public function stripTagsKeepLessMore($value, $htmlspecialchars = true) {
        $value = (string) $value;
        $value = preg_replace("(<[^ <][^<]*?>)", "", $value);
        if ($htmlspecialchars) {
            $value = htmlspecialchars($value, ENT_QUOTES);
        }
        return $value;
    }


    /**
     * @name htmlentitiesKeepHTMLTags
     * @brief  htmlentities on everything except a,b,br,h1,h2,h3,h4,h5,h6,i,p,strong,u
     *
     * @param $htmlText
     * @param $ent
     * @return array|string|string[]|null
     * @retval
     * @author kevin.liu@century21cn.com
     * @version 1.0
     * @date 2017-08-13
     * @todo PHPUnit @test filename.php::method_called()
     */
    public static function htmlentitiesKeepSafeHTMLTags ($htmlText, $ent)
    {
        $matches = Array();
        $sep = '###HTMLTAG###';

        $pattern = "/(<\/?(?:a|b(?:r)?|h(?:[1-6]|r)|i|p|u|(?:strong))(?:\s(?:\S|\s)*>|\/?>))/imU";
        preg_match_all($pattern, $htmlText, $matches);

        $tmp = preg_replace($pattern, $sep, $htmlText);
        $tmp = explode($sep, $tmp);

        for ($i=0; $i<count($tmp); $i++)
            $tmp[$i] = htmlentities($tmp[$i], $ent, 'UTF-8', false);


        $tmp = join($sep, $tmp);

        for ($i=0; $i<count($matches[0]); $i++)
            $tmp = preg_replace(":$sep:", $matches[0][$i], $tmp, 1);


        //remove all attributes except hrefs
        $tmp = preg_replace_callback(
            '/<([a-z][a-z0-9]*)(?:[^>]*(\shref=[\'\"]([^\'\"]*)[\'\"]))?[^>]*?(\/?)>/i',
            function ($match) {
                if(empty($match[2])){
                    return $match[0];
                }
                if (filter_var($match[3], FILTER_VALIDATE_URL) === false || $match[1] != "a") {
                    return "<".$match[1].$match[4].">";
                } else {
                    return "<".$match[1].$match[2].$match[4].">";
                }
            },
            $tmp);

        return $tmp;
    }

    /**
     * @name       getArrayOfStrings
     * @brief   getArrayOfStrings
     *
     * @author  kevin.liu@century21cn.com
     * @param      $name
     * @param bool $preserve_keys
     * @param string $pattern
     * @return array
     * @retval
     * @version 1.1
     * @date    2017-04-22
     * @test    \SanitizerTest::testGetArrayOfStrings
     * @todo    change $_REQUEST to $this->request
     */
    public function getArrayOfStrings($name, $preserve_keys = false, $pattern = '')
    {
        $toreturn = [];

        if (isset($_REQUEST[$name]) && is_array($_REQUEST[$name])) {
            foreach ($_REQUEST[$name] as $key => $item) {

                if($pattern != "") {
                    if($pattern == FILTER_SANITIZE_STRING){
                        $item = preg_replace("/(<)\s*(\d)/", "DSRLESSTHAN$2", $item);
                        $item = filter_var($item, $pattern);
                        $item = preg_replace("/DSRLESSTHAN(\d)/", "< $1", $item);
                    }
                    elseif (in_array($pattern, $this->allowed_filters)) {
                        $item = filter_var($item, $pattern);
                    } else {
                        $item = filter_var($item, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $pattern)));
                    }
                }

                if ($preserve_keys) {
                    $toreturn[$key] = $item;
                } else {
                    $toreturn[] = $item;
                }
            }
        }

        return $toreturn;
    }

    /**
     * @name  getArrayOfStrings2
     * @brief   getArrayOfStrings2
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @param $default
     * @param string $pattern
     * @return array
     * @retval
     * @version 1.1
     * @date    2017-04-22
     * @test    \SanitizerTest::testGetArrayOfStrings2
     * @todo    change $_REQUEST to $this->request
     */
    public function getArrayOfStrings2($name, $default, $pattern = "")
    {

        $return_string = $default;

        if (array_key_exists($name, $_REQUEST)) {

            $toreturn = [];

            if (is_array($_REQUEST[$name])) {

                foreach ($_REQUEST[$name] as $item) {

                    if($pattern != "") {
                        if($pattern == FILTER_SANITIZE_STRING){
                            $item = preg_replace("/(<)\s*(\d)/", "DSRLESSTHAN$2", $item);
                            $item = filter_var($item, $pattern);
                            $item = preg_replace("/DSRLESSTHAN(\d)/", "< $1", $item);
                        }
                        elseif (in_array($pattern, $this->allowed_filters)) {
                            $item = filter_var($item, $pattern);
                        } else {
                            $item = filter_var($item, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $pattern)));
                        }
                    }

                    $string = (string)$item;
                    $toreturn[] = $string;

                }

                $return_string = $toreturn;

            }

        }

        return $return_string;

    }

    /**
     * @name  getArrayOfSerializedData
     * @brief   getArrayOfSerializedData
     *
     * @author  kevin.liu@century21cn.com
     * @param $name
     * @return bool
     * @retval
     * @version 1.0
     * @date    2017-10-02
     * @test \SanitizerTest::testGetArrayOfSerializedData
     * @todo    change $_REQUEST to $this->request
     */
    public function getArrayOfSerializedData($name)
    {
        if (isset($_REQUEST[$name])) {
            parse_str($_REQUEST[$name], $output);

            return $output;
        }

        return false;
    }

    /**
     * @name  getArrayKey
     * @brief   getArrayKey
     *
     * @author  kevin.liu@century21cn.com
     * @param $object
     * @param $key
     * @param $default
     * @return mixed
     * @retval
     * @version 1.0
     * @date    2017-10-02
     * @test    \SanitizerTest::testGetArrayKey
     */
    public function getArrayKey($object, $key, $default)
    {

        $return_string = $default;

        if (array_key_exists($key, $object)) {

            $return_string = $object[$key];

        }

        return $return_string;

    }

    /**
     * @name  getObjectVariable
     * @brief   getObjectVariable
     *
     * @author  kevin.liu@century21cn.com
     * @param $object
     * @param $variable
     * @param $replacement
     * @return mixed
     * @retval
     * @version 1.0
     * @date    2017-10-02
     * @test    \SanitizerTest::testGetObjectVariable
     */
    public function getObjectVariable($object, $variable, $replacement)
    {

        $return_value = $replacement;

        if (isset($object->$variable)) {

            $return_value = $object->$variable;

        }

        return $return_value;

    }

    /**
     * @name  checkEmail
     * @brief   checkEmail
     *
     * @author  kevin.liu@century21cn.com
     * @param $email_address
     * @return mixed
     * @retval
     * @version 1.0
     * @date    2017-10-02
     * @test \SanitizerTest::testCheckEmail
     */
    public function checkEmail($email_address)
    {

        return filter_var($email_address, FILTER_VALIDATE_EMAIL);

    }

    /**
     * @name    Sanitizer::removeSpecialCharacters
     * @brief   Remove all characters except alphanumeric, underscore, dash and optionally spaces.
     *
     * @author  kevin.liu@century21cn.com
     * @param   string $string
     * @param   bool $allow_spaces
     *
     * @return  mixed
     * @retval
     * @version 1.1
     * @date    2018-12-17
     * @test    \SanitizerTest::testRemoveSpecialCharacters
     */
    public function removeSpecialCharacters($string, $allow_spaces = false)
    {
        // Remove everything not a-zA-Z0-9_`-
        return trim(preg_replace($allow_spaces ? "/[^A-Za-z0-9 _`-]/i" : "/[^A-Za-z0-9_`-]/i", '', $string));
    }

    /**
     * @name normalizeFileName
     * @brief   normalize FileName Get rid of accents and turn it back to latin1 so it'll sit pretty in the database.
     *
     * @author  kevin.liu@century21cn.com
     * @param string $filename
     * @retval  string|false
     * @return string|false
     * @version 1.0
     * @date    2018-08-23
     */
    public static function normalizeFileName($filename)
    {
        $filename = normalizeUtf8String($filename);
        $filename = iconv("UTF-8", 'ISO-8859-1//TRANSLIT', $filename);

        return $filename;
    }

    /**
     * @name    isColorValid
     * @brief   Validates a colour string. If it isn't a hex value of either 3 or 6 characters, with characters 0 to 9,
     *          and/or a to f, it is invalid.
     * @author  kevin.liu@century21cn.com
     * @param   string $color
     *
     * @retval  bool
     * @return  bool
     * @version 1.0
     * @date    2018-12-06
     */
    public static function isColorValid($color)
    {

        if (preg_match("/^#([0-9A-Fa-f]{3}){1,2}$/i", $color) !== 1) {

            return false;
        }

        return true;
    }

}

$sanitizer = new Sanitizer($_REQUEST);

