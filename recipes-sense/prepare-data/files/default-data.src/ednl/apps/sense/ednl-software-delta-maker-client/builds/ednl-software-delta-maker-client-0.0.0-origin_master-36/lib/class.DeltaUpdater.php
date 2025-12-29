<?php
/**
 * CmdLineApp
 *
 * PHP Version 5
 *
 * @category CLI_App
 * @package  EdnlSoftwareDeltaUpdate
 * @author	 Robert Verspuy <robert@exa.nl>
 * @license	 (c) 2015 EDNL
 * @link	 https://www.sensecloud.nl
 */

require_once __DIR__.'/../lib/class.ExaLog.php';
require_once __DIR__.'/../lib/class.CmdLineApp.php';

/** CmdLineApp class
 *
 * @category CLI_App
 * @package  EdnlSoftwareDeltaUpdate
 * @author   Robert Verspuy <robert@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     https://www.sensecloud.nl
 */

class DeltaUpdater extends CmdLineApp {
	public $AppUpdate;
	public $url                 = 'http://builds.exa.nl/delta/';
	public $defaultDir          = '/home/app/sense/';
	public $tmpfile             = '/tmp/deltaUpdaterReturnData';
	public $AppArray            = array(
		'apps' => array(),
		'exitcode' => '0'
		);
	public $extraoptions        = array(
		'flags' => array (
			//When set runs ansible always to apply symlink from ./dev to ./current
			'vagrant' => array(false,"always runs ansbile app when true"), 
			'beta' => array(false,"Request BETA versions"),
			'random' => array(false, "Use a random delay after startup"),
		),
		'options' => array (
			'name' => array('', 'Name of the App'),
			'build' => array('', 'Build number of the app'),
			'appversion' => array('', 'Version number of the app'),
			'branch' => array('', 'Branch of the app'),
			'target' => array('production', 'Target of the app'),
			'dev_gmc' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-gatestate-module-controller
			'dev_smc' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-controller
			'dev_smp' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-portal
			'dev_smps' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-portalserver
			'dev_smd' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-debugger
			'dev_smh' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-hardwaretester
			'dev_smhc' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-healthcheck
			'dev_ect' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-common-tools
			'dev_ew' => array('n', 'Enable locale symlink to local develop'), //enable local version for exa-watchdog
			'dev_dmc' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-software-delta-maker-client
			'dev_smsp' => array('n', 'Enable locale symlink to local develop'), //enable local version for ednl-sense-module-serial-proxy
		)
	);
	public $applicationAndShort = array( //application shorts used to start Ansible
			'ect' => 'ednl-common-tools',
			'ew' => 'exa-watchdog',
			'gmc' => 'ednl-gatestate-module-controller',
			'smc' => 'ednl-sense-module-controller',
			'smh' => 'ednl-sense-module-hardwaretester',
			'smhc' => 'ednl-sense-module-healthcheck',
			'smps' => 'ednl-sense-module-portalserver',
			'smp' => 'ednl-sense-module-portal',
			'dmc' => 'ednl-software-delta-maker-client',
			'smsp' => 'ednl-sense-module-serial-proxy',
		);

	/**
	 * Constructor
	 *
	 * @param array $settingsargs Default arguments (usually from settings file)
	 */
	public function __construct($settingsargs = array()) {
		global $l; // starts logging plugin
		$this->l = $l->start(__CLASS__);

		parent::__construct($settingsargs);
		//Sets dir/file where data will be stored
		$this->tmpfile = __DIR__.'/../tmp/returndata';
	}

	/**
	 * main class
	 *
	 * @return void
	 */
	public function main() {
		$return = parent::main();
		if ($return !== true) {
			return $return;
		}

		if ($this->args['random']) {
			$rand = rand(0, 999);
			$this->l->fatal('Waiting '.$rand.' seconds...');
			sleep($rand);
		}

		$this->scanAppDir(); // scans /home/app/sense for applications
		$this->readbuildjson(); //reads build.json(if it exists) from application dirs
		if ($this->sendToServer()) {//sends current builds to server(returns boolean)
			$this->processReceivedData();// procces return data(for example ah update)
		}

		if ($this->args['vagrant']) {
			//Runs ansible for every application to set current symlink
			$this->vagrantModes();
		}

		exit($this->AppArray['exitcode']);
	} 

