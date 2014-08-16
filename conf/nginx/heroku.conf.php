http {
    server_tokens off;
    include       mime.types;
    default_type  application/octet-stream;

    #log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
    #                  '$status $body_bytes_sent "$http_referer" '
    #                  '"$http_user_agent" "$http_x_forwarded_for"';

    #access_log  logs/access.log  main;

    sendfile        on;
    #tcp_nopush     on;

    #keepalive_timeout  0;
    keepalive_timeout  65;

    #gzip  on;

    fastcgi_buffers 256 4k;

    upstream heroku-fcgi {
        #server 127.0.0.1:4999 max_fails=3 fail_timeout=3s;
        server unix:/tmp/heroku.fcgi.<?=getenv('PORT')?:'8080'?>.sock max_fails=3 fail_timeout=3s;
        keepalive 16;
    }
    
    server {
        listen <?=getenv('PORT')?:'8080'?>;
        server_name localhost;
        root <?=getenv('DOCUMENT_ROOT')?:getenv('HEROKU_APP_DIR')?:getcwd()?>;
        
        error_log stderr;
        access_log /tmp/heroku.nginx_access.<?=getenv('PORT')?:'8080'?>.log;
                
        # default handling of .php
        location / {
            index  index.php index.html index.htm;
        }
        location ~ \.php {
            try_files $uri =404;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_pass heroku-fcgi;
        }
    }
    server {
      listen      <?=getenv('PORT')?:'8080'?>
      server_name  wut.bakatube.net;
   
      access_log  /var/log/nginx/access.log;
      error_log  /var/log/nginx/error.log;
      root   /usr/share/nginx/html;
      index  index.html index.htm;
   
      location / {
       valid_referers none blocked bakatube.net www.bakatube.net ~\.google\. ~\.yahoo\. ~\.bing\. ~\.facebook\. ~\.fbcdn\.;
       if ($invalid_referer) {
         return 403;
       }
       proxy_pass  http://www7.mp4upload.com:182;
       proxy_next_upstream error timeout invalid_header http_500 http_502 http_503 http_504;
       proxy_redirect off;
       proxy_buffering off;
       proxy_set_header        Host            "www7.mp4upload.com";
       proxy_set_header        X-Real-IP       $remote_addr;
     }
  }
}
