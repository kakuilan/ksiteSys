# ksiteSys
base on swoole and phalcon web sites system  
  

部署时,composer去掉2个IDE插件  
phalcon/incubator  
eaglewu/swoole-ide-helper  

License
-------
No license.本系统不授权.  

swoole orm:  
https://github.com/heikezy/Sworm  


TODO:  
swoole里SQL注入处理  
修复StringHelper::getText  
session的redis使用连接池  
取消LkkModel和LkkServer的dbMaster/dbSlave  
重写Application支持异步  


nginx配置  
``` bash
server {
    listen 80;
    root /home/wwwroot/default;
    server_name my.com;
    charset utf-8;
    index index.html index.htm default.html default.htm index.php;
    
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
    
    location / {
        try_files $uri $uri/ /index.php?_url=$uri&$args;
    }
    
    location ~ ^/(index)\.php(/|$) {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP  $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $host;
        proxy_pass http://127.0.0.1:6666;
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


