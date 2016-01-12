psql -U postgres template1 -c "SELECT  procpid, datname, current_query  from pg_stat_activity";
