<?php
/**
 * Pleasant PHP — a set of useful methods and variables.
 *
 * @package   pp
 * @version   2024.07.19
 * @see       https://app.w87.eu/codeInfo?app=pp.w87.eu&file=pp.w87.eu.php
 * @license   https://creativecommons.org/licenses/by-sa/4.0/ CC BY-SA 4.0
 * @author    Walerian Walawski <https://w87.eu/?contact>
 * @link      https://w87.eu/
 * @copyright 20016-2024 SublimeStar.com Walerian Walawski © All Rights Reserved.
 */

class PP
{
    public const MB = 1048576;
    public const GB = 1073741824;
    public const LOGS_PATH = '/var/logs/pp';

    public static $conf = [
            'app'       => 'test',
            'logsPath'   => __DIR__,
            'mail'  => [
                'reName'  => 'SublimeStar.com',
                'reEmail' => 'help@sublimestar.com',
                'from'    => 'notify@sublimestar.com',
                'footer'  => '

-- 
Kind regards,
Walerian Walawski
https://sublimestar.com/

',
                'notify'  => 'SublimeStar <test.notification@sublimestar.com>',
            ],
        'date' => [
            'time' => 'H:i:s',
            'year' => 'Y',
            'date' => 'Y-m-d',
            'full' => 'Y-m-d H:i:s',
        ],
        'db' => [
            'connect' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock',
            'user' => 'root',
            'pass' => '',
            'name' => 'pp',
            'charset' => 'utf8',
        ]
    ];

    public function __construct($conf=[]){
        self::$conf = array_merge(self::$conf, $conf);

		$this->unixTime      = $_SERVER['REQUEST_TIME'];            // request
		$this->unixTimeFloat = $_SERVER['REQUEST_TIME_FLOAT']; // microsecond
		
		// -*- Initializing: General purpose var. ------------------------------------------------------------------------------
		$this->time = date(self::$conf['date']['time']);
		$this->date = date(self::$conf['date']['full']);
		$this->year = date(self::$conf['date']['year']);
		$this->dt   = "{$this->date} {$this->time}";
		$this->salt = 'Łódź ęąćŹŻŁóśń LOL :-)';
		$this->path = realpath('.');

		$this->daysInSec = [1 => 86400, 2 => 172800, 3 => 259200, 7 => 604800, 10 => 864000, 15 => 1296000, 30 => 2592000, 90 => 7776000, 100 => 8640000];
		$this->whiteChars = ["\n", "\r", "\t", ' ', ' '];
		$this->userIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
		$this->proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' === '' ? $_SERVER['REQUEST_SCHEME'] : $_SERVER['HTTP_X_FORWARDED_PROTO'];
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->ref = $_SERVER['HTTP_REFERER'] ?? '';
		$this->uas = $_SERVER['HTTP_USER_AGENT'];
		$this->host = $_SERVER['HTTP_HOST'];
		$this->base = "{$this->proto}://{$this->host}";
		$this->url = "{$this->base}{$this->uri}";
		$this->request = "{$this->proto} {$this->method} {$this->url} (port {$_SERVER['SERVER_PORT']})";

        return $this;
    }

    /**
     * Get the general config. var. (single arg.) or set it (two args.)
     *
     * @param  string $key — key in $config
     * @param  mixed $value — sets the key value if not null
     * @return mixed value for the $key (also if setting a new one)
     */

    public static function conf($key, $value = null){
        if($value === null){
            return self::$conf[$key] ?? null;
        }else{
            self::$conf[$key] = $value;
            return self::$conf[$key];
        }
    }

    /** ------------------------------------------------- https://w87.eu/?v=2023.08.27 ----
     * Example vanilla conf:
     * ------------------------------------------------------------------------------------
        
        PP::conf('app', [
            'id'         => 'test',
            'date' => 'Y-m-d H:i:s',
            'logsPath'   => __DIR__,
            'mail'  => [
                    'reName'  => 'SublimeStar.com',
                    'reEmail' => 'help@sublimestar.com',
                    'from'    => 'notify@sublimestar.com',
                    'footer'  => '

-- 
Kind regards,
Walerian Walawski
https://sublimestar.com/

',
                    'notify'  => 'SublimeStar <test.notification@sublimestar.com>',
                ]
            ]);
    
     */

