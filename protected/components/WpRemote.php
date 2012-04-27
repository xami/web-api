<?php
/**
 * Created by JetBrains PhpStorm.
 * User: UU
 * Date: 12-1-8
 * Time: 下午11:42
 * To change this template use File | Settings | File Templates.
 */

class WpRemote
{
    private $client;
    private $wpURL;
    private $ixrPath;

    private $domain;
    private $uname;
    private $upass;

    public $postID;
    public $cateID;

    var $code;
    var $message;

    function __construct()
    {
        $this->uname  = YII::app()->params['wp_user'];
        $this->upass  = YII::app()->params['wp_pass'];
        $this->domain = YII::app()->params['wp_domain'];

        $this->wpURL='http://'.$this->domain.'/xmlrpc.php';
        $this->ixrPath=Yii::getPathOfAlias(
            'application.extensions'
        ).DIRECTORY_SEPARATOR.'class-IXR.php';
        include_once($this->ixrPath);
        $this->client = new IXR_Client($this->wpURL);
    }

    function IXR_Error($code, $message)
    {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }

    public function postContent($content)
    {
        if(!is_array($content)) throw new CException('Invalid Argument');

        if(!$this->client->query('metaWeblog.newPost','',$this->uname,$this->upass,$content,true)){
            //            throw new CException($this->client->getErrorMessage());
            return false;
        }


        $this->postID = $this->client->getResponse();
        return $this->postID;
    }

    public function getCate($categoryName='')
    {
        if(!is_string($categoryName) || empty($categoryName)) throw new CException('Invalid Argument');

        if(!$this->client->query('metaWeblog.getCategories',0 ,$this->uname,$this->upass))
            throw new CException($this->client->getErrorMessage());
        $cateArray = $this->client->getResponse();
        if(!empty($cateArray)){
            foreach($cateArray as $catOne){
                if($catOne['categoryName']==$categoryName) {
                    $this->cateID = $catOne['categoryId'];
                    return $this->cateID;
                }
            }
        }

        $newCate=array(
            'name' => $categoryName,
            'slug' => $categoryName,
            'parent_id' => 0,
            'description' => $categoryName
        );

        foreach($newCate as $oneCate){
            $listCate[]=is_array($oneCate) ? $oneCate : new IXR_Base64($oneCate);
        }
        if(!$this->client->query('wp.newCategory', 0,$this->uname, $this->upass, $listCate))
            throw new CException($this->client->getErrorMessage());
        $this->cateID = $this->client->getResponse();
        return $this->cateID;
    }

    public function getImages($gid){
        $gid=intval($gid);
        if(!$this->client->query('ngg.getImages',0 ,$this->uname,$this->upass,$gid))
            throw new CException($this->client->getErrorMessage());
        $images = $this->client->getResponse();
        return $images;
    }

    public function getImage($pid){
        $gid=intval($pid);
        if(!$this->client->query('ngg.getImage',0 ,$this->uname,$this->upass,$pid))
            throw new CException($this->client->getErrorMessage());
        $image = $this->client->getResponse();
        return $image;
    }

    public function NewGallery($name){
        if(!is_array($name))
            $name=new IXR_Base64($name);
        if(!$this->client->query('ngg.newGallery',0 ,$this->uname,$this->upass,$name))
            throw new CException($this->client->getErrorMessage());
        $id = $this->client->getResponse();
        return $id;
    }

    public function addImages($galleryID, $imageslist, $description){
        if(count($imageslist)==1 && !is_array($imageslist)){
            $imageslist=array($imageslist);
        }
        foreach($imageslist as $image){
            $images[]=is_array($image) ? $image : new IXR_Base64($image);
        }
        if(!$this->client->query('ngg.addImages',0 ,$this->uname, $this->upass, $galleryID, $images, $description))
            throw new CException($this->client->getErrorMessage());
        $ids = $this->client->getResponse();
        return $ids;
    }

