<?php

/*
(top -d1 -ocpu -Upgsql |grep postgres )
mar@spb-db:~$  top -d1 -Upgsql |grep pgsql
72695 pgsql     42   0   421M   168M CPU1   1  21:06 21.92% 21.92% postgres
*/

$log_file = "/var/log/query.log";
$top_input = fopen("php://stdin", "r");
$handle = fopen($log_file, "a+");

$counter = 0;
$look = 0;
$wcpu_limit = 65;
$stdout = '';

while (!feof ($top_input)) {
    
    $buffer = fgets($top_input, 4096);

    $pos_proc_id1 = strpos( $buffer, ' pgsql')   ;
    $proc_id[$counter] = trim(rtrim(substr($buffer, 0, $pos_proc_id1 )));
    $pos_wcpu[$counter] = strrpos( $buffer, ':') + 5 ;
    $wcpu[$counter] =  substr($buffer, $pos_wcpu[$counter], 7);
    $pos_wcpu1[$counter] = strrpos( $wcpu[$counter], '%') ;
    $wcpu[$counter] =  substr($wcpu[$counter], 0, $pos_wcpu1[$counter] );

    if ($wcpu[$counter] > $wcpu_limit && $proc_id[$counter] > 0) {
	    $stdout = shell_exec('/usr/local/bin/psql -U pgsql template1 -c "SELECT now() AS date, '. $wcpu[$counter]. ' AS wcpu, procpid, datname, current_query  from pg_stat_activity WHERE current_query <> \'<IDLE>\' AND  procpid='. $proc_id[$counter]. '"');
	    /* тестовый вариант запроса, чтобы убедиться в выводе данных */
	    //$stdout = shell_exec('/usr/local/bin/psql -U pgsql template1 -c "SELECT now() AS date, '. $wcpu[$counter]. ' AS wcpu, procpid, datname, current_query  from pg_stat_activity WHERE procpid='. $proc_id[$counter]. '"') ;
	    
	    $stdout_strings = array(); 
	    $stdout_strings = explode("\n", $stdout);

	    if (!eregi('(0 rows)', $stdout_strings[2]) && sizeof($stdout_strings) > 2 ) {
		fputs($handle, $stdout_strings[2]);
		fputs($handle, "\n");	
	    }

	    $look = 1;

    }	
    
    $counter++;
}
/*
if ($look > 0) {
    echo "\n================\n";
} 
*/   
fclose ($handle);
fclose ($top_input);


?>
	