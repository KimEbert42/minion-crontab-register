minion-crontab-register
=======================

Minion task for Kohana to help manage register tasks using the crontab command line

Example of how to add crontab entry.

./minion crontab:register:add --stask="migrations:run" --schedule="*/1 * * * *"
