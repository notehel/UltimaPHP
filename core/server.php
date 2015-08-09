<?php

/**
 * Ultima PHP - OpenSource Ultima Online Server written in PHP
 * Version: 0.1 - Pre Alpha
 */
class UltimaPHP
{
    
    /* Server Status */
    const STATUS_START = 1;
    const STATUS_STOP = 2;
    const STATUS_FATAL = 4;
    const STATUS_FILE_LOADING = 8;
    const STATUS_FILE_LOAD_FAIL = 16;
    const STATUS_FILE_LOADED = 32;
    const STATUS_UNHANDLED = 64;
    const STATUS_UNKNOWN = 128;
    const STATUS_LISTENING = 256;
    const STATUS_RUNNING = 512;
    
    /* Server Log Types */
    const LOG_NORMAL = "NORMAL";
    const LOG_WARNING = "WARNING";
    const LOG_DANGER = "DANGER";
    const LOG_ERROR = "ERROR";
    
    /* Server Variables */
    static $status = self::STATUS_UNKNOWN;
    static $start_time;
    static $basedir;
    static $conf;
    static $servers = array();

    /* Server Clients Sockets Variables */
    static $socketServer;
    static $socketClients = array();
    static $socketEvents = array();

    /* Shard Variables */
    static $clients = 0;
    static $items = 0;
    static $npcs = 0;
    
    function __construct($dir) {
        self::$basedir = $dir . "/";
    }
    
    public function start() {
        self::setStatus(self::STATUS_START);
        
        // Load the ini configuration file
        self::loadIni();
        
        // Load the core files
        foreach (glob(self::$basedir . "core/*.core.php") as $file) {
            self::setStatus(self::STATUS_FILE_LOADING, array("core/" . basename($file)));
            
            if (!require_once ($file)) {
                self::setStatus(self::STATUS_FILE_LOAD_FAIL);
                self::stop();
            }
            
            $name = basename($file, ".core.php");
            $className = ucfirst($name);
            $$name = new $className();
            
            self::setStatus(self::STATUS_FILE_LOADED);
        }

        // Load included classes
        foreach (glob(self::$basedir . "core/*.class.php") as $file) {
            self::setStatus(self::STATUS_FILE_LOADING, array("class/" . basename($file)));
            
            if (!require_once ($file)) {
                self::setStatus(self::STATUS_FILE_LOAD_FAIL);
                self::stop();
            }
            self::setStatus(self::STATUS_FILE_LOADED);
        }

        self::setStatus(self::STATUS_RUNNING, array(self::$conf['server']['ip'], self::$conf['server']['port']));

        while (self::$status != self::STATUS_FATAL && self::$status != self::STATUS_STOP) {
			Sockets::monitor();
            Sockets::runEvents();
		}
    }
    
    public static function stop() {
        self::setStatus(self::STATUS_STOP);
        exit();
    }
    
