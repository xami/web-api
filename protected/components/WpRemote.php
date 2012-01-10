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

    private $uname;
    private $upass;

    public $postID;
    public $cateID;

    function __construct($domain, $uname, $upass)
    {
        $this->uname=$uname;
        $this->upass=$upass;

        $this->wpURL='http://'.$domain.'/xmlrpc.php';
        $this->ixrPath=Yii::getPathOfAlias(
            'application.extensions'
        ).DIRECTORY_SEPARATOR.'class-IXR.php';
        include_once($this->ixrPath);
        $this->client = new IXR_Client($this->wpURL);
    }

    public function postContent($content)
    {
        if(!is_array($content)) throw new CException('Invalid Argument');
        pd($content);
        if(!$this->client->query('metaWeblog.newPost','',$this->uname,$this->upass,$content,true))
            throw new CException($this->client->getErrorMessage());

        $this->postID = $this->client->getResponse();
        return $this->postID;
    }

    public function getCate($categoryName='')
    {
        if(!is_string($categoryName) || empty($categoryName)) throw new CException('Invalid Argument');

        if(!$this->client->query('metaWeblog.getCategories',0 ,$this->uname,$this->upass))
            throw new CException($this->client->getErrorMessage());
        $cateArray = $this->client->getResponse();
        foreach($cateArray as $catOne){
            if($catOne['categoryName']==$categoryName) {
                $this->cateID = $catOne['categoryId'];
                return $this->cateID;
            }
        }

        $newCate=array(
            'name' => $categoryName,
            'slug' => $categoryName,
            'parent_id' => 0,
            'description' => $categoryName
        );
        if(!$this->client->query('wp.newCategory', 0,$this->uname, $this->upass, $newCate))
            throw new CException($this->client->getErrorMessage());
        $this->cateID = $this->client->getResponse();
        return $this->cateID;
    }
}
