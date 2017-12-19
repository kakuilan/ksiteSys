# ksiteSys
base on swoole and phalcon web sites system  
  

部署时,composer去掉2个IDE插件  
phalcon/ide-stubs
eaglewu/swoole-ide-helper  

License
-------
No license.本系统不授权.  


依赖:  
phalcon 3.2.4  
swoole 1.9.23  



TODO:  
swoole里SQL注入处理  
修复StringHelper::getText  
使用php7 xhprof  


检查nginx配置: /usr/local/nginx/sbin/nginx -t  
nginx配置  
``` bash
server {
    listen 80;
    charset utf-8;
    server_name my.com;
    index index.html index.htm index.php default.html default.htm default.php;
    root /home/wwwroot/default;
    server_tokens off;

    #client_header_buffer_size 32k;
    #large_client_header_buffers 4 32k;
    client_max_body_size    20m;
    client_body_buffer_size 128k;
    proxy_connect_timeout      5;
    proxy_send_timeout         60;
    proxy_read_timeout         5;
    proxy_buffer_size          64k;
    proxy_buffers             4 64k;
    proxy_busy_buffers_size    128k;
    proxy_temp_file_write_size 128k;

    proxy_http_version 1.1;
    proxy_pass_header Server;
    proxy_set_header Connection "keep-alive";
    proxy_set_header X-Real-IP  $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $host;

    location / {
        try_files $uri $uri/ /index.php?_url=$uri&$args;
        if (!-e $request_filename) {
            proxy_pass http://127.0.0.1:6666;
        }
    }
    
    location ~ [^/]\.php(/|$) {
        try_files $uri =404;
        fastcgi_pass  unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
    }
    
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|htm)$ {
        access_log off;
        expires 30d;
    }
    
    location ~ .*\.(js|css|html)?$ {
        access_log off;
        expires 12h;
    }
    
    location ~ /\. {
        deny all;
    }
    
    access_log  /home/wwwlogs/my.log;
    
}
```

useage:  
启动服务  
php bin/server.php status | start | stop | restart | reload | kill [-d]  
启动热更新  
php bin/reload.php  

单元测试:  
cd tests/
phpunit ./  
phpunit JustTest.php  

