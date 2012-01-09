<?php

class ApiController extends Controller
{
	public function actionIndex()
	{
//		$this->render('index');
        $src=Yii::app()->doc360->getDocSrc(5000);

        $o = Tools::OZCurl($src, 600, false);
        echo $o['Result'];die;
        include_once(
            Yii::getPathOfAlias(
                'application.extensions.simple_html_dom'
            ).DIRECTORY_SEPARATOR.'simple_html_dom.php'
        );
        $html = str_get_html($o['Result']);
        $c = $html->find('span[id=articlecontent]', 0);
        echo ($c->innertext);

        if(!empty($src)){
            Yii::app()->doc360->getDocInfo(5000);
        }

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
}