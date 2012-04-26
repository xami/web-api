<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xami
 * Date: 12-4-23
 * Time: 下午3:49
 * To change this template use File | Settings | File Templates.
 */

class Xuk extends CApplicationComponent{

    public function NewGallery($name){
        $gallery_id = false;
        $wp = new WpRemote();
        $gallery_id = $wp->NewGallery($name);
        return $gallery_id;
    }

    public function addImages($galleryID, $imageslist = array(), $description){
        $wp = new WpRemote();
        $pictures_ids = $wp->addImages($galleryID, $imageslist, $description);
        return $pictures_ids;
    }

    /*
    * $content_struct['post_type']                 //文章类型（attachment/page/post/revision）       post
    * $content_struct['page_status']               //状态                                           publish
    * $content_struct['wp_page_template']          //模板                                            default
    * $content_struct['wp_post_format']            //帖子类型                                          gallery
    * $content_struct['wp_slug']                   //短链接
    * $content_struct['wp_password']               //                                               ''
    * $content_struct['wp_page_parent_id']         //                                              0
    * $content_struct['wp_page_order']             //                                               0
    * $content_struct['wp_author_id']              //                                              1
    * $content_struct['title']                     //                                              xxxx
    * $content_struct['description']               //                                              xxxx
    * $content_struct["{$post_type}_status"]       //$content_struct['page_status']                 用于发布page不管
    * $content_struct['mt_excerpt']                //摘要                                          feed内容是取此部分
    * $content_struct['mt_text_more']              //更多
    * $content_struct['mt_keywords']               //关键词                                       分类,名称,'lolita.im'
    * $content_struct['mt_allow_comments']         //允许评论                                      true
    * $content_struct['mt_allow_pings']            //允许ping                                        true
    * $content_struct['mt_tb_ping_urls']           //参考文档
    * $content_struct['date_created_gmt']          //gmt                                           gmmktime()
    * $content_struct['dateCreated']               //                                               ''
    * $content_struct['categories']                //内容分类                                      teen, name,       创建内容分类wp_create_categories($categories, $post_id = '');
    * $content_struct['sticky']                    //精华贴(置顶)                                  false
    * $content_struct['custom_fields']             //自定义字段,存储在wp_postmeta表                 常用字段 title, description, description
    * $content_struct['enclosure']                 //附件
    *
    */
    public function newPost($content_struct){
        extract(array('title'=>'', 'descriptione'=>'', 'wp_sluge'=>'', 'mt_excerpte'=>'', 'mt_keywordse'=>array(), 'mt_text_moree'=>'',  'categoriese'=>array(), 'post_marke'=>''));
        extract($content_struct, EXTR_OVERWRITE);

        $wp = new WpRemote();
        $post_id = $wp->findMeta('lolita', $post_mark);
        if($post_id)
            return $post_id;

        //取得分类id
        $cids=array();
        foreach($categories as $cat_name){
            $cids[] = $wp->getCategory($cat_name);
        }

        //取得tags id
        $tids=array();
        foreach($mt_keywords as $tag_name){
            $tids[] = $wp->getTag($tag_name);
        }


        $content_struct=array();
        $content_struct['post_type']               = 'post';             //文章类型（attachment/page/post/revision）       post
        $content_struct['page_status']             = 'publish';         //状态                                           publish
        $content_struct['wp_page_template']       = 'default';         //模板                                            default
        $content_struct['wp_post_format']          = 'gallery';           //帖子类型    gallery    aside chat    gallery    link    image    quote    status    video    audio   $content_struct['wp_slug']                  = $wp_slug;             //短链接
//        $content_struct['wp_password']              = '';                   //                                               ''
        $content_struct['wp_page_parent_id']       = 0;                    // 父级                                             0
        $content_struct['wp_page_order']            = 0;                    // 排序                                              0
        $content_struct['wp_author_id']             = 1;                    // 发布用户ID                                             1
        $content_struct['title']                      = $title;              //                                              xxxx
        $content_struct['description']               = $description;        //                                              xxxx
//        $content_struct["{$post_type}_status"]       = '';                  //$content_struct['page_status']                 用于发布page不管
        $content_struct['mt_excerpt']                = $mt_excerpt;          //摘要                                          feed内容是取此部分
        $content_struct['mt_text_more']              = $mt_text_more;       //更多
        $content_struct['mt_keywords']               = $mt_keywords;               //关键词                                       分类,名称,'lolita.im'
        $content_struct['mt_allow_comments']        = true;                //允许评论                                      true
        $content_struct['mt_allow_pings']            = true;                //允许ping                                        true
        //Ping 服务地址
        $content_struct['mt_tb_ping_urls']           = array(
            'http://ping.baidu.com/ping/RPC2',
            'http://blogsearch.google.com/ping/RPC2',
            'http://www.feedsky.com/api/RPC2',
            'http://rpc.pingomatic.com/',
            'http://blog.youdao.com/ping/RPC2'
        );
        $content_struct['date_created_gmt']          = new IXR_Date(time());          //gmt                                           gmmktime()
//        $content_struct['dateCreated']                = '';                 //                                               ''
        $content_struct['categories']                 = $categories;        //内容分类                                      teen, name,       创建内容分类wp_create_categories($categories, $post_id = '');
        $content_struct['sticky']                      = false;             //精华贴(置顶)                                  false
        //自定义字段,存储在wp_postmeta表                 常用字段 title, description, description
        $content_struct['custom_fields']              = array(
//            'title'=>$title,
//            'description'=>$description,
//            'keywords'=>implode(',', $mt_keywords),
//            'lolita'=>$post_mark
            array('key'=>'title','value'=>$title),
            array('key'=>'description','value'=>$description),
            array('key'=>'keywords','value'=>implode(',', $mt_keywords)),
            array('key'=>'lolita','value'=>$post_mark)
        );
//        $content_struct['enclosure']                   = '';                //附件


        $post_id=$wp->newPost($content_struct);
        return $post_id;
    }
}

