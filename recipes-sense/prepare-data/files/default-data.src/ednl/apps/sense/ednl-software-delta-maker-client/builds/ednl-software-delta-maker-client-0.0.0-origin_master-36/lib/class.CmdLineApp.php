<?php
/**
 * CmdLineApp
 *
 * PHP Version 5
 *
 * @category Ruleset
 * @package  EdnlGatestateModule
 * @author   Robert Verspuy <robert@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     https://www.gatestate.nl
 */

/** CmdLineApp class
 *
 * @category Ruleset
 * @package  EdnlGatestateModule
 * @author   Robert Verspuy <robert@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     https://www.gatestate.nl
 */
class CmdLineApp {

	public $version = '';
	public $appname = '';
	public $args    = array();


	public $options = array(
		'flags' => array(),
		'options' => array(),
	);

	public $defoptions = array(
		'flags' => array(
			'help' => array(false,"Show help text"),
			'version' => array(false, "Show version"),
			'verbose' => array(
				false,
				'Print verbose debugging information during execution'
				),
			),
		'options' => array(
			'debug' => array('WARN', "Set debug level for console"),
			'debugfile' => array('INFO', "Set debug level for logfile"),
		),
	);

	public $extraoptions = array();

	public $l = null;

	private $_settingsargs = array();

	/**
	 * Constructor
	 *
	 * @param array $settingsargs Default arguments (usually from settings file)
	 */
	public function __construct($settingsargs = array()) {
		global $l;
		$this->l = $l->start(__CLASS__);
		$this->combineOptions();
		$this->resetArgs();
		$this->_settingsargs = $settingsargs;
	}

	/**
	 * Main method
	 *
	 * @return int return value (0=ok)
	 */
	public function main() {
		global $l;

		$return = $this->readArgs();
		if ($return > 0) {
			return $return;
		}

		$l->setLogfile(basename($this->appname));
		$l->setlevel('console', $this->args['debug']);
		$l->setlevel('logfile', $this->args['debugfile']);
		if ($this->args['help']) {
			$this->showUsage();
			return 0;
		}

		if ($this->args['version']) {
			print "Version: ".$this->version."\n";
			return 0;
		}

		if ($this->args['verbose']) {
			$this->showArgs();
		}

		return true;
	}

	/**
	 * Combine argument options
	 *
	 * @return void
	 */
	public function combineOptions() {
		foreach ($this->defoptions as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$this->options[$k1][$k2] = $v2;
			}
		}

		foreach ($this->extraoptions as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$this->options[$k1][$k2] = $v2;
			}
		}
	}

	/**
	 * Reset arguments
	 *
	 * @return void
	 */
	public function resetArgs() {
		foreach ($this->options['flags'] as $k => $v) {
			$this->args[$k] = $v[0];
		}

		foreach ($this->options['options'] as $k => $v) {
			$this->args[$k] = $v[0];
		}
	}

	/**
	 * Read arguments
	 *
	 * @return void
	 */
	public function readArgs() {
		global $argv;

		$this->appname = array_shift($argv);
		if (count($this->_settingsargs) > 0) {
			foreach (array_reverse($this->_settingsargs) as $arg) {
				array_unshift($argv, $arg);
			}
		}

		$amount = count($argv);

		while ($amount > 0) {
			$arg   = array_shift($argv);
			$found = false;
			foreach ($this->options['flags'] as $k => $v) {
				if (preg_match('/^--'.$k.'/', $arg)) {
					$found          = true;
					$this->args[$k] = true;
				}
			}

			foreach ($this->options['options'] as $k => $v) {
				if (preg_match('/^-'.$k.'$/', $arg)) {
					if (count($argv) > 0) {
						$found          = true;
						$argval         = array_shift($argv);
						$this->args[$k] = $argval;
					} else {
						$msg    = 'ERR Missing argument value of option -'.$k;
						$return = $this->closeApp(1, $msg, true);
						return $return;
					}
				}
			}

			if (!$found) {
				$msg    = 'ERR unknown argument '.$arg;
				$return = $this->closeApp(1, $msg, true);
				return $return;
			}

			$amount = count($argv);
		}
	}

	/**
	 * Print arguments
	 *
	 * @return void
	 */
	public function showArgs() {
		print "Running ".$this->appname."\n";
		$maxlen = 0;
		foreach ($this->args as $k => $v) {
			if (strlen($k) > $maxlen) {
				$maxlen = strlen($k);
			}
		}

		print "Arguments:\n";
		foreach ($this->args as $k => $v) {
			print sprintf('%-'.($maxlen + 8).'s %s', $k, var_export($v, true))."\n";
		}
	}

	/**
	 * Print usage
	 *
	 * @return void
	 */
	public function showUsage() {
		print "Usage:\n";
		print "\n";
		$maxlen = 0;
		foreach ($this->options['flags'] as $k => $v) {
			if (strlen($k) > $maxlen) {
				$maxlen = strlen($k);
			}
		}

		foreach ($this->options['options'] as $k => $v) {
			if (strlen($k) > $maxlen) {
				$maxlen = strlen($k);
			}
		}

		print $this->appname." (flags...) (options...)\n";
		print "\n";
		print "Flags:\n";
		foreach ($this->options['flags'] as $k => $v) {
			$msg = '--%-'.($maxlen + 8).'s %s (Default: %s)';
			print sprintf($msg, $k, $v[1], var_export($v[0], true))."\n";
		}

		if (count($this->options['options']) > 0) {
			print "Options:\n";
			foreach ($this->options['options'] as $k => $v) {
				$msg = '-%-'.($maxlen + 9).'s val %s (Default: %s)';
				print sprintf($msg, $k.' (value)', $v[1], var_export($v[0], true))."\n";
			}
		}

	}

	/**
	 * Close application
	 *
	 * @param int     $rc        Return code
	 * @param string  $msg       Message to print
	 * @param boolean $showusage Show usage
     *
	 * @return int return value (0=ok)
	 */
	public function closeApp($rc,$msg, $showusage = false) {
		if ($showusage) {
			$this->showUsage();
		}

		print "\n".$msg."\n";
		return $rc;
	}

}
