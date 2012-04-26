<?php

class ApiController extends Controller
{
    public function actionTest()
    {
        $src=Yii::app()->doc360->getDocSrc(87308);

        $o = Tools::OZCurl($src, 600, false);
        //        echo $o['Result'];die;
        include_once(
            Yii::getPathOfAlias(
                'application.extensions.simple_html_dom'
            ).DIRECTORY_SEPARATOR.'simple_html_dom.php'
        );
        if(empty($o['Result']))die;
        $html = str_get_html($o['Result']);
        $c = $html->find('span[id=articlecontent]', 0);
        //        echo $c->innertext;die;
        echo Tools::formatHtml($c->innertext);

        //        if(!empty($src)){
        //            Yii::app()->doc360->getDocInfo(5000);
        //        }

    }

    //资源代理
    public function actionIndex(){
        $src=urldecode(Yii::app()->request->getParam('src', ''));
        if(empty($src) || !Tools::is_url($src)){
            throw new CException('Src must be real url', 1);
        }

        //文件缓存有则直接取得内容，否则从网络取得
        $key=md5($src);
        $data=Yii::app()->fcache->get($key);
        if(isset($data['Info']['http_code']) && $data['Info']['http_code']==200){
            if(isset($data['Info']['content_type']) && !empty($data['Info']['content_type']))
                header('Content-Type: '.$data['Info']['content_type']);
                echo $data['Result'];
        }else{
            $data = Tools::OZCurl($src, 60, true);
            Yii::app()->fcache->set($key, $data, 3600*24);
        }

    }


    public function actionIo(){
        $model=new IO;
        $IO=Yii::app()->request->getParam('IO', array());
        $model->in=isset($IO['in'])?$IO['in']:'';
        $model->type=isset($IO['type'])?$IO['type']:'';

        if(isset($model->in) && isset($model->type) && !empty($model->type))
        {
            try
            {
                $type=explode( ',', $model->type);
                if(count($type)>1){
                    $type_c=$type[0];
                    $type[0]=$model->in;
                    $model->out=call_user_func_array($type_c, $type);
                }else{
                    if($model->type=='eval'){
                        eval($model->in);die;
                    }else{
                        $model->out=call_user_func($model->type, $model->in);
                    }
                }
                if(is_array($model->out)){
                    $model->out=print_r($model->out, true);
                }
            }
            catch(Exception $e)
            {
                throw new CException('无法解析', 1);
            }
        }

        $this->render('io',array('model'=>$model));

    }

    public function actionPw()
    {
        if(true)
        {
            $content['title'] = 'title';
            $content['categories'] = array('category');
            $content['description'] = 'description';
            try
            {
                $posted = new WpRemotePost($content);
                echo $pid = $posted->postID;
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }

    }


    public function actionDo()
    {
        //        $sid=intval(Yii::app()->request->getParam('id', 0));
        $sid=YII::app()->doc360->getSid();
        $domain=trim(Yii::app()->request->getParam('dm', ''));
        $user=trim(Yii::app()->request->getParam('u', ''));
        $pass=trim(Yii::app()->request->getParam('p', ''));

        if(!YII::app()->doc360->Post2Wp($sid, $domain, $user, $pass)){
            throw new CHttpException(500, 'false');
        }else{
            echo 'true';
        }
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

    public function actionA(){
        $ad1=Yii::app()->params['ad1'];
        $ad2=Yii::app()->params['ad2'];
        $ad3=Yii::app()->params['ad3'];

        $href=MCrypy::decrypt(rawurldecode(Yii::app()->request->getParam('href', '')), Yii::app()->params['mcpass'], 128);
        if(empty($href) || !Tools::is_url($href)){
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>链接失效</title>
</head>
<body>
<div style="position:inherit;width:720px;margin-left: auto;margin-right: auto;text-align:left;">
'.$ad1.$ad2.$ad1.$ad2.$ad1.$ad2.'
</div>
</body>
</html>';die;
        }
        $html=Tools::getLink($href);
        if(empty($html)){
            $adc='<div style="top:0;margin-left: auto;margin-right: auto;text-align:center">'.$ad1.$ad2.'</div>';

            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>链接跳转</title>
</head>
<body>
<div style="float:left;">'.$ad3.'</div>
<div style="position:inherit;width:720px;margin-left: auto;margin-right: auto;text-align:left;">
'.$ad1.'&nbsp;&nbsp;目标链接，非本站链接<h1>'.CHtml::link('点此跳转', $href).'</h1>&nbsp;&nbsp;'.$ad2.$ad2.'
</div>
<div style="position:absolute;right:10px;top:10px;">'.$ad3.'
</div>
<div style="clear:both;margin-left:260px;">'.$ad2.'</div>
</body>
</html>';
        }else{
            echo $html;
        }
    }

    public function actionImg(){
        $src=MCrypy::decrypt(rawurldecode(Yii::app()->request->getParam('src', '')), Yii::app()->params['mcpass'], 128);
        $r=Tools::getImg($src);
        //        pd($r);
        if(!empty($r)
            && (isset($r['Info']['content_type']) && !empty($r['Info']['content_type']))
            && (isset($r['Info']['size_download']) && !empty($r['Info']['size_download']))
        ){
            if(headers_sent()) die('Headers Sent');
            if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
            header("Pragma: public"); // required
            header("Cache-Control: max-age=864000");//24小时
            header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
            header('Expires:'.gmdate('D, d M Y H:i:s', time() + '864000').'GMT');
            header("Cache-Control: private",false); // required for certain browsers
            header("Content-Type: ".$r['Info']['content_type']);
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$r['Info']['size_download']);
            echo $r['Result'];
            flush();
        }else{
            throw new CHttpException('404','内容失效');
        }
    }
}