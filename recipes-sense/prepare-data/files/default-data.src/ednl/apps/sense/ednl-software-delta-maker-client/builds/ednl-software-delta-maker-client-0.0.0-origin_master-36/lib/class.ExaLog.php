<?php
/**
 * ExaLog
 *
 * PHP Version 5
 *
 * @category Logging
 * @package  EdnlGatestateModule
 * @author   Robert Verspuy <robert@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     https://www.gatestate.nl
 */

define('LOG4PHPDIR', __DIR__.'/../3rdparty/apache-log4php-2.3.0/src/main/php');
require_once LOG4PHPDIR.'/Logger.php';

/** ExaLog class
 *
 * @category Logging
 * @package  EdnlGatestateModule
 * @author   Robert Verspuy <robert@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     https://www.gatestate.nl
 */
class ExaLog {

	public $logfile = null;
	public $loggers = array();
	public $levels  = array(
		'console' => 'INFO',
		'logfile' => 'DEBUG',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		global $argv;

		$this->l = $this->start(__CLASS__);
		$this->setLogfile();
	}

	/**
	 * Set Logfile
	 *
	 * @param string $name Name of logfile
	 *
	 * @return void
	 */
	public function setLogfile($name = null) {
		global $argv;

		if (isset($name)) {
			$this->logfile = $name;
		} elseif (isset($argv[0])) {
			$this->logfile = basename($argv[0]);
		} elseif (isset($_SERVER['SERVERNAME'])) {
			$this->logfile = $_SERVER['SERVERNAME'];
		} else {
			$this->logfile = 'default';
		}
	}

	/**
	 * Set debug level
	 *
	 * @param string $levelname Type of logging
	 * @param string $level     Level of debugging for $levelname
	 *
	 * @return void
	 */
	public function setlevel($levelname,$level) {
		$this->levels[$levelname] = $level;
		$this->reconfigure();
	}

	/**
	 * Start logging
	 *
	 * @param string $name Name of logging
	 *
	 * @return object Logging class
	 */
	public function start($name) {
		global $argv;

		if (isset($this->loggers[$name])) {
			$this->loggers[$name]->trace("Continueing ".$name);
		} else {
			$this->reconfigure();
			$this->loggers[$name] = Logger::getLogger($name);
			$this->loggers[$name]->trace("Starting ".$name);
		}

		return $this->loggers[$name];
	}

	/**
	 * Reconfigure logging
	 *
	 * @return void
	 */
	public function reconfigure() {
		$logfile = __DIR__.'/../log/'.$this->logfile.'.log';

		$config = array(
/*
			'loggers' => array(
				'gateStateDaemon' => array(
					'level' => 'TRACE',
					'appenders' => array('console','logfile'),
				),
			),
*/
			'rootLogger' => array(
				'level' => 'TRACE',
				'appenders' => array('console','logfile'),
			),
			'appenders' => array(
				'console' => array(
					'class' => 'LoggerAppenderConsole',
					'layout' => array(
						'class' => 'LoggerLayoutSimple',
					),
					'threshold' => $this->levels['console'],
				),
				'logfile' => array(
					'class' => 'LoggerAppenderRollingFile',
					'layout' => array(
						'class' => 'LoggerLayoutPattern',
						'params' => array(
							'conversionPattern' =>
								'%d{Y-m-d H:i:s} [%p] [%logger]  %c: %m (at %F line %L)%n',
						),
					),
					'params' => array(
						'file' => $logfile,
						'append' => true,
						'maxBackupIndex' => '5',
						'maxFileSize' => '10MB',
					),
					'threshold' => $this->levels['logfile'],
				),
			),
		);
		Logger::configure($config);
	}

}

global $l;
$l = new ExaLog();
