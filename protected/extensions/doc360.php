<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lj
 * Date: 12-1-4
 * Time: 下午2:12
 * To change this template use File | Settings | File Templates.
 */

class Doc360 extends CApplicationComponent{
    protected $_src;

    public function  getDocSrc($sid){
        $sid=intval($sid);

        if(empty($sid)) return false;
        $src='http://www.360doc.com/showWeb/0/0/'.$sid.'.aspx';
        $o = Tools::OZCurl($src, 600, false);

        $this->_src='';
        if(isset($o['Header']['7']) && substr($o['Header']['7'], 0, 9)=='Location:'){
            $this->_src=trim(substr($o['Header']['7'], 9));
        }
        if(empty($this->_src)) return false;
        return $this->_src;
    }

    public function getDocInfo(){
        if(empty($this->_src)) return false;
    }

    public function getSid(){
        $i=Crontab::model()->findBySql('select `sid` from `crontab` ORDER BY `sid` DESC limit 1');
        if(empty($i)){
            return 1000;
        }else{
            return $i->sid+1;
        }
    }

    public function Post2Wp($sid, $domain, $user, $pass){
        $sid=intval($sid);
        if($sid<1) return false;
        $src=$this->getDocSrc($sid);

        $crontab=Crontab::model()->find('sid=:sid',array(':sid'=>$sid));
        if(empty($crontab)){
            $crontab=new Crontab();
            $crontab->status=0;
            $crontab->msg='';
            $crontab->error='';
            $crontab->sid=$sid;
            $crontab->pid=0;
        }

        if(empty($src)){
            $crontab->status=-1;
            $crontab->msg='此页面不存在';
            $crontab->error='404';
            $crontab->pid=0;
            $crontab->save();
            throw new CException('此页面不存在');
        }

        $o = Tools::OZCurl($src, 600, false);
        if(empty($o['Result'])){
            $crontab->status=-1;
            $crontab->msg='页面为空';
            $crontab->error='500';
            $crontab->pid=0;
            $crontab->save();
            throw new CException('页面为空');
        }

        include_once(
            Yii::getPathOfAlias(
                'application.extensions.simple_html_dom'
            ).DIRECTORY_SEPARATOR.'simple_html_dom.php'
        );
        $html = str_get_html($o['Result']);

        $description=Tools::subString_UTF8(trim($html->find('span[id=articlecontent]', 0)->plaintext),0,200);
        $search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
            "'<[\/\!]*?[^<>]*?>'si",           // 去掉 HTML 标记
            "'([\r\n])[\s]+'",                 // 去掉空白字符
            "'\/'",
            "'&(quot|#34);'i",                 // 替换 HTML 实体
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&#(\d+);'e");                    // 作为 PHP 代码运行
        $replace = array ("",
            "",
            "\\1",
            "",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            "chr(\\1)");
        $description = preg_replace ($search, $replace, $description);

        $adName='ad'.mt_rand(3,4);
        $body=trim('<div style="float:right;">'.Yii::app()->params[$adName].'</div>'.Tools::formatHtml($html->find('span[id=articlecontent]', 0)->innertext));
        $title=trim($html->find('title', 0)->plaintext);

        $keywords=preg_split('/[+-,\/\\\s?;.\[\]\<\>]+/', $title, -1, PREG_SPLIT_NO_EMPTY);
        $keywords[]=trim($html->find('td[class=mz]', 0)->plaintext);
        $keywords=implode(',', $keywords);

        $category=trim($html->find('span[class=bulebold bulelink]', 0)->plaintext, '[]');
        $wp = new WpRemote($domain, $user, $pass);
        //取得分类成功（没有则自动创建）
        if($wp->getCate($category)){
            $publish = array(
                'title'=>$title,
                'description'=>$body,
                'mt_allow_comments'=>1,  // 1 to allow comments
                'mt_allow_pings'=>1,		// 1 to allow trackbacks
                'mt_excerpt'=>$description,
                'post_type'=>'post',
                'mt_keywords'=>$keywords,
                'categories'=>array($category),
                'custom_fields' => array(
                    array("key"=>"description","value"=>"$description"),
                    array("key"=>"keyword","value"=>"$keywords"),
                    array("key"=>"sid","value"=>"$sid"),
                    array("key"=>"author","value"=>"yardown.com"),
                )
            );
            if($pid=$wp->postContent($publish)){
                $crontab->status=1;
                $crontab->msg="成功发布: $title | ID :$pid";
                $crontab->error=serialize(array('pid'=>$pid,'sid'=>$sid));
                $crontab->pid=$pid;
                $crontab->save();
                return true;
            }else{
                $crontab->status=-1;
                $crontab->msg="发布失败: $title | SID :$sid";
                $crontab->error=serialize(array('pid'=>$pid,'sid'=>$sid));
                $crontab->pid=0;
                $crontab->save();
                throw new CException('发布帖子失败');
            }
        }else{
            $crontab->status='505';
            $crontab->msg='创建分类失败';
            $crontab->error='';
            $crontab->save();
            throw new CException('创建分类失败');
        }
    }
}