    /*
     * $content_struct['post_type']                 //文章类型（attachment/page/post/revision）       post
     * $content_struct['page_status']               //状态                                           publish
     * $content_struct['wp_page_template']          //模板                                            default
     * $content_struct['wp_post_format']            //帖子类型                                          gallery
     * $content_struct['wp_slug']                   //短链接
     * $content_struct['wp_password']               //
     * $content_struct['wp_page_parent_id']         //                                              0
     * $content_struct['wp_page_order']             //
     * $content_struct['wp_author_id']              //                                              1
     * $content_struct['title']                     //                                              xxxx
     * $content_struct['description']               //                                              xxxx
     * $content_struct["{$post_type}_status"]       //$content_struct['page_status']
     * $content_struct['mt_excerpt']                //摘要                                          feed内容是取此部分
     * $content_struct['mt_text_more']              //更多
     * $content_struct['mt_keywords']               //关键词                                       分类,名称,'lolita.im'
     * $content_struct['mt_allow_comments']         //允许评论                                      true
     * $content_struct['mt_allow_pings']            //允许ping                                        true
     * $content_struct['mt_tb_ping_urls']           //参考文档
     * $content_struct['date_created_gmt']          //gmt                                           gmmktime()
     * $content_struct['dateCreated']               //
     * $content_struct['categories']                //内容分类                                      teen, name,       创建内容分类wp_create_categories($categories, $post_id = '');
     * $content_struct['sticky']                    //精华贴(置顶)                                  false
     * $content_struct['custom_fields']             //自定义字段,存储在wp_postmeta表                 常用字段 title, description, description
     * $content_struct['enclosure']                 //附件
     *
     */
    /*
    public function newPost($content_struct){
//        if(count($content_struct)==1 && !is_array($content_struct))
//            $content_struct=array($content_struct);
//        foreach($content_struct as $one){
//            $list[]=is_array($one) ? $one : new IXR_Base64($one);
//        }
        if(!$this->client->query('metaWeblog.newPost',0 ,$this->uname, $this->upass, $content_struct, $publish=true))
            throw new CException($this->client->getErrorMessage());
        $post_id = $this->client->getResponse();
        return $post_id;
    }
    */

    public function newPost($post_mark, $categories, $mt_keywords, $content_struct){
        if(!$this->client->query('ngg.newPost',0 ,$this->uname, $this->upass, $post_mark, $categories, $mt_keywords, $content_struct, $publish=true))
            throw new CException($this->client->getErrorMessage());
        $post_id = $this->client->getResponse();
        return $post_id;
    }

    public function findMeta($key, $val){
        if(!$this->client->query('ngg.findMeta',0 ,$this->uname, $this->upass, $key, $val))
            throw new CException($this->client->getErrorMessage());
        $post_id = $this->client->getResponse();
        return $post_id;
    }

    /*
     * $category['slug']
     * $category['parent_id']
     * $category["description"]
     * $category['name']
     *  原生的,不满足要求
     */
    public function newCategory($category){
        if(count($category)==1 && !is_array($category))
            $category=array($category);
        foreach($category as $one){
            $list[]=is_array($one) ? $one : new IXR_Base64($one);
        }
        if(!$this->client->query('wp.newCategory',0 ,$this->uname, $this->upass, $list))
            throw new CException($this->client->getErrorMessage());
        $cid = $this->client->getResponse();
        return $cid;
    }

    // xami 扩展
    public function getCategory($cat_name, $parent=0){
        if(!$this->client->query('ngg.getCategory',0 ,$this->uname, $this->upass, $cat_name, $parent))
            throw new CException($this->client->getErrorMessage());
        $cid = $this->client->getResponse();
        return $cid;
    }

    // xami 扩展
    public function getTag($tag_name){
        if(!$this->client->query('ngg.getTag',0 ,$this->uname, $this->upass, $tag_name))
            throw new CException($this->client->getErrorMessage());
        $tid = $this->client->getResponse();
        return $tid;
    }

}
