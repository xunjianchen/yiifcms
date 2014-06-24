<?php
/**
 * Tag控制器
 *
 * @author        zhao jinhan <326196998@qq.com>
 * @copyright     Copyright (c) 2014-2015 . All rights reserved. 
 */
class TagController extends FrontBase
{
	protected $_catalog;
	protected $_menu_unique;
	protected $_tags;
	
	public function init(){
		parent::init();
		//标签
		$this->_tags = PostTags::model()->findAll(array('order'=>'data_count DESC','limit'=>20));
	}
	
  /**
   * 标签首页
   */
  public function actionIndex() {     
    $tag = trim( $this->_request->getParam( 'tag' ) );   
    //查询条件
    $post = new Post();
    $criteria = new CDbCriteria();
    $condition = "t.status_is = 'Y'";
    $tag && $condition .= " AND FIND_IN_SET('{$tag}', tags)";   
    $criteria->with = array('catalog');
    $criteria->condition = $condition;
    $criteria->order = 'view_count DESC, t.id DESC';   
    $criteria->select = "title, title_style, attach_thumb, image_list, copy_from, copy_url, last_update_time,intro,tags, view_count";
   
    //分页
    $count = $post->count( $criteria );    
    $pages = new CPagination( $count );
    $pages->pageSize = 15;
    
    $criteria->limit = $pages->pageSize;
    $criteria->offset = $pages->currentPage * $pages->pageSize;
    
    $datalist = $post->findAll($criteria);  

    //SEo
    $this->_seoTitle = $tag.' - '.$this->_setting['site_name'];  
    $navs[] = array('url'=>$this->_request->getUrl(),'name'=>$tag);
   
    //加载css,js	
    Yii::app()->clientScript->registerCssFile($this->_stylePath . "/css/list.css");
	Yii::app()->clientScript->registerScriptFile($this->_static_public . "/js/jquery/jquery.js");	
	
    $this->render( 'index', array('navs'=>$navs, 'posts'=>$datalist,'pagebar' => $pages, 'tags'=>$tags, 'last_posts'=>$last_posts));
  }  
 
}