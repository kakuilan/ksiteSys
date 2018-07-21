##
前台密码使用md5,然后发送到后端  
视图目录和文件名统一小写  


## 参考
https://github.com/slince/phpdish  

##  


$query = $this->modelsManager->createBuilder()  
$res = $query->getQuery()->execute();  

##编辑器  
https://github.com/pandao/editor.md  
https://summernote.org/  
https://gitee.com/benhail/thinker-md  
http://www.oschina.net/p/thinker-md-for-chanzhi  
http://www.wangeditor.com/  
https://www.tinymce.com/  
http://kindeditor.net/demo.php  
http://simditor.tower.im/  
https://github.com/mindmup/bootstrap-wysiwyg  
http://bootstrap-wysiwyg.github.io/bootstrap3-wysiwyg/  

##库  
https://github.com/jobbole/awesome-php-cn  
https://github.com/JingwenTian/awesome-php  
https://github.com/Intervention/image  
https://github.com/flyimg/flyimg(webp)  
https://github.com/emojione/emojione (emoji)  

## 图片
nginx image_fileter  

##IP地址转换
https://gitee.com/lionsoul/ip2region  
https://github.com/lionsoul2014/ip2region  




##xhpfrof 
- https://github.com/longxinH/xhprof.git
- 开启配置xhprof_enable=true  
- 查看结果数据
访问<http://$host_url/monitor/xhprof/xhprof_html/index.php>  

## 查看mysql/redis连接池数量  
- netstat -nap|grep 3306 |grep KSS|wc -l  
- netstat -nap|grep 6379 |grep KSS|wc -l  


配置表项
web_site_title	站点标题
web_site_slogan	站点标语
web_site_logo	站点LOGO
web_site_description	站点描述
web_site_keywords	站点关键词
web_site_copyright	版权信息
web_site_icp	备案信息
web_site_statistics_js	站点统计脚本
upload_site_url     上传站点URL,带http,小写,结尾/,为空时取当前站点url
upload_file_size	文件上传大小限制,0为不限制大小,单位字节
upload_file_ext	允许上传的文件后缀,rar,zip,gz,bz2,7z,txt,doc,docx,xls,xlsx,ppt,pptx,pdf,wps,gif,jpg,jpeg,bmp,png
upload_image_size	图片上传大小限制,0为不限制大小,单位字节
upload_image_ext	允许上传的图片后缀,gif,jpg,jpeg,bmp,png
list_rows_num	分页数量,每页的记录数
smtp_server	邮件服务器
smtp_port	邮件服务端口
smtp_username 邮件服务用户名
smtp_password	邮件服务密码
smtp_from	邮件服务发送者地址
smtp_fromname	邮件服务发送者名称
user_qps_limit 用户每秒访问次数限制,0为不限制
user_pv_early_warning 用户每天访问次数预警,0为不限制,当大于N时,把该用户加入监控范围
user_pv_day_limit 用户每天访问次数限制,0为不限制,当大于N时,返回错误信息
comment_enable 是否开启评论 
comment_hour_limit 每小时最多N次评论 







