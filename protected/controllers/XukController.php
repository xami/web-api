<?php

class XukController extends Controller
{
    public function actionIndex()
    {
        ini_set('max_execution_time', 3600);

        //get the page number
        $page=intval(Yii::app()->request->getParam('p', 1));
        if ($page<1) {
            throw new CException('页号错误', 1);
        }

        //取得此页链接
        $src='http://xuk.lolita.im/'.$page.'.html';
        $data = Tools::OZCurl($src, 600, false);
        $html=$data['Result'];
        if (strlen($html)<500) {
            //throw new CException('列表页面内容取得错误', 2);
            IXR_Server::output(WpRemote::IXR_Error(404, '列表页面内容取得错误'));
        }
        preg_match_all('/\<a\s+href=[\"\']http:\/\/xuk\.ru\/([\-\w\d^\/]+?)\/([\-\w\d^\/]+?)\/vid-1\.html[\"\']\s*\/?\>/', $html, $out);


        //取得相册列表地址
        for($i=0;$i<count($out[0]);$i++){
            $list_url='http://xuk.lolita.im/'.$out[1][$i].'/'.$out[2][$i].'/vid-1.html';
            $data = Tools::OZCurl($list_url, 3600, false);
            $html=$data['Result'];
            if (strlen($html)<500) {
                //throw new CException('图片页面内容取得错误', 3);
                IXR_Server::output(WpRemote::IXR_Error(404, '图片页面内容取得错误'));
            }
            $all[$i]['gallery']=$list_url;
            $all[$i]['cat']=$out[1][$i];
            $all[$i]['name']=$out[2][$i];
            $all[$i]['path']=$out[1][$i].'/'.$out[2][$i];

            preg_match_all('/\<a\s+class=([\"\'])xuk_gallery(?1)\s+href=([\"\'])(http:\/\/img\d?\.xuk\.ru\/(.*?\.jpe?g))(?2)\s*\/?\>/i', $html, $images_src);
            foreach($images_src[4] as $file){
                $all[$i]['images'][]='http://img.lolita.im/'.$file;
            }

            $all[$i]['key']='';
            if(isset($file)){
                preg_match('/\/([\w\d_]*)\/\d+\(www\.xuk\.ru\)\d{0,3}\.jpg$/i', $file, $cut_key);
                $all[$i]['key']=isset($cut_key[1]) ? $cut_key[1] : '';
            }


//                        break;
        }
        $all=array($all[0]);
//        pd($all);

        if(empty($all)){
            //throw new CException('没有取得需要数据', 4);
            IXR_Server::output(WpRemote::IXR_Error(500, '没有取得需要的图片源数据'));
        }

        //轮循：取得单页图片链接，发表帖子
        $pids=array();
        $all_pids=array();
        $post_ids=array();
        foreach($all as $item){
            //发表新帖
            $search = array (
                "'_'",                  // 去掉下划线
                "'\d'",                 // 去掉数字
                "'([\r\n])[\s]+'",     // 去掉空白字符
                "'\_'"
            );
            $replace = array (
                "",
                "",
                "",
                " ",
            );
            $name_slug=trim(preg_replace($search, $replace, $item['name'])," \t\n\r\0\x0B\-_");

            // 创建相册
            $gid=Yii::app()->xuk->NewGallery($item['path']);
            if(empty($gid)){
                //throw new CException('新建相册失败', 5);
                IXR_Server::output(WpRemote::IXR_Error(500, '新建相册失败'));
            }

            // 发布图片,添加图片比较耗时的操作
            if(empty($item['images'])){
                IXR_Server::output(WpRemote::IXR_Error(500, '源相册列表为空'));
            }
            $img_des='lolita.im,'.$name_slug;
            $pids=Yii::app()->xuk->addImages($gid, $item['images'], $img_des);
            if(empty($pids)){
                //throw new CException('新建相册失败', 5);
                IXR_Server::output(WpRemote::IXR_Error(500, '发布图片失败'));
            }

            $images_list=Yii::app()->xuk->getImages($gid);

            // 取得缩略图列表
            $imageHTML='';
            $thumbHTML='';
            $thumbnail='';
            if(!empty($images_list)) foreach($images_list as $image){
                $imageHTML.= $image['imageHTML'];
                $thumbHTML.= $image['thumbHTML'];
                $thumbnail = $image['thumbHTML'];
            }
            $imageHTML=preg_replace('/[\r\n]+/', '', $imageHTML);
            $thumbHTML=preg_replace('/[\r\n]+/', '', $thumbHTML);

            //取得首张缩略图
//            $images_obj=Yii::app()->xuk->getImage($pids[0]);
//            $thumbnail=preg_replace('/[\r\n]+/', '', $images_obj['href']);


            //比较曲折,发布帖子
            $key=array('title', 'description', 'wp_slug', 'mt_excerpt', 'mt_keywords', 'mt_text_more',  'categories', 'post_mark', 'thumbnail', 'gallery');
            $val=array(
                $item['name'],
                $thumbHTML,
                $name_slug,
                'I\'m LoLiTa (http://'.$name_slug.'.lolita.im)',
                array($item['cat'], $name_slug, $item['key'], $name_slug.'.lolita.im'),
                $imageHTML,
                array($item['cat']),
                $item['gallery'],
                $thumbnail,
                $gid
            );
            $content_struct=array_combine($key, $val);
            $post_ids[]=Yii::app()->xuk->newPost($content_struct);
            $all_pids=array_merge($all_pids, $pids);
            //            break;
        }
        pd($post_ids);

        if(!is_array($all_pids) || !is_array($post_ids) ){
            //throw new CException('新建相册失败', 5);
            IXR_Server::output(WpRemote::IXR_Error(500, '发布图片 或 帖子失败'));
        }

        // 发布帖子,更新首页缩略图
        $post_info='';
        foreach($post_ids as $post_id){
            if($post_id ==Yii::app()->xuk->publishPost($post_id)){
                $post_info.=$post_id.'('.'成功'.')'."\t";
            }else{
                $post_info.=$post_id.'('.'失败'.')'."\t";
            }
        }

        IXR_Server::output(WpRemote::IXR_Error(200,
            '成功更新'.count($all_pids).'张图片: '.implode(',',$all_pids ).';'."\r\n".
            '执行发布'.count($post_ids).'组图片: '.$post_info));

    }

    public function actionPublish(){
        $page=intval(Yii::app()->request->getParam('p', 1));
    }

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/


}