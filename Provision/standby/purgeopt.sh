#!/bin/sh

# purgeopt.sh
# Database purge and optimize

db_replication=no

keybuffer=20M
sortbuffer=20M
readbuffer=2M
writebuffer=2M

emails=(rmiller@handsfreenetworks.com)

crondelay=1800

mysqld_pidfile=/var/run/mysqld/mysqld.pid
mysql2d_pidfile=/var/run/mysqld/mysql2d.pid
httpd_pidfile=/var/run/httpd.pid
webcron_crontab=/home/webcron/crontab.txt
www_normal=/var/www/html
www_standby=/var/www/html/main/standby

logfile=/var/log/purgeopt.log
lock=/home/webcron/lock/purge.lock

quick_db_check ()
{
	dbdirectory=$1
	/usr/bin/myisamchk -s -f -F -U \
		--key_buffer_size=$keybuffer \
		--sort_buffer_size=$sortbuffer \
		--read_buffer_size=$readbuffer \
		--write_buffer_size=$writebuffer \
		$dbdirectory/*/*.MYI >> $logfile
}

db_optimize ()
{
	dbdirectory=$1
	/usr/bin/myisamchk -r -a -F \
		--key_buffer_size=$keybuffer \
		--sort_buffer_size=$sortbuffer \
		--read_buffer_size=$readbuffer \
		--write_buffer_size=$writebuffer \
		$dbdirectory/*/*.MYI >> $logfile
}

start_mysql ()
{
	/bin/echo `date` "Starting mysql" >> $logfile
	/sbin/service mysqld start >> $logfile
	/bin/sleep 10

	if [ ! -e $mysqld_pidfile ]; then
		/bin/echo `date` "Failed to start mysql" >> $logfile
		return 2
	fi
}

stop_mysql ()
{
	/bin/echo `date` "Stopping mysql" >> $logfile
	/sbin/service mysqld stop >> $logfile
	/bin/sleep 10

	if [ -e $mysqld_pidfile ]; then
	/bin/echo `date` "Stopping mysql, second attempt" >> $logfile
		/sbin/service mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ]; then
		/bin/echo `date` "Couldn't stop mysql, time for killall" >> $logfile
		/usr/bin/killall -9 mysql >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ]; then
		/bin/echo `date` "Stopping mysql following killall" >> $logfile
		/sbin/service mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ]; then
		/bin/echo `date` "Couldn't stop mysql, time for killall2" >> $logfile
		/usr/bin/killall -9 mysql >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ]; then
		/bin/echo `date` "Stopping mysql following killall 2" >> $logfile
		/sbin/service mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ]; then
		/bin/echo `date` "Failed to stop mysql" >> $logfile
		return 2
	fi
}

start_replicated_mysql ()
{
	/bin/echo `date` "Starting mysql" >> $logfile
	/etc/rc.d/init.d/mysqld start >> $logfile
	/bin/sleep 10

	if [ ! -e $mysqld_pidfile ] || [ ! -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Failed to start mysql" >> $logfile
		return 2
	fi
}

stop_replicated_mysql ()
{
	/bin/echo `date` "Stopping mysql" >> $logfile
	/etc/rc.d/init.d/mysqld stop >> $logfile
	/bin/sleep 10

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
	/bin/echo `date` "Stopping mysql, second attempt" >> $logfile
		/etc/rc.d/init.d/mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Couldn't stop mysql, time for killall" >> $logfile
		/usr/bin/killall -9 mysql >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Stopping mysql following killall" >> $logfile
		/etc/rc.d/init.d/mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Couldn't stop mysql, time for killall2" >> $logfile
		/usr/bin/killall -9 mysql >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Stopping mysql following killall 2" >> $logfile
		/etc/rc.d/init.d/mysqld stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $mysqld_pidfile ] || [ -e $mysql2d_pidfile ]; then
		/bin/echo `date` "Failed to stop mysql" >> $logfile
		return 2
	fi
}

start_apache ()
{
	/bin/echo `date` "Starting apache" >> $logfile
	/sbin/service httpd start >> $logfile
	/bin/sleep 10

	if [ ! -e $httpd_pidfile ]; then
		/bin/echo `date` "Failed to start apache" >> $logfile
		return 2
	fi
}