	/**
	 * Gathers directory apps
	 *
	 * @return directorys
	 */
	public function scanAppDir() {
		$scandir    = scandir($this->defaultDir);//scans /home/app/sense
		$unsetArray = $this->createUnsetArray();//creates array to unset . and ..

		$scandir = array_diff($scandir, $unsetArray);
		$this->procesDirs($scandir); //sets name for applicatie in $this->apparray
	}

	/**
	 * removes applicaties that have dev symlink enabled
	 *
	 * @return array with removable items
	 */
	public function createUnsetArray() {
		$devLink = $this->args["dev_dmc"];
		$array   = array( '.', '..');

		if (strcmp($devLink, 'y') == 0) {
			//Adds dmc to unset array because it will always update
			array_push($array, 'ednl-software-delta-maker-client');
		}

		return $array;
	}
	
	/**
	 * Proces content from AppsToArray
	 *
	 * @param array $Dirs array with found dirs
	 * @return void
	 */
	public function procesDirs($Dirs) {
		foreach ($Dirs as $Dir) {
			if (is_link($this->defaultDir.'/'.$Dir)) {
				$this->l->debug('Ignoring app '.$Dir.', because this is a symlink');
			} else {
				$this->l->debug('Found app '.$Dir);
				$this->AppArray['apps'][$Dir] = array('name' => $Dir);
			}
		}
	}

	/**
	 * Reads build.json file,
	 * if build.json is empty it sends a warning,
	 * but wil continue without any values(eg requesting a full update)
	 *
	 * @return unknown
	 */
	public function readbuildjson() {
		foreach ($this->AppArray['apps'] as $appName => $App) {
			$json      = array(
				'name' => $appName,
				'branch' => $this->setBranch(), //converts env to branch
				'version' => null,
				'build' => null,
			);
			$buildjson = $this->appDir($App, 'current') . '/build.json';

			if (file_exists($buildjson)) {
				$filecontent = file_get_contents($buildjson);

				if (!empty($filecontent)) {
					$json = $this->processBuildJson($filecontent);
				} else {
					$msg = 'Build.json is empty for '.$appName;
					$this->l->warn($msg);
					$this->errorNotifier($msg, $msg);
				}
			} else {
				$this->l->warn('Could not find build.json for '.$appName);
			}

			$json['md5sum']                   = $this->getMd5sum($json);
			$this->AppArray['apps'][$appName] = $json;

			//Checks if there is an old version to use with the delta			
			if (!$this->checkOldZip($json)) {
				$this->AppArray['apps'][$appName]['build']   = null;
				$this->AppArray['apps'][$appName]['version'] = null;
			}
		}
	}

	/**
	 *	Proccesses Json form build.json
	 *
	 *  @param array $filecontent array with found dirs
	 *	@return array with processed json
	 */
	public function processBuildJson($filecontent) {
		$json = json_decode($filecontent, true);
		//Correct / to _ (for usage in filenames
		$json['branch'] = $this->fixBranch($json['branch']);
		unset($json['created']);
		$msg  = 'Found build info: ';
		$msg .= strtr(var_export($json, true), array("\n" => ""));
		$this->l->debug($msg);

		return $json;
	}

	/**
	 * Generates the md5sum of
	 *
	 * @param array  $App array with app values
	 * @param string $new string for naming
	 * @return md5sum of file
	 */
	public function getMd5sum($App, $new = '') {
		$pathdw   = $this->defaultDir . '/' . $App['name'] . '/download/';
		$pathcrnt = $this->defaultDir . '/' . $App['name'] . '/current/';
		$rv       = null;

		if (is_dir($pathcrnt)) {
			$App['extra'] = '-compiled';
			$fileName     = $this->getName($App, 'full', $new);// get name from symlink
			$locationdw   = $pathdw . $fileName;

			if (file_exists($locationdw)) {
				//Returns md5 of zip file matching the current symlink
                                $rv = md5_file($locationdw);
			}
		}

                return $rv;
	}

	/**
	 * set branch with target
	 *
	 * @return Branch
	 */
	public function setBranch() {
		//Setup this way to always use master if target is unknown
		if (strcmp($this->args['target'], 'test') == 0) {
			$rv = 'origin_develop';
			return $rv;
		} else {
			$rv = 'origin_master';
			return $rv;
		}
			
	}

	/**
	 * replaces the / in branch with _
	 *
	 * @param string $branch branch of app
	 * @return branch
	 */
	public function fixBranch($branch) {
		//Simple str replace to replace / for usage in filenames
		$rv = str_replace('/', '_', $branch);

		return $rv;
	}

