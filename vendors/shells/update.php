<?php
class UpdateShell extends Shell {
	var $Folder = null;

	function initialize() {
		parent::initialize();

		$this->Folder = new Folder();
	}

	function main() {
		$this->out('CakePHP Updater');
		$this->hr();

		$plugins = Configure::listObjects('plugin');


		$toUpdate = array();
		if (!empty($this->args)) {
			$toUpdate = $this->args;

			if (count($toUpdate) == 1 && $toUpdate[0] == 'all') {
				$toUpdate = $plugins;
			}
		} else {
			$this->out('a: all');
			foreach($plugins as $i => $plugin) {
				$this->out($i + 1 . ': ' . $plugin);
			}

			$toUpdate = $this->in('Select Plugin ("q" to quit, "?" for help):');
			$toUpdate --;

			switch (strtolower($toUpdate)) {
				case 'q':
					exit(0);
				case 'a':
					$toUpdate = $plugins;
					break;
				default:
					if (empty($plugins[$toUpdate])) {
						$toUpdate = array();
					} else {
						$toUpdate = array($plugins[$toUpdate]);
					}
					break;
			}
		}

		foreach($toUpdate as $plugin) {
			$this->out('Updating ' . $plugin . '...');

			$path = APP . 'plugins' . DS . Inflector::underscore($plugin);
			if (!$this->Folder->cd($path)) {
				$this->out('ERROR: plugin not found at ' . $path);
				continue;
			}

			list($dirs, $files) = $this->Folder->read(false);

			$isGit = array_values(preg_grep('/^\.git$/i', $dirs));
			$isSvn = array_values(preg_grep('/^\.svn$/i', $dirs));

			if (!$isGit && !$isSvn) {
				$this->out('ERROR: plugin is not version controlled.');
			}

			chdir($path);

			if ($isGit) {
				unset($result);
				exec('git pull origin master', $result, $code);

				if ($code == 0) {
					$this->out($result[0]);
				} else {
					$this->out($result);
				}
			}

			if ($isSvn) {
				unset($result);
				exec('svn update', $result, $code);

				if ($code == 0) {
					$this->out($result[0]);
				} else {
					$this->out($result);
				}
			}

			$this->out('');
		}

		if ($this->args) {
			exit(0);
		}

		$this->hr();
		$this->main();
	}

}
?>