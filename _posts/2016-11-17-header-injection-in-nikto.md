
nl=$'\n'
cr=$'\r'
nikto -nossl -host http://172.16.122.131:8912/ -useragent "hello world${cr}${nl}Some: haeder"
