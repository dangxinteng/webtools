# light解释器简介
> 2018-12-13 16:48
> light 解释器 star

> &copy;Power By [ojisuan.com(o计算)](http://www.ojisuan.com"http://www.ojisuan.com") 作者: [Ooo]("qq:978831608")

------

light是一个c++实现的轻量级解释器，具有动态语言类型的特点，支持变量，基础运算表达式，嵌套作用域，if-else,while,for控制流程,函数，闭包，类。

## 数据类型

布尔值：true, false;  
字符串：“hello!”;  
数值：44, 45.33，  
空值：nil;  

## 变量声明与赋值

```
var a = "hello";
var b = 33.33;
print a;
a = "hi";
print a;
a = 12;
print a;
a = 12.3;
print a;
a = nil;
print a;
a = b;
print a;
```

## 算数运算符和逻辑运算符

```
a + b;
a - b;
a * b;
a / b;
-a;
a > b;
a < b;
a >= b;
a <= b;
a == b;
a != b;
!a;
a and b;
a or b;
```

## 嵌套作用域

```
{
  var a = "hello";
  {
     var b = a;
    {
      b = 13;
      print a;
      print b;
    }
      print a;
      print b;
  }
  print a;
  print b;//error b不在该作用域内
}
```
控制流程

```
if (true) {
  print "true";
} else {
  print "false";
}

var a = 0;
while (a < 6) {
  print a;
  a = a + 1;
}

for (var a = 0; a < 6; a = a + 1) {
  print a;
}
```

## 函数

```
fun say(){
  print "hello !";
}
say();

fun sayrt(){
  return "hello !";
}
print sayrt();

fun add(a,b){
  return a+b;
}
print add();

```

## 闭包

```
fun out(){
  var a = "out";
  fun inside(){
    print a;
  }
  return inside();
}

var f = out();
f();
```

## 类

```
class Hello{
  say(){
    print "say hello";
  }
  say(some){
    print some;
  }
  sayThis(){
    print this.newField;
  }
}

Hello().say();
var c = Hello();
c.say("Hi");

c.newFild = "fild";
print c.newFild;
c.sayThis();

```

代码下载地址:[light源代码](_v_attachments/light.rar)




> * 作者：Ooo  -- QQ:`978831608`
> * 邮箱：<978831608@qq.com>
> * 链接：[o计算](http://www.ojisuan.com"http://www.ojisuan.com")
> * 转载请注明出处。


