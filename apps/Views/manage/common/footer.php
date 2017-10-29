<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/5/7
 * Time: 14:42
 * Desc: -后台公共底部模板
 */
 

?>
<!-- basic scripts -->

<!--[if !IE]> -->
<script src="/statics/js/jquery-2.1.4.min.js"></script>
<!-- <![endif]-->

<!--[if IE]>
<script src="/statics/js/jquery-1.11.3.min.js"></script>
<![endif]-->

<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='/statics/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="/statics/js/bootstrap.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="/statics/js/excanvas.min.js"></script>
<![endif]-->
<script src="/statics/js/jquery-ui.custom.min.js"></script>
<script src="/statics/js/jquery.ui.touch-punch.min.js"></script>

<script src="/statics/js/lkkFunc.js"></script>

{{ assets.outputJs() }}

<!-- inline scripts related to this page -->
{% if FOOT_OTH_CONT is defined %}
{{FOOT_OTH_CONT}}
{% endif %}

</body>
</html>