    /**
     * Memcached: new instance (persistent)
     * 
     * @param  string $instance — persistent instance ID
     */
    
    public static function mcNew($instance = null) {
        $instance = null === $instance ? self::$conf['app']['id'] : $instance;

        $memCached = new Memcached($instance);
        $memCached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

        if(count($memCached->getServerList()) == 0){
            $memCached->addServer('127.0.0.1', 11211);
        }

        return $memCached;
    }

    /**
     * Memcached: get value for a key
     *
     * @param  string $key — key name
     */
    
    public static function mcGet($key){
        global $memCached;
        return $memCached->get(self::$conf['app']['id'].":$key");
    }
    
    /**
     * Memcached: set value for a key (seconds)
     *
     * @param  string  $key — key name
     * @param  mixed   $value
     * @param  integer $time
     */
    
    public static function mcSet($key, $value='', $time=30){
        global $memCached;
        return $memCached->set(self::$conf['app']['id'].":$key", $value, $_SERVER['REQUEST_TIME'] + $time);
    }
    
    /**
     * Memcached: set value for a key - minutes
     *
     * @param  string  $key — key name
     * @param  mixed   $value
     * @param  integer $time
     */
    
    public static function mcSetM($key, $value='', $time=5){
        global $memCached;
        return $memCached->set(self::$conf['app']['id'].":$key", $value, ( $_SERVER['REQUEST_TIME'] + ( $time * 60 ) ) );
    }
    
    /**
     * Memcached: set value for a key - hours
     * 
     * @param  string  $key — key name
     * @param  mixed   $value
     * @param  integer $time
     */
    
    public static function mcSetH($key, $value='', $time=4){
        global $memCached;
        return $memCached->set(self::$conf['app']['id'].":$key", $value, ( $_SERVER['REQUEST_TIME'] + ( $time * 3600 ) ) );
    }
    
    /**
     * Memcached: set value for a key - days
     * 
     * @param  string  $key — key name
     * @param  mixed   $value
     * @param  integer $time
     */
    
    public static function mcSetD($key, $value='', $time=7){
        global $memCached;
        return $memCached->set(self::$conf['app']['id'].":$key", $value, ( $_SERVER['REQUEST_TIME'] + ( $time * 86400 ) ) );
    }
    
    /**
     * Memcached: delete a key
     * 
     * @param  string  $key — key name
     */
    
    public static function mcDel($key){
        global $memCached;
        return $memCached->delete(self::$conf['app']['id'].":$key");
    }
    
    /**
     * Send an e-mail
     *
     * @return bool
     */
    
    public static function email($name, $email, $subject, $content, $reName='', $reEmail='', $notify=''): bool{
        if(!self::conf('app')){
            return false;
        }
        
        $id   = self::conf('app')['id'];
        $conf = self::conf('app')['mail'];

        $nameRe = ($reName === '') ? $conf['reName'] : $reName;
        $reply  = ($reEmail === '') ? $conf['reEmail'] : $reEmail;
        $notify = ($notify === '') ? $conf['notify'] : $notify;
        $notify = empty($notify) ? '' : "\nDisposition-Notification-To: $notify";
        
        $from  = '=?UTF-8?B?'.base64_encode($nameRe).'?= <'.$conf['from'].'>';
        $reply = '=?UTF-8?B?'.base64_encode($nameRe).'?= <'.$reply.'>';

        return mail('=?UTF-8?B?'.base64_encode($name).'?= <'.$email.'>','=?UTF-8?B?'.base64_encode($subject).'?=',$content.$conf['footer'],
'MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
From: '.$from.'
Reply-To: '.$reply.$notify.'
User-Agent: pp.w87.eu Email
X-Mailer: pp.w87.eu Email
X-MTK: https://api.sublimestar.com/mtk.out?in='.$id.'-ppW87euEmail-'.$_SERVER['REQUEST_TIME']);
    }

    public static function sleep($milisecondsMin = 1000, $milisecondsMax = null){
        return usleep($milisecondsMax === null ? $milisecondsMin : rand($milisecondsMin, $milisecondsMax));
    }

    public static function var($var, $compact = false, $html = false): string {
        return $compact
        ? str_replace("),\n", "\n", str_replace(["\r", "array (\n", "\n)"], '', var_export($var, true)))
        : var_export($var, true);
    }

    /** ------------------------------------------------- https://w87.eu/?v=2023.08.20 ----
     * Texts
     * ------------------------------------------------------------------------------------ */

    public static function shortenStr($str, $limit, $start = 0, $sign = '...'): string {
        return mb_strlen($str) > $limit ? mb_substr($str, $start, ($limit - 1)) . $sign : ($start ? mb_substr($str, $start) : $str);
    }

    public static function generateRandomString(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        $string = '';
        $max    = mb_strlen($keyspace) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $string .= $keyspace[random_int(0, $max)];
        }

        return $string;
    }

