<?php
/**
 * 批量操作
 * 
 * @author        GoldHan.zhao <326196998@qq.com>
 * @copyright     Copyright (c) 2014-2016. All rights reserved.
 */

class BatchAction extends CAction
{	
	public function run(){		
        $ids = Yii::app()->request->getParam('id');
        $command = Yii::app()->request->getParam('command');
        $status = Yii::app()->request->getParam('status');
        $type   = Yii::app()->request->getParam('type');
        
        //需要传送id的操作
        if(in_array($command, array('delete', 'deleteData'))) {
            empty( $ids ) && $this->controller->message( 'error', Yii::t('admin','No Select') );
            if(!is_array($ids)) {
                $ids = array($ids);
            }
            $criteria = new CDbCriteria();
            $criteria->addInCondition('id', $ids);        
        }
        switch ( $command ) {
            case 'delete':      
                //删除站点设置
                SpiderSetting::model()->deleteAll($criteria);                
                break;
            case 'deleteData':      
                //删除数据
                $this->_deleteTableData($ids, $type);                                
                break;
            case 'truncate':
                //清空数据
                $this->_truncateTableData($status, $type);
                break;
            default:
                $this->controller->message('error', Yii::t('admin','Error Operation'));                
        }
        $this->controller->message('success', Yii::t('admin','Batch Operate Success'));    	
	}
    
    /**
     * 批量删除列表和内容
     * 
     * @param array $ids
     * @param string $type
     */
    private function _deleteTableData($ids, $type)
    {
        $list_condition = 'id IN (' .implode(',', $ids). ')';
        $content_condtion = 'list_id IN (' .implode(',', $ids). ')';
        switch($type) {
            case 'post':
                SpiderPostList::model()->deleteAll($list_condition);
                SpiderPostContent::model()->deleteAll($content_condtion);
                break;
            default:
                break;
        }
        return true;
    }
    
    /**
     * 清空全部已采集/已导入数据
     * 
     * @param int $status
     * @param string $type
     */
    private function _truncateTableData($status, $type)
    {
        switch($type) {
            case 'post':
                SpiderPostList::model()->deleteAllByAttributes(array('status' => $status));
                break;
            default:
                break;
        }
        $this->_deleteUnlinkContent($type);
        $this->controller->message('success', Yii::t('admin', 'Truncate Finish'));
    }
    
    /**
     * 删除无关联的内容数据
     * 
     * @param string $type
     * @return boolean
     */
    private function _deleteUnlinkContent($type = '')
    {        
        switch($type) {
            case 'post':
                $ltable = (new SpiderPostList)->tableName();
                $ctable = (new SpiderPostContent)->tableName();                
                break;
            default:
                break;
        }        
        $sql = "DELETE FROM `{$ctable}` WHERE NOT EXISTS ( SELECT id FROM `{$ltable}` WHERE `{$ltable}`.id = `{$ctable}`.list_id )";
        Yii::app()->db->createCommand($sql)->execute();
        return true;
    }
}