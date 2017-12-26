phpstorm设置：  

代码模板  
Editor->File and Code Templates->Includes  
```  
/**
 * Copyright (c) ${YEAR} LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: ${DATE}
 * Time: ${TIME}
 * Desc: -
 */
```  

JS:  
```
/**
 * Created by kakuilan@163.com/lianq.net on ${DATE}.
 * Desc: 
 */
```

phpstorm action函数注释模板设置
Editoer->File and Code Templates->Includes->PHP Function Doc Comment
加上
```
/**
#if ($NAME.lastIndexOf('Action')>0 && ($NAME.lastIndexOf('Action')+6)==$NAME.length())
@title -
@desc -
#end
${PARAM_DOC}
#if (${TYPE_HINT} != "void") * @return ${TYPE_HINT}
#end
${THROWS_DOC}
*/
```





