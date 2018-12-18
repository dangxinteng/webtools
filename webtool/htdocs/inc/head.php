<?php
$xml_url="config.xml";
$xml=simplexml_load_file($xml_url);
$home_star_number=$xml->home_star_number;
?>
<!DOCTYPE html>
<html class="no-js">
<head> 
<meta charset="UTF-8" /> 
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1" /> 
<meta name="renderer" content="webkit" /> 
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 
<meta name="keywords" content="<?php echo $xml->keywords; ?>" />
<meta name="description" content="<?php echo $xml->description; ?>" /> 
<title><?php echo $xml->title; ?></title> 
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel="stylesheet" href="style/normalize.min.css" /> 
<link rel="stylesheet" href="style/grid.css" /> 
<link rel="stylesheet" href="style/default.css" /> 
<link rel="stylesheet" type="text/css" href="style/highlights/atom-one-light.css" rel="stylesheet" />
<script src="style/jquery.min.js"></script>
<script src="style/markdown-it.min.js"></script>
<script>
  window.searchMap = location.search.substr(1).split('&').reduce(function(r, it) {
    var them = it.split('='); r[them[0]] = them[1]; return r;
  }, {});
  $.ajaxSetup({async: false});
</script>
<script src="style/highlight.min.js"></script>
<script >hljs.initHighlightingOnLoad();</script>
<script type="text/javascript" src="style/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<?php
if($mdoc=="")
{
?>
<script src="style/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    //每页显示的数目
    var show_per_page = <?php echo $xml->page_item_number; ?>; 
    //获取content对象里面，数据的数量
    var number_of_items = $('#content').children().size();
    //计算页面显示的数量
    var number_of_pages = Math.ceil(number_of_items/show_per_page);
    //隐藏域默认值
    $('#current_page').val(0);
    $('#show_per_page').val(show_per_page);
    var navigation_html = '<a class="previous_link link_nav" href="javascript:previous();">«上一页</a>&nbsp;&nbsp;';
    var current_link = 0;
    while(number_of_pages > current_link){
        navigation_html += '<a class="page_link" href="javascript:go_to_page(' + current_link +')" longdesc="' + current_link +'">'+ (current_link + 1) +'</a>';
        current_link++;
    }
    navigation_html += '<a class="next_link link_nav" href="javascript:next();">下一页»</a>';
    $('#page_navigation').html(navigation_html);
    //add active_page class to the first page link
    $('#page_navigation .page_link:first').addClass('active_page');
    //隐藏该对象下面的所有子元素
    $('#content').children().css('display', 'none');
    //显示第n（show_per_page）元素
    $('#content').children().slice(0, show_per_page).css('display', 'block');
});
//上一页
function previous(){
    new_page = parseInt($('#current_page').val()) - 1;
    //if there is an item before the current active link run the function
    if($('.active_page').prev('.page_link').length==true){
        go_to_page(new_page);
    }
}
//下一页
function next(){
    new_page = parseInt($('#current_page').val()) + 1;
    //if there is an item after the current active link run the function
    if($('.active_page').next('.page_link').length==true){
        go_to_page(new_page);
    }
}
//跳转某一页
function go_to_page(page_num){
    //get the number of items shown per page
    var show_per_page = parseInt($('#show_per_page').val());
    //get the element number where to start the slice from
    start_from = page_num * show_per_page;
    //get the element number where to end the slice
    end_on = start_from + show_per_page;
    //hide all children elements of content div, get specific items and show them
    $('#content').children().css('display', 'none').slice(start_from, end_on).css('display', 'block');
    /*get the page link that has longdesc attribute of the current page and add active_page class to it and remove that class from previously active page link*/
    $('.page_link[longdesc=' + page_num +']').addClass('active_page').siblings('.active_page').removeClass('active_page');
    //update the current page input field
    $('#current_page').val(page_num);
}
</script>
<?php
}
?>
<!--[if lt IE 9]>
<script src="style/html5.min.js"></script>
<script src="style/respond.min.js"></script>
<![endif]--> 
</head> 
<body> 
  <header id="header" class="clearfix"> 
   <div class="container"> 
    <div class="row"> 
     <div class="site-name col-mb-12 col-9"> 
      <h1><a id="logo" href="<?php echo $xml->url; ?>"><?php echo $xml->logo_text; ?></a></h1> 
      <p class="description"><?php echo $xml->logo_description; ?></p> 
     </div> 
     <div class="site-search col-3 kit-hidden-tb"> 
      <form id="search" method="get" action="?" role="search"> 
       <label for="s" class="sr-only">搜索关键字</label> 
       <input type="text" name="search" class="text" placeholder="搜索" /> 
      </form> 
     </div> 
     <div class="col-mb-12"> 
      <nav id="nav-menu" class="clearfix" role="navigation"> 
        <?php
          foreach($xml->navigation->item as $nav)
          {
            $cur="";
            if($page==$nav[0]['name'])
              $cur="class=\"current\" ";
            echo "<a ".$cur."href=\"?do=".$nav[0]['name']."\">".$nav."</a>";
          }
        ?>
      </nav> 
     </div> 
    </div>
   </div> 
  </header>