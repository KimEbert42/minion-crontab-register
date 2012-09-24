<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Crontab_Register {

	private $minion_path = NULL;
	private $minion_dir = NULL;

	public function __construct()
	{
		$this->minion_path = $this->_get_minion_path();
		$this->minion_dir = dirname($this->minion_path);
	}

	protected function _get_crontab($user = NULL)
	{
		$results = array();
		if ($user == NULL)
			exec("crontab -l",$results);
		else
			exec("crontab -u $user -l",$results);
		return $results;
	}

	protected function _set_crontab($entries, $user = NULL)
	{
		if (is_array($entries))
		{
			$entries = implode("\r\n",$entries);
			$entries .= "\r\n";
		}
		
		$tmpfname = tempnam("/tmp", "cron");

		file_put_contents($tmpfname, $entries);

		$data = array();
		$results = -1;

		if ($user == NULL)
			exec("crontab $tmpfname",$data,$results);
		else
			exec("crontab -u $user $tmpfname",$data,$results);

		unlink($tmpfname);

		if ($results != 0)
		{
			throw new Exception("Failed to set cron!");
		}
	}

	protected function _can_set_user()
	{
		$tmp = array();
		exec("id -u",$tmp);
		if (trim($tmp[0]) == '0')
			return true;
		return false;
	}

	protected function _get_minion_path()
	{
		if (defined('DOCROOT'))
		{
			if (file_exists(constant('DOCROOT') . 'minion'))
			{
				return constant('DOCROOT') . 'minion';
			}
		}
		throw new Exception("Cannot find minion!");
	}

	protected function _get_minion_tasks()
	{
		$tmp = array();
		exec($this->minion_path,$tmp);
		$results = array();
		foreach ($tmp as $value)
		{
			if (strpos($value,"*") !== FALSE)
				$results[trim(substr($value,strpos($value,"*") + 1))] = TRUE;
		}
		return $results;
	}

	protected function _is_valid_minion_task($task)
	{
		return array_key_exists($task,$this->_get_minion_tasks());
	}

	protected function _get_crontab_string($schedule, $task, $task_options)
	{
		$result = "$schedule ";
		$result .= "pushd $this->minion_dir 2>&1 > /dev/null ;";
		$result .= "$this->minion_path $task $task_options;";
		$result .= "popd 2>&1 > /dev/null ; ";
		return $result;
	}

	public function add_minion_task($schedule, $task, $task_options = '', $user = NULL, $allow_duplicates = FALSE)
	{
		if ($user != NULL && (! $this->_can_set_user()))
		{
			throw new Exception("Cannot specify user. Must be UID 0!");
		}

		if (! $this->_is_valid_minion_task($task))
			throw new Exception("Invalid minion task!");


		$crontab = $this->_get_crontab($user);

		$new = $this->_get_crontab_string($schedule, $task, $task_options);

		if (!$allow_duplicates)
		{
			foreach ($crontab as $entry)
			{
				if (trim($entry) == $new)
					return false;
			}
		}

		$crontab[] = $new;

		$this->_set_crontab($crontab, $user);

		return true;
	}

}

