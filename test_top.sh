#!/bin/sh
(/usr/bin/top -d1 -ocpu -Upgsql |/usr/bin/grep pgsql) | /usr/local/bin/php /root/psql_query/test_top.php
