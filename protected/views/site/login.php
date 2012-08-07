<?php 

// default width 300, we made text fields 250 px
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
	    'id'=>'userLoginWindow',
	    // additional javascript options for the dialog plugin
	    'options'=>array(
	        'title'=>Yii::t('site', 'Login'),
	        'autoOpen'=>false,
	        'modal'=>true, 
			'resizable'=>false,			      
	    ),
	));
?>
	<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'login-form',
		'enableClientValidation'=>true,
	
	)); ?>
		<div class="row">
			<?php echo $form->labelEx($model,'email'); ?>
			<?php echo $form->textField($model,'email', array('style'=>'width:250px')); ?>
			<?php $errorMessage = $form->error($model,'email'); 
				  if (strip_tags($errorMessage) == '') { echo '<div class="errorMessage">&nbsp;</div>'; }
				  else { echo $errorMessage; }
			?>			
		</div>
	
		<div class="row">
			<?php echo $form->labelEx($model,'password'); ?>
			<?php echo $form->passwordField($model,'password', array('style'=>'width:250px')); ?>
			<?php $errorMessage = $form->error($model,'password'); 
				  if (strip_tags($errorMessage) == '') { echo '<div class="errorMessage">&nbsp;</div>'; }
				  else { echo $errorMessage; }
			?>
		</div>
	
		<div class="row rememberMe">
			<?php echo $form->checkBox($model,'rememberMe'); ?>
			<?php echo $form->label($model,'rememberMe'); ?>
			<?php echo $form->error($model,'rememberMe'); ?>
		</div>
	
		<div class="row buttons">
			<?php echo CHtml::ajaxSubmitButton(Yii::t('site','Login'), $this->createUrl('site/login'), 
												array(
													'success'=> 'function(result){ 
																	try {
																		var obj = jQuery.parseJSON(result);
																		if (obj.result && obj.result == "1") 
																		{
																			$("#username").html(obj.realname);
																			$("#userId").html(obj.id);
																			//TRACKER.getFriendList(1);
																			$("#lists").show();
																			$("#tab_view").tabs("select",0);
																		'.
																		CHtml::ajax(
																			array(
																			'url'=>$this->createUrl('users/getFriendList', array('userType'=>array(UserType::RealUser, UserType::GPSDevice))),
																			'update'=>'#users_tab',
																			)
																		)
																		. '  '.
																		CHtml::ajax(
																			array(
																			'url'=>$this->createUrl('users/getFriendList', array('userType'=>array(UserType::RealStaff, UserType::GPSStaff))),
																			'update'=>'#staff_tab',
																			)
																		)
																		. '  '.														
																		CHtml::ajax(
																			array(
																			'url'=> $this->createUrl('upload/getList', array('fileType'=>0)),
																			'update'=>'#photos_tab',
																			)
																		)
																		. '  '.
																		CHtml::ajax(
																			array(
																			'url'=> $this->createUrl('groups/getGroupList', array('groupType'=>GroupType::FriendGroup)),
																			'update'=>'#groups_tab',
																			)
																		)
																		. '  '.														
																		CHtml::ajax(
																			array(
																			'url'=> $this->createUrl('groups/getGroupList', array('groupType'=>GroupType::StaffGroup)),
																			'update'=>'#staff_groups_tab',
																			)
																		)																	
																		 .'	
																			$("#loginBlock").hide();
																			$("#userBlock").show();
																			$("#userLoginWindow").dialog("close");
																			TRACKER.getFriendList(1);	
		  																	TRACKER.getImageList();
																		}
																	}
																	catch (error){
																		$("#userLoginWindow").html(result);
																	}
																 }',
													 ),
												null); ?>
		</div>
	
	<?php $this->endWidget(); ?>
</div>
<?php 
	$this->endWidget('zii.widgets.jui.CJuiDialog');
?>