	/**
	 * Generates json to send to server
	 *
	 * @return json
	 */
	public function genJson() {
		//$json = json_encode(array('apps' => $this->AppArray['apps']));
		$data['apps'] = array();
		foreach ($this->AppArray['apps'] as $k => $v) {
			$data['apps'][] = $v;
		}

		$data['beta'] = $this->args['beta'];

		$json = json_encode($data);

		return $json;
	}

	/**
	 * communicate with the server and recives the answer
	 *
	 * @return data from the server
	 */
	public function sendToServer() {
		$data = $this->genJson();
		$ch	  = curl_init($this->url);
		$fh   = fopen($this->tmpfile, 'w'); //opens ./tmp/returndata

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FILE, $fh);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data))
		); 
		//Checks for internet connection to minimize error messages
		if ($this->isConnected()) {
			$result = curl_exec($ch);
			fclose($fh);
			$curlinfo = curl_getinfo($ch);
			//Checks if url was successfull, creates error if it failed
			if (!in_array($curlinfo['http_code'], array('200'))) {
				$text  = 'DMC - Curl download failed with ';
				$msg   = $text.$curlinfo['http_code'];
				$body  = '';
				$body .= 'URL: '.$this->url."\n";
				$body .= 'Data: '.var_export($data, true)."\n";
				$body .= 'Curl Info: '.var_export($curlinfo, true)."\n";
				$this->errorNotifier($msg, $body);
				curl_close($ch);
				return false;
			} else {
				curl_close($ch);
				return true;
			}
		}

	}

	/**
	 * processes data recived form the server
	 *
	 * @return void
	 */
	public function processReceivedData() {
		$fh = fopen($this->tmpfile, 'r');
		//Retrieves headers (how many updates and the boundary)
		if ($this->getHeaders($fh)) {
			for ($i = 1; $i <= $this->AppArray['updates']; $i++) {
				if ($this->checkBoundary($fh)) {
					//Retrieves applications headers (apname, build, update type etc.)
					$name = $this->getAppHeaders($fh);
					//Gets update from tmp file
					$this->getAppUpdate($fh, $this->AppArray['apps'][$name]);
	
					if ($this->checkBoundary($fh) && $this->emptyLine($fh)) {
						//Parameter thats used to check if updates has successfully writen to disk
						$this->AppArray['apps'][$name]['updateondisk'] = true;
					}

					//Application list used by ansible
					$this->installApp($this->AppArray['apps'][$name]);
				}
			}
		}
	}

	/**
	 * handels data from the processor
	 *
	 * @param string $fh fopen for the file
	 * @return void
	 */
	public function getHeaders($fh) {
		$finish = false;

		while (!feof($fh) && !$finish) {
			$fg = fgets($fh);

			//Test line in format: app:<name>
			if (preg_match('/^([a-z0-9]*):(.*)$/', trim($fg), $match)) {
				$this->AppArray[$match[1]] = $match[2];
				$this->l->trace('Received Header '.$match[1].': '.$match[2]);
			} elseif (preg_match('/^$/', trim($fg))) {
				$finish = true;
			} else {
				$msg  = "DMC - Unknown line in Headers";
				$body = $fg;
				$this->errorNotifier($msg, $body);
				return false;
			}
		}

		return true;

	}

	/**
	 * handels data from the processor
	 *
	 * @param string $fh fopen for the file
	 * @return void
	 */
	public function getAppHeaders($fh) {
		$finish = false;
		$rx     = array(
			'/^app:(.*)$/',
			'/^([a-z0-9]*):(.*)$/'
		);

		$appName = 'unknown';

		while (!feof($fh) && !$finish) {
			$fg = fgets($fh); //reads line for line

			if (preg_match($rx[0], $fg, $match)) {//finds appname
				$appName = $match[1];
				$this->l->trace('App header for '.$appName);
			} elseif (preg_match($rx[1], $fg, $match)) {//finds everything else
				$this->AppArray['apps'][$appName][$match[1]] = $match[2];
				$this->l->trace('App header '.$match[1].': '.$match[2]);
			} elseif (preg_match('/^$/', $fg)) {// finds empty line
				$this->AppArray['apps'][$appName]['readedbytes'] = 0;
				$finish                                          = true;
			} else {
				$msg = 'DMC - Unexpected App header';
				$this->errorNotifier($msg, $body);
			}
		}

		return $appName;
	}

	/**
	 * second stap in retrieving application update. this gets the update
	 *
	 * @param string $fhr fopen for the file
	 * @param string $App the App to procces
	 * @return void
	 */
	public function getAppUpdate($fhr, $App) {
		//Gets filename
		$FileName = $this->getName($App, null, 'new');
		//Sets folder location based on update
		$FolderLocation = $this->appDir($App, 'download');
		if (!file_exists($FolderLocation)) {
			mkdir($FolderLocation);
		}

		$FullPath = $FolderLocation.'/'.$FileName;
		$fhw      = fopen($FullPath, 'w');
		$complete = false;

		$this->l->info('Extracting '.$App['size'].' bytes to '.$FullPath);

		//Checks if all the bytes are writen to the update
		while ($this->completecheck($App)) {
			$Bytes   = $this->nextBytes($App);
			$Content = fread($fhr, $Bytes);
			fwrite($fhw, $Content);
		}

		$this->emptyLine($fhr);
	}

	/**
	 * calculates following bytes
	 *
	 * @param string $App the app to procces
	 * @return void
	 */
	public function completecheck($App) {
		$size   = $this->AppArray['apps'][$App['name']]['size'];
		$readed = $this->AppArray['apps'][$App['name']]['readedbytes'];

		if ($size == $readed) {//checks if all the bytes are writen to the update
			return false;
		} elseif ($readed < $size) {
			return true;
		}
	}

	/**
	 * calculates following bytes
	 *
	 * @param string $App the app to procces
	 * @return void
	 */
	public function nextBytes($App) {
		$readedbytes = $this->AppArray['apps'][$App['name']]['readedbytes'];
		$updatesize  = $this->AppArray['apps'][$App['name']]['size'];
		$bytestoread = $readedbytes + 1024;

		if ($bytestoread <= $updatesize) {
			$this->AppArray['apps'][$App['name']]['readedbytes'] += 1024;

			$rv = 1024;

			return $rv;
		} else {
			$rv = $updatesize - $readedbytes;

			$this->AppArray['apps'][$App['name']]['readedbytes'] += $rv;

			return $rv;
		}
	}

	/**
	 * check if the boundary is on the place were it's expected to be
	 *
	 * @param string $fh fopen for the file
	 * @return true or false
	 */
	public function checkBoundary($fh) {
		$fg    = fgets($fh);
		$regex = '/^--=_' . $this->AppArray['boundary'] . '$/';

		if (preg_match($regex, $fg)) {
			return true;
		} else {
			$msg   = 'DMC - Could not find boundary';
			$body  = 'boundary: '.$this->AppArray['boundary']."\n";
			$body .= 'Found line '.$fg."\n";
			$this->errorNotifier($msg, $body);
			return false; //error
		}
	}

	/**
	 * returns filename based on input
	 *
	 * @param string $App      app to make filename for
	 * @param string $nameType what type of name should be returnd?
	 * @param string $new      sets version to get from array
	 * @return the requested name
	 */
	public function getName($App, $nameType = null, $new = '') {
		if (!isset($App['extra'])) {
			$App['extra'] = '';
		}

		if (is_null($nameType)) {
			$nameType = $this->AppArray['apps'][$App['name']]['updatetype'];
		}

		if (strcmp($nameType, 'delta') == 0) {//make filename for delta package
			$Name  = $App['name'] . '-' . $App['newversion'] . '-';
			$Name .= $App['newbranch'] . '-' . $App['newbuild'];
			$Name .= '.delta.' . $App['version'] . '_' . $App['branch'];
			$Name .= '-' . $App['build'];
		} elseif (strcmp($nameType, 'full') == 0) {//makes filename for a full update
			$Name  = $App['name'] . '-' . $App[$new . 'version'] . '-';
			$Name .= $App[$new . 'branch'] . '-' . $App[$new . 'build'];
			//$Name .= $App['extra']. '.zip';
			$Name .= '.zip';
		} elseif (strcmp($nameType, 'folder') == 0) {// makes folder name
			$Name  = $App['name'] . '-' . $App['newversion'] . '-';
			$Name .= $App['branch'] . '-' . $App['newbuild'];
		} else {
			$msg  = 'Unknown nameType '.$nameType;
			$body = $msg;
			$this->errorNotifier($msg, $body);
			die();
		}

		return $Name;
	}

	/**
	 * checks if there's a empty line
	 *
	 * @param string $fh fopen for the file
	 * @return true or false
	 */
	public function emptyLine($fh) {
		$fg = fgets($fh);


		if (preg_match('/^\s*$/', $fg )) {
			return true;
		} else {
			return false; //error
		}
	}

	/**
	 *	installs app 
	 *
	 * @param string $App app name to process
	 * @return void
	 */
	public function installApp($App) {
		$ok = true;
		//If delta update, generate full update with delta file
		if (strcmp($App['updatetype'], 'delta') == 0) {
			$ok = $this->mergeDelta($App);
		// Full update
		} elseif (strcmp($App['updatetype'], 'full') == 0) {
			$AppNewZip = $this->getName($App, 'full', 'new');
			$AppDir    = $this->appDir($App, 'download');
			$ok        = $this->md5Zip($AppDir.'/'.$AppNewZip, $App);
		}

		if ($ok) { 
			$this->runAnsible($App);
		}
	}	

	/**
	 * merge delta in to old version
	 *
	 * @param string $App app name to process
	 * @return void
	 */
	public function mergeDelta($App) {
		$AppOldZip = $this->getName($App, 'full');
		$AppNewZip = $this->getName($App, 'full', 'new');
		$AppDelta  = $this->getName($App, 'delta');
		$AppDir    = $this->appDir($App, 'download');
		$command   = "cd $AppDir; bspatch $AppOldZip $AppNewZip $AppDelta";
		$this->l->debug('Running patch command: '.$command);

		$ok = false;
		if ($this->md5Delta($AppDir.'/'.$AppDelta, $App)) {	
			exec($command, $output, $rv); //merges delta with the latest full zip
			if ($rv != 0) {
				$msg  = 'DMC - Patch failed, rv = '.$rv;
				$body = implode("/", $output);
				$this->errorNotifier($msg, $body);
				return false;
			}

			$ok = $this->md5Zip($AppDir.'/'.$AppNewZip, $App);
		}

		return $ok;
	}

	/**
	 * gives app installed directory
	 *
	 * @param string $App app name for dir
	 * @param string $wd  directory to work in
	 * @return app directory
	 */
	public function appDir($App, $wd) {
		$path = $this->defaultDir . $App['name'] . '/'. $wd;

		return $path;
	}

	/**
	 * Checks md5 of file
	 *
	 * @param string $Path Path to check
	 * @param string $App  App to check
	 * @return true or false
	 */
	public function md5Zip($Path, $App) {
		$md5File = md5_file($Path);

		if (strcmp($md5File, $App['filemd5sum']) == 0) {
			return true;
		} else {
			$msg   = "DMC - MD5 Zip does not match";
			$body  = "ZipFile: ".$Path."\n";
			$body .= "MD5 should be: ".$App['filemd5sum']."\n";
			$body .= "MD5 really is: ".$md5File."\n";
			$this->errorNotifier($msg, $body);
			return false;
		}
	}

	/**
	 * Checks md5 of delta
	 *
	 * @param string $Path Path to check
	 * @param string $App  App to check
	 * @return true or false
	 */
	public function md5Delta($Path, $App) {
		$md5Delta = md5_file($Path);
		
		if (strcmp($md5Delta, $App['deltamd5sum']) == 0) {
			return true;
		} else {
			$msg   = "DMC - MD5 Delta does not match";
			$body  = "DeltaFile: ".$Path."\n";
			$body .= "MD5 should be: ".$App['deltamd5sum']."\n";
			$body .= "MD5 really is: ".$md5Delta."\n";
			$this->errorNotifier($msg, $body);
			return false;
		}
	}

	/**
	 * start ansbile to update the app
	 *
	 * @param string $App App to install with ansible
	 * @return void
	 */
	public function runAnsible($App) {
		$appShort = $this->genAppShort($App);//fils array with appshorts
		$args     = $this->args;

		if ($args['vagrant']) {
			$fileName = $this->getName($App, 'full');
		} else {
			$fileName = $this->getName($App, 'full', 'new');
		}

		if ($appShort !== false) {
			$command  = __DIR__.'/../bin/run-ansible.sh '. $appShort .' ';
			$command .= $fileName . ' dev_smc=' . $args['dev_smc'];
			$command .= ' dev_gmc=' . $args['dev_gmc'];
			$command .= ' dev_smp=' . $args['dev_smp'];
			$command .= ' dev_smps=' . $args['dev_smps'];
			$command .= ' dev_smh=' . $args['dev_smh'];
			$command .= ' dev_smhc=' . $args['dev_smhc'];
			$command .= ' dev_ect=' . $args['dev_ect'];
			$command .= ' dev_ew=' . $args['dev_ew'];
			$command .= ' dev_dmc=' . $args['dev_dmc'];
			$command .= ' dev_smsp=' . $args['dev_smsp'];

			$spec = array(
				array('pipe', 'r'),
				array('pipe', 'w'),
				array('pipe', 'w'),
			);

			$env = array(
				'PYTHONUNBUFFERED' => 1,
			);

			//Proc_open writes messages directly to stdout
			$proc = proc_open($command, $spec, $pipes, null, $env);
				while (!feof($pipes[1])) {
				$line = fgets($pipes[1]);
				print $line;
				}

			$errors = '';
			while (!feof($pipes[2])) {
				$errors .= fgets($pipes[2]);
			}

			if (strcmp(trim($errors), '') != '') {
				print "\n== ERRORS ==\n";
				print $errors."\n";
			}

			$this->getStatus($proc, $errors, $App);

			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($proc);
		}
	}

	/**
	 * get proc status
	 *
	 * @param string $proc   Current running procces
	 * @param string $errors error message when things go wrong
	 * @param string $App    App to install with ansible
	 * @return void
	 */
	public function getStatus($proc, $errors, $App) {
		$status = $this->getExitCode($proc);

		if (!$status['exitcode'] == 0) {
			$txt = 'Ansible failed for ';
			$this->l->fatal($txt . $App['name']);
			$msg   = "DMC - Ansible Failed";
			$body  = "App: ".$App['name']."\n";
			$body .= "Error Log: ".$errors."\n";
			$this->errorNotifier($msg, $body);
			return false;
		}
	}

	/**
	 * get exit code
	 *
	 * @param string $proc Current running procces
	 * @return status
	 */
	public function getExitCode($proc) {
		$status = proc_get_status($proc);
		while ($status["running"]) {
			sleep(0.5);
			$status = proc_get_status($proc);
		}

		$this->setExitCode($status);
		return $status;
	}

	/**
	 * set exit code
	 *
	 * @param string $status exit code form ansible
	 * @return void
	 */
	public function setExitCode($status) {
		$appArray = $this->AppArray;

		if ($appArray['exitcode'] < $status['exitcode']) {
			$this->AppArray['exitcode'] = $status['exitcode'];
		}
	}

	/**
	 * Generate appshort
	 *
	 * @param string $App app to generate appshort from
	 * @return appshort
	 */
	public function genAppShort($App) {

		$shortcode = array_search($App['name'], $this->applicationAndShort);

		if ($shortcode === false) {	
			$msg  = 'Unknown appname '.$App['name'];
			$body = $msg;
			$this->errorNotifier($msg, $body);
		}

		return $shortcode;
	}

	/**
	 * checks if the old build zip exists
	 *
	 * @param string $App App to install with ansible
	 * @return true or false
	 */
	public function checkOldZip($App) {
		$AppZip = $this->getName($App, 'full');
		$AppDir = $this->appDir($App, 'download');
		$file   = $AppDir.'/'.$AppZip;

		if (file_exists($file)) {
			return true;
		} else {
			$App['extra'] = '-compiled';
			$AppZip       = $this->getName($App, 'full');
			$AppDir       = $this->appDir($App, 'download');
			$file         = $AppDir.'/'.$AppZip;
			if (file_exists($file)) {
				return true;
			} else {	
				$txt = 'Could not find old zip for ';
				$this->l->warn($txt . $App['name'].': '.$file);
				return false;
			}
		}
	}

	/**
	 *  
	 * @return void
	 *  
	 * @param string $reason reason for the errornotifier
	 * @param string $body   full description and logging for error
	 */ 
	public function errorNotifier($reason, $body) {
		$this->l->error('errorNotifier: '.$reason);
		$time     = time();
		$filename = '/var/ednl/errors/'. $time . '-'. $reason;
		$fh       = fopen($filename, 'a');

		fwrite($fh, $body);
		fclose($fh);
	}

	/**
	 *  
	 * @return true/false
	 *  
	 */ 
	public function isConnected() {
		$i = 0;

		//Tries 3 times to open builds.exa.nl, when successfull it continues
		while ($i <= 3) {
			$connected = fsockopen("builds.exa.nl", 80, $errno, $errstr, 20);
			if ($connected) {
				fclose($connected);
				return true;
			} elseif ($i == 3) {
				echo "Internet connectiond failed";
				exit(1);
			}

			$i++;
		}
	}

	/**
	 *
	 *
	 *
	 * @return void
	 */
	public function vagrantModes() {
		foreach ($this->AppArray['apps'] as $App) {
			//When app is on disk, ansible is triggered to run and update
			if (!isset($App['updateondisk'])) {
				//Otherwise run ansible for every aplication
				$this->runAnsible($App);
			}
		}
	}

}
