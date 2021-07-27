# bu-adminsearch
Scans by default for the `"administrator"` capability, but will look for other capablility if entered in command.  
Use WP_CLI command: `wp dbsearch-user find "{a capability}"`.Or leave blank to search for `"administrator"`  
To push resuts to file: `wp dbsearch-user find "{a capability}">> {your_file_name.txt}`  