stop_apache ()
{
	/bin/echo `date` "Stopping apache" >> $logfile
	/sbin/service httpd stop >> $logfile
	/bin/sleep 10

	if [ -e $httpd_pidfile ]; then
		/bin/echo `date` "Stopping apache, second attempt" >> $logfile
		/sbin/service httpd stop >> $logfile
		/bin/sleep 15
	else
		return 0
	fi

	if [ -e $httpd_pidfile ]; then
		/bin/echo `date` "Stopping apache, third attempt" >> $logfile
		/sbin/service httpd stop >> $logfile
		/bin/sleep 15
	else
		return 0
	fi

	if [ -e $httpd_pidfile ]; then
		/bin/echo `date` "Couldn't stop apache, time for killall" >> $logfile
		/usr/bin/killall -9 httpd >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $httpd_pidfile ]; then
		/bin/echo `date` "Stopping apache following killall" >> $logfile
		/sbin/service httpd stop >> $logfile
		/bin/sleep 10
	else
		return 0
	fi

	if [ -e $httpd_pidfile ]; then
		/bin/echo `date` "Failed to stop apache" >> $logfile
		return 2
	fi
}

enter_standby ()
{
	if [ -d $www_standby ]; then
		rm -rf /www
		ln -s $www_standby /www
	else
		return 2
	fi
}

leave_standby ()
{
	if [ -d $www_normal ]; then
		rm -rf /www
		ln -s $www_normal /www
	else
		return 2
	fi
}

master_reset ()
{
	/usr/bin/curl http://localhost/main/cron/c-reset.php >> $logfile 2>&1
}

perform_purge ()
{
	if /usr/bin/lockfile -r 0 -l 1800 -s 5 $lock ; then
		/usr/bin/curl http://localhost/main/cron/c-purge.php >> $logfile 2>&1
		echo `date` "Purge completed" >> $logfile
		rm -rf $lock
	else
		echo `date` "Purge skipped" >> $logfile
	fi
}

do_success ()
{
	for i in "${emails[@]}"; do
		mail -s "Purge success" $i < $logfile
	done
	exit
}

do_failure ()
{
	for i in "${emails[@]}"; do
		mail -s "Purge failure" $i < $logfile
	done
	exit
}

########
# main #
########

rm -rf $logfile

/usr/bin/crontab -u webcron -r

# to avoid starting at the same time as a notification
/bin/sleep 30

if ! stop_apache
then
	do_failure
fi

/bin/sleep 15

if ! enter_standby
then
	do_failure
fi

/bin/sleep 5

if ! start_apache
then
	do_failure
fi

/bin/sleep 5

if [ $db_replication = yes ]; then
	if ! stop_replicated_mysql
	then
		do_failure
	fi
else
	if ! stop_mysql
	then
		do_failure
	fi
fi

/bin/sleep 20

if ! quick_db_check /var/lib/mysql
then
	do_failure
fi

if [ $db_replication = yes ]; then
	if ! quick_db_check /var/lib/mysql2
	then
		do_failure
	fi
fi

/bin/sleep 10

if [ $db_replication = yes ]; then
	if ! start_replicated_mysql
	then
		do_failure
	fi
else
	if ! start_mysql
	then
		do_failure
	fi
fi

/bin/sleep 20

if ! master_reset
then
	do_failure
fi

/bin/sleep 10

if ! perform_purge
then
	do_failure
fi

/bin/sleep 10

if [ $db_replication = yes ]; then
	if ! stop_replicated_mysql
	then
		do_failure
	fi
else
	if ! stop_mysql
	then
		do_failure
	fi
fi

/bin/sleep 10

if ! db_optimize /var/lib/mysql
then
	do_failure
fi

if [ $db_replication = yes ]; then
	if ! db_optimize /var/lib/mysql2
	then
		do_failure
	fi
fi

/bin/sleep 10

if ! stop_apache
then
	do_failure
fi

/bin/sleep 10

if ! leave_standby
then
	do_failure
fi

/bin/sleep 10

if [ $db_replication = yes ]; then
	if ! start_replicated_mysql
	then
		do_failure
	fi
else
	if ! start_mysql
	then
		do_failure
	fi
fi

/bin/sleep 20

if ! start_apache
then
	do_failure
fi

/bin/sleep $crondelay

if [ -e /home/webcron/crontab.txt ]; then
	/usr/bin/crontab -u webcron /home/webcron/crontab.txt
else
	do_failure
fi

do_success

exit
