<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Task_Crontab_Register_Add extends Minion_Task
{

        protected $_config = array(
                'schedule',
                'stask',
                'stask_options',
                'user'
        );

	public function execute(array $config)
	{
		try {
			$schedule = Arr::get($config, 'schedule',NULL);
			$task = Arr::get($config, 'stask',NULL);
			$task_options = Arr::get($config, 'stask_options','');
			$user = Arr::get($config, 'user',NULL);

			$mcr = new Minion_Crontab_Register();
			if (!$mcr->add_minion_task($schedule,$task,$task_options,$user))
				echo "Crontab entry already exists!";
			else
				echo "Crontab updated";
		} catch (Exception $e)
		{
			echo "Unable to set crontab entry! $e";
		}
	}

}
