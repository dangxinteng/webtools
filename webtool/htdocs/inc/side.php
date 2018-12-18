     <div class="col-mb-12 col-offset-1 col-3 kit-hidden-tb" id="secondary" role="complementary"> 
      <section class="widget"> 
       <h3 class="widget-title">星标文档</h3> 
       <ul class="widget-list"> 
        <?php
          $xml_url="_post/_tag/star.xml";
          if(file_exists($xml_url))
          {
            $post=simplexml_load_file($xml_url);
            $index=0;
            foreach($post->mdfile->item as $md)
            {
              $index++;
              if($index>$home_star_number)
                break;
              echo "<li><a href=\"?md=".$md."\">".$md[0]['title']."</a></li>";
            }
          }
          else echo "暂无数据。";
        ?>
       </ul> 
      </section> 
      <section class="widget"> 
       <h3 class="widget-title">文档分类</h3> 
       <ul class="widget-list"> 
        <?php
          $xml_url="_post/class.xml";
          if(file_exists($xml_url))
          {
            $class=simplexml_load_file($xml_url);
            foreach($class->class as $item)
            {
              echo "<li><a href=\"?class=_post/".$item->main."/mdfiles.xml\">".$item->main."</a></li>";
            }
          }
          else echo "暂无数据。";
        ?>
       </ul> 
      </section> 
     </div>
     <!-- end #sidebar --> 