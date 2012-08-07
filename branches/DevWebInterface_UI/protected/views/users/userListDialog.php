<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
	    'id'=>'friendRequestsWindow',
	    // additional javascript options for the dialog plugin
	    'options'=>array(
	        'title'=>Yii::t('users', 'Friendship Requests'),
	        'autoOpen'=>false,
	        'modal'=>true, 
			'resizable'=>false      
	    ),
	));

$this->renderPartial('userList', array('dataProvider'=>$dataProvider, 'viewId'=>'userListDialog', 'friendRequestList'=>true)); 	

$this->endWidget('zii.widgets.jui.CJuiDialog');
?>