    /** ------------------------------------------------- https://w87.eu/?v=2023.08.20 ----
     * URLs
     * ------------------------------------------------------------------------------------ */

    public static function removeAccent($str): string {
        $a = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];
        return str_replace($a, $b, $str);
    }

    public static function slug($str, $placeholder='default', $length = 256): string {
        $str = trim(preg_replace(['`[^a-z0-9]`', '`[-]+`'], '-', mb_strtolower(self::removeAccent($str))), '-');
        return self::shortenStr(($str == '') ? $placeholder : $str, $length, 0, '');
    }

    public static function redirect($url, $code = 307){
        header("Location: $url", true, $code);
        exit("Redirecting to <a href=\"$url\">$url</a>");
    }

    /** ------------------------------------------------- https://w87.eu/?v=2023.08.20 ----
     * Humanizing
     * ------------------------------------------------------------------------------------ */

    public static function humanDomain($domain): string {
        $domain = mb_strtolower($domain);
        return ucfirst((mb_substr($domain, 0, 4) === 'www.') ? mb_substr($domain, 4) : $domain);
    }

    /**
     * Convert bytes to human readable format filesize
     * 
     * @param  integer $filesize
     * @return string
     */

    public static function humanBytes($filesize, $dec = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for($i = 0; $filesize >= 1024; $i++){
            $filesize /= 1024;
        }
        return round($filesize, $dec).' '.$units[$i];
    }

    public static function humanDate($timestamp): string {
        return date(self::$conf['date']['full'], $timestamp);
    }

    /**
     * Remove file/dir (recur., force)
     * 
     * @param  string $path
     * @param  string $safe — $path has to START WITH $safe, but can NOT BE only $safe, to run rm
     *
     * @return bool
     */

    public static function rm($path, $safe): bool {

        if(str_starts_with($path, $safe) && $path !== $safe){
            system('rm -rf -- '.escapeshellarg($path), $retval);
            return $retval == 0;
        }

        return false;
    }

    public static function loadAvg()
    {
        $avg  = file_get_contents('/proc/loadavg');
        $load = explode(' ', $avg);

        return [
            '1m'    => $load[0],
            '5m'    => $load[1],
            '15m'   => $load[2],
            'loads' => $load[0] . ' ' . $load[1] . ' ' . $load[2],
            'full'  => $avg,
        ];
    }

    /**
     * Logging
     * 
     * @param  string $file   — use __FILE__.':'. __LINE__
     * @param  string $info   — description
     * @param  string $type   — e.g. info, warning, error, fatal etc.
     * @param  mixed  $export — data to be exported
     * 
     * @return int|false
     */

    public static function log($file, $type, $info, $export = []): int {
        $date = date(self::$conf['date']['full']);
        $dump = empty($export) ? '' : var_export($export, true)."\n";
        return file_put_contents(self::$conf['app']['path']['logs']."/$type.log", "$date → $info\n$file\n$dump\n", FILE_APPEND);
    }

    /**
     * Convert human readable format filesize to bytes.
     * Based on https://stackoverflow.com/a/11807179/7057874
     *
     * @author John V., Walerian Walawski
     */
    
    public static function converStringSizeToInt($from) {
        $from   = str_replace([',', 'BYTES', 'BYTE'], ['.', 'B', 'B'], strtoupper($from));
        $from   = preg_replace('`[^\dBKMGTP.]`', '', $from);
        $units  = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $number = substr($from, 0, -2);
        $suffix = substr($from, -2);

        // B or no suffix
        if(is_numeric(substr($suffix, 0, 1))){
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null){
            return null;
        }

        return round($number * (1024 ** $exponent));
    }

    /** ------------------------------------------------- https://w87.eu/?v=2024.04.06 ----
     * Global vars.
     * ------------------------------------------------------------------------------------ */

    public static function get($name='', $value=null, $length=null, $type=null){
        if(!isset($_GET[$name])) return null;
        if($value && $_GET[$name] != $value) return null;
        if($length && mb_strlen($_GET[$name]) < $length) return null;
        if($type && $type === 'numeric' && !is_numeric($_GET[$name])) return null;

        return $_GET[$name];
    }

    public static function post($name='', $value=null, $length=null, $type=null){
        if(!isset($_POST[$name])) return null;
        if($value && $_POST[$name] != $value) return null;
        if($length && mb_strlen($_POST[$name]) < $length) return null;
        if($type && $type === 'numeric' && !is_numeric($_POST[$name])) return null;

        return $_POST[$name];
    }

    // TODO: improve!!!
    
    public static function db($sql, $args = null){
        global $PPdb;
        return $PPdb->run($sql, $args);
    }

}