    private static function loadIni() {
        self::setStatus(self::STATUS_FILE_LOADING, array("ultimaphp.ini"));
        if (!is_file(self::$basedir . "ultimaphp.ini")) {
            self::setStatus(self::STATUS_FILE_LOAD_FAIL);
            self::stop();
        }
        
        self::$conf = array_change_key_case(parse_ini_file(self::$basedir . "ultimaphp.ini", true), CASE_LOWER);

        // Ini validations
        if (!isset(self::$conf['server'])) {
        	$iniMessage = "No [server] configuration section";
        } elseif (!isset(self::$conf['database'])) {
        	$iniMessage = "No [databe] configuration section";
        } elseif (!isset(self::$conf['accounts'])) {
        	$iniMessage = "No [accounts] configuration section";
        } elseif (!isset(self::$conf['server']['name'])) {
        	$iniMessage = "Server name not defined";
        } elseif (!isset(self::$conf['server']['ip'])) {
        	$iniMessage = "Server ip not defined";
        } elseif (!isset(self::$conf['server']['port'])) {
        	$iniMessage = "Server port not defined";
        } elseif (!isset(self::$conf['server']['timezone'])) {
        	$iniMessage = "Server timezone not defined";
        	$iniNote = "More information at: https://en.wikipedia.org/wiki/List_of_tz_database_time_zones";
        } elseif (!isset(self::$conf['server']['lang'])) {
        	$iniMessage = "Server language not defined";
        } elseif (!isset(self::$conf['server']['save_time'])) {
        	$iniMessage = "Server save time not defined";
        } elseif (!isset(self::$conf['database']['type'])) {
        	$iniMessage = "Server database type not defined";
        } elseif (!isset(self::$conf['database']['host'])) {
        	$iniMessage = "Server database host not defined";
        } elseif (!isset(self::$conf['database']['user'])) {
        	$iniMessage = "Server database user not defined";
        } elseif (!isset(self::$conf['database']['password'])) {
        	$iniMessage = "Server database password not defined";
        } elseif (!isset(self::$conf['database']['schema'])) {
        	$iniMessage = "Server database schema not defined";
        } elseif (!isset(self::$conf['accounts']['auto_create'])) {
        	$iniMessage = "Server accounts auto create not defined";
        } elseif (!isset(self::$conf['accounts']['password_crypt'])) {
        	$iniMessage = "Server accounts password encrypt not defined";
        } elseif (!isset(self::$conf['accounts']['max_chars'])) {
        	$iniMessage = "Server accounts maximoun characters per account not defined";
        } elseif (!isset(self::$conf['accounts']['char_delete_time'])) {
        	$iniMessage = "Server accounts character deletion minimum time not defined";
        } elseif (!isset(self::$conf['accounts']['login_tries'])) {
        	$iniMessage = "Server accounts login tries not defined";
        } elseif (!isset(self::$conf['accounts']['login_tries_block_time'])) {
        	$iniMessage = "Server accounts login tries block time not defined";
        } elseif (!isset(self::$conf['accounts']['allow_crypt'])) {
        	$iniMessage = "Server accounts allow crypt not defined";
        } elseif (!isset(self::$conf['accounts']['allow_nocrypt'])) {
        	$iniMessage = "Server accounts allow nocrypt not defined";
        } elseif (!isset(self::$conf['accounts']['ConnectingMaxIp'])) {
        	$iniMessage = "Server accounts maximoun connection per ip not defined";
        }

        $ip = explode(".", self::$conf['server']['ip']);

		if (count($ip) > 4 || count($ip) < 4) {
			$iniMessage = "The defined server IP is not valid";
		} else {
			foreach ($ip as $key => $value) {
				if (!(int)$value && $value !== "0") {
					$iniMessage = "The defined server IP is not valid";
					break;
				} elseif ($value > 255) {
					$iniMessage = "The defined server IP is not valid";
					break;
				} elseif ($value < 0) {
					$iniMessage = "The defined server IP is not valid";
					break;
				}
			}
		}

        if (isset($iniMessage)) {
        	self::log($iniMessage . " in ultimaphp.ini.", self::LOG_ERROR);
        	if (isset($iniNote)) {
        		self::log($iniNote, self::LOG_NORMAL);
        	}
        	self::stop();
        }

        self::$servers[] = array(
            'name' => UltimaPHP::$conf['server']['name'],
            'ip' => UltimaPHP::$conf['server']['ip'],
            'port' => UltimaPHP::$conf['server']['port'],
            'full' => (UltimaPHP::$conf['server']['max_players'] == 0 ? 0 : ceil((UltimaPHP::$clients/UltimaPHP::$conf['server']['max_players']) * 100)),
            'timezone' => UltimaPHP::$conf['server']['timezone']
        );

        self::setStatus(self::STATUS_FILE_LOADED);
    }

    public static function setStatus($status, $args = array()) {
        switch ($status) {
            case self::STATUS_START:
                $message = "Starting server";
                $type = self::LOG_NORMAL;
                break;

            case self::STATUS_STOP:
                $message = "Stoping server";
                $type = self::LOG_NORMAL;
                break;

            case self::STATUS_FATAL:
                $message = "Server crashed suddenly";
                $type = self::LOG_DANGER;
                $shutdown = true;
                break;

            case self::STATUS_FILE_LOADING:
                $message = "Loading file: " . $args[0];
                $type = self::LOG_NORMAL;
                break;

            case self::STATUS_FILE_LOAD_FAIL:
                $message = "Loading file failed";
                $type = self::LOG_DANGER;
                break;

            case self::STATUS_FILE_LOADED:
                $message = NULL;
                $type = self::LOG_NORMAL;
                break;

            case self::STATUS_UNKNOWN:
                $message = "Unknown status set";
                $type = self::LOG_WARNING;
                break;

            case self::STATUS_LISTENING:
            	$message = "Server is listening on " . $args[0] . " at port " . $args[1];
            	$type = self::LOG_NORMAL;
            	break;
            case self::STATUS_RUNNING:
                $message = "Server is running on " . $args[0] . " at port " . $args[1];
                $type = self::LOG_NORMAL;
                break;

            default:
                $message = "Wrong status set. restoring last status.";
                $type = self::LOG_DANGER;
                $status = self::$status;
                break;
        }
        self::$status = $status;
        self::log($message, $type);

        if (isset($shutdown)) {
        	self::stop();
        }
    }
    
    public static function log($message = NULL, $type = "NORMAL") {
    	if ($message !== NULL && $type !== NULL) {
        	echo date("H:i:s") . ($type != self::LOG_NORMAL ? " (" . $type . ") " : "") . ": " . $message . "\n";
	    }
    }
}
?>