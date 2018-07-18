# ksiteSys
base on swoole and phalcon web sites system  
  

部署时,composer去掉2个IDE插件  
phalcon/ide-stubs
eaglewu/swoole-ide-helper  

License
-------
### No license.本系统不授权.  


### 依赖: 
- php 7.1  
- fileinfo 1.0.5  
- phalcon 3.2.4/3.3.2   
- swoole 1.10.5  
- redis 4.0.2  
- gd 2.1.0  
- imagick 3.4.3  
- inotify 2.0.0  

### 需打开函数:  
- popen  
- pclose  
- exec  
- proc_open
- proc_get_status



### TODO:  
- swoole里SQL注入处理  
- socket  
- 404页面慢  
- 上次cookie response检查   
- 敏感词过滤功能  
- 后台配置功能  
- redis pool库错乱问题  
- IP黑名单数据表和功能,包含时间限制范围 
- 用户头像因nginx缓存不能及时更新问题
- 二进制上传 
- 长时间附近页面不动,再上传无权限,token没刷新问题




### BUG:  
- none



### 注意:
- 注意不能打印model记录信息,会出现内存超标,造成540错误 
- 注意使用控制器的initialize()返回拦截 



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
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Real-PORT $remote_port;
    proxy_set_header X-Real-IP  $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

    location / {
        try_files $uri $uri/ /index.php?_url=$uri&$args;
        if (!-e $request_filename) {
            proxy_pass http://127.0.0.1:6666;
        }
    }
    
    location ~ [^/]\.php(/|$) {
        try_files $uri =404;
        #注意,以实际的php-cgi.sock路径为准,否则502,具体看/usr/local/php/etc/php-fpm.conf
        fastcgi_pass  unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        fastcgi_param PHP_ADMIN_VALUE "open_basedir=/home/wwwroot/:/tmp/:/proc/";
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

CLI:  
php bin/cli.php main main  

单元测试:  
cd tests/
phpunit ./  
phpunit JustTest.php  
phpunit JustTest.php  --repeat 100 

