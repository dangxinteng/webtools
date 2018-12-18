<?php $page = !empty($_GET['do']) ? $_GET['do'] : '';?>
<?php $mdoc = !empty($_GET['md']) ? $_GET['md'] : '';?>
<?php $class = !empty($_GET['class']) ? $_GET['class'] : '';?>
<?php $tag = !empty($_GET['tag']) ? $_GET['tag'] : '';?>
<?php $search = !empty($_GET['search']) ? $_GET['search'] : '';?>
<?php $is_Linux=strpos(php_uname(),"Linux"); ?>
<?php $is_MacOS=strpos(php_uname(),"Darwin");?>
<?php
function fliter_str($value) {
if(preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_+]+$/u", $value))
  return $value;
else return '';
}
?>
<?php include 'inc/head.php';?>
  <div id="body"> 
   <div class="container"> 
    <div class="row"> 
     <div class="col-mb-12 <?php if($mdoc=="") echo "col-8"?>" id="main" role="main"> 
<?php
if($search!="")
{
        /*
        echo "<div class=\"main-block\">";
        echo "搜索功能暂不可用。";
        echo "</div>";
        */
        $search=fliter_str($search);
        if($is_Linux!==false or $is_MacOS!==false)
          $str=exec("./mdoc search ".$search);
        else $str=exec("mdoc search ".$search);
        echo "<div id=\"content\">";
        if(file_exists("search.xml"))
        {
          $post=simplexml_load_file("search.xml");
          $number=$post->num;
          $xtime=$post->time;
          echo "<div id=\"number\">共找到".$number."条记录, 耗时".$xtime."秒。</div>";
          echo "<ul class=\"archive-block widget-list\">"; 
          foreach($post->mdfile->item as $md)
          {
            echo "<li><a itemtype=\"url\" href=\"?md=".$md."\">".$md."</a></li>";
          }
          echo "</ul>";
        }
        else echo "暂无数据。";
        echo "</div>";        
}
else if(($page=="" or $page=="home" or $page=="star" or $class!="" or $tag!="") and $mdoc=="")
{
?>
      <input type="hidden" id="current_page" /> 
      <input type="hidden" id="show_per_page" /> 
      <div id="content">
        <?php
        if($page=="star")
          $xml_url="_post/_tag/star.xml";
        else if($page=="" && $class!="")
          $xml_url=$class;
        else if($page=="" && $tag!="")
          $xml_url=$tag;
        else if($page=="home" or $page=="")
          $xml_url="_post/mdfiles.xml";
        if(file_exists($xml_url))
        {
          $post=simplexml_load_file($xml_url);
          $number=$post->num;
          if($class!="" or $tag!="")
            echo "<div id=\"number\">共有".$number."条记录。</div>";
          foreach($post->mdfile->item as $md)
          {
            echo "<div class=\"post-block\">";
            echo "<h2 class=\"post-title\" itemprop=\"name headline\"><a itemtype=\"url\" href=\"?md=".$md."\">".$md[0]['title']."</a></h2>";
            echo "<p class=\"post-meta\">".$md[0]['date']."  ";
            $tags=$md[0]['tags'];
            $taga=explode(' ',$tags);
            for($i=0;$i<count($taga);$i++)
            {
              echo "<a class=\"tag\" href=\"?tag=_post/_tag/".$taga[$i].".xml\">".$taga[$i]."</a>";
              if($i<count($taga)-1)
                echo ", ";
            }
            echo "</p>";
            echo "</div>";
          }
        }
        else echo "暂无数据。";
        ?>  
      </div>
      <div id="page_navigation"></div> 
<?php
}else if($page=="class")
{
        $xml_url="_post/class.xml";
        if(file_exists($xml_url))
        {
          $class=simplexml_load_file($xml_url);  
          foreach($class->class as $item)
          {
            echo "<h2 class=\"post-title\" itemprop=\"name headline\"><a itemtype=\"url\" href=\"?class=_post/".$item->main."/mdfiles.xml\">".$item->main."</a></h2>";
            echo "<ul class=\"widget-list\">";
            foreach($item->sub->item as $sub)
            {
              echo "<li><a class=\"tag\" href=\"?class=_post/".$item->main."/".$sub."/mdfiles.xml\">".$sub."</a></li>";
            }
            echo "</ul>";
          }
        }
        else echo "暂无数据。";
}else if($page=="tag")
{
        $xml_url="_post/tag.xml";
        if(file_exists($xml_url))
        {
          $tag=simplexml_load_file($xml_url); 
          echo "<div class=\"main-block\">"; 
          echo "<p class=\"post-meta\">"; 
          foreach($tag->item as $item)
          {
            echo "<a class=\"tag\" href=\"?tag=_post/_tag/".$item.".xml\">".$item."(".$item[0]['number'].")</a> &nbsp;&nbsp; ";
          } 
          echo "</p>";
          echo "</div>";
        }
        else echo "暂无数据。";
}else if($page=="archive")
{
        $xml_url="_post/archive.xml";
        if(file_exists($xml_url))
        {        
          $archive=simplexml_load_file($xml_url);   
          echo "<div id=\"content\">";
          echo "<div id=\"number\">共有".$archive->num."条记录。</div>";
          echo "<ul class=\"archive-block widget-list\">"; 
          foreach($archive->mdfile->item as $item)
          {
            echo "<li>".substr($item[0]['date'],0,10)." <a href=\"?md=".$item."\">".$item[0]['title']."</a></li>";
          } 
          echo "</ul>";
          echo "</div>";
        }
        else echo "暂无数据。";  
}else if($page=="about")
{
        echo "<div class=\"main-block\">";
        $gourl=$xml->url.$xml->navigation->item[5]['redirect'];
        echo "<script type=\"text/javascript\">"; 
        echo "location.href='$gourl'"; 
        echo "</script>";         
        echo "</div>";
}else if($mdoc!="")
{
        echo "<div class=\"markdown-html\">";
        echo "<script>$.get(searchMap.md, function(text) { document.write(markdownit().render(text)); });</script>";
        echo "</div>";
}else if($page=="update")
{
        echo "<div class=\"main-block\">";
        echo "<p>Mdoc正在执行更新...</p>";
        if($is_Linux!==false or $is_MacOS!==false)
          $str=exec("./mdoc update");
        else $str=exec("mdoc update");
        echo "<p>".$str."</p>";
        echo "</div>";
?>

<?php
}
?>
     </div>
     <?php if($mdoc=="") include 'inc/side.php';?>
<?php include 'inc/foot.php';?>