/** ------------------------------------------------- https://w87.eu/?v=2023.08.22 ----
 * Database (PDO)
 * ------------------------------------------------------------------------------------ */

class PPdb extends PDO{
    public $prefix = '';

    public function run($sql, $args = null){
        try{

            if($args === null){
                return $this->query($sql);
            }

            $stmt = $this->prepare($sql);
            $stmt->execute($args);
            return $stmt;

        }catch(Exception $e){
            PP::$conf['debug']['dbQuery'] ? PP::log(__FILE__.':'. __LINE__, 'db-error', "PPdb Exception
            REQUEST: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}
            QUERY:   $sql
            ERROR:   ".$e->getMessage()."
            ARGS:    ".str_replace(["\n  ", "\n", '  ', '  ', '  '], ' ', var_export($args, true))) : null;

            return false;
        }
    }
}

/** ------------------------------------------------- https://w87.eu/?v=2023.08.23 ----
 * Example of using PPdb:
 * ------------------------------------------------------------------------------------
try {
    $PPdb = new PPdb(PP::$conf['db']['connect'].';dbname='.PP::$conf['db']['name'].';charset='.PP::$conf['db']['charset'], PP::$conf['db']['user'], PP::$conf['db']['pass'], [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT               => true,
        PDO::ATTR_EMULATE_PREPARES         => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
    $PPdb->exec('USE '.PP::$conf['db']['name']);
}catch(Exception $e){
    PP::log(__FILE__.':'. __LINE__, 'db-error', "PPdb Connection Exception: ".$e->getMessage());
}
*/

/*

function w87VarDump($var, $depth=1, $indentation=0): string{
    $output = '';
    
    if(is_array($var)){
        $output .= "[\n";
        foreach($var as $key=>$value){
            if(is_array($value)){
                if($depth <= 0){
                    return '';
                }else{
                    for($i=0;$i<$indentation;$i++){
                        $output .= '    ';
                    }
                    $output .= "$key => [\n".w87VarDump($value, $depth-1, $indentation+1);
                    for($i=0;$i<$indentation;$i++){
                        $output .= '    ';
                    }
                    $output .= "],\n";
                }
            }else{
                for($i=0;$i<$indentation;$i++){
                    $output .= '    ';
                }
                $value = var_export($value, true);
                $output .= "$key => $value,\n";
            }
        }
        $output .= "],\n";
    }else{
        $output = var_export($var, true);
    }
    
    return str_replace(["[\n]", '=> NULL,'], ['[]', '=> null,'], $output);
}
*/
