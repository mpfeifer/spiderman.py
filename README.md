# spiderman.py
- Knocking on port 80 on random hosts
- Found hosts are stored in database with columns ip, timestamp, port and hostname.
- For example:
```
CREATE TABLE `active_hosts` (
	`ip` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`timestamp` TIMESTAMP NULL DEFAULT NULL,
	`port` INT(11) NULL DEFAULT NULL,
	`hostname` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci'
)
```
- I have used https://mysqlclient.readthedocs.io/ to interact with the database
