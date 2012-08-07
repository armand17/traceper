<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
	    'id'=>'geofenceSettingsWindow',
	    // additional javascript options for the dialog plugin
	    'options'=>array(
	        'title'=>Yii::t('geofence', 'Geofence Settings'),
	        'autoOpen'=>false,
	        'modal'=>true, 
			'resizable'=>false,
			'width'=> '350px'      
	    ),
	));
?>

<div>
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'geofenceSettings-form',
		'enableClientValidation'=>true,
		'clientOptions'=> array(
							'validateOnSubmit'=> true,
							'validateOnChange'=>false,
						 ),
	
	)); ?>
	
		<div class="row" style="padding-top:1em">
			<?php echo Yii::t('geofence', 'Check the geofences that you want to follow the selected user:'); ?>
		</div>		
		
		<div class="row" style="padding-top:2em;padding-left:10px">
			<?php			
				if(empty($geofencesOfUser))
				{
					echo '</br></br>'.Yii::t('geofence', 'There is no geofence to show...').'</br></br>';
					echo Yii::t('geofence', 'First create some geofence(s) please');
				}
				else
				{
					echo CHtml::activeCheckboxList(
					  $model, 'geofenceStatusArray', 
					  CHtml::listData($geofencesOfUser, 'Id', 'name'),
					  array()
					);	
				}				
				
				
			?>				
		</div>
		
		<div class="row buttons" style="padding-top:2em;text-align:center">
			<?php 
				if(!empty($geofencesOfUser))
				{
					echo CHtml::ajaxSubmitButton('Save', $this->createUrl('Geofence/UpdateGeofencePrivacy', array('friendId'=>$friendId)), 
														array(
															'success'=> 'function(result){ 
																			try {
																				var obj = jQuery.parseJSON(result);
																				if (obj.result && obj.result == "1") 
																				{
																					$("#geofenceSettingsWindow").dialog("close");
																					TRACKER.showMessageDialog("'.Yii::t('geofence', 'Your settings have been saved').'");
																				}
																				else if(obj.result && obj.result == "Duplicate Entry")
																				{
																					$("#geofenceSettingsWindow").html(result);
		
																					$("#geofenceSettingsWindow").dialog("close");
																					TRACKER.showMessageDialog("'.Yii::t('geofence', 'Select only one geofence!').'");
																				}																				
																			}
																			catch (error){
																				$("#geofenceSettingsWindow").html(result);
																			}
																		 }',														
															 ),
														null);					
				}
				else
				{
					
					echo CHtml::htmlButton('OK',  
														array(
															'onclick'=> '$("#geofenceSettingsWindow").dialog("close"); return false;',
															'style'=>'text-align:center'
															 ),
														null); 					
				} 
			?>
												
			<?php 
				if(!empty($geofencesOfUser))
				{
					echo CHtml::htmlButton('Cancel',  
														array(
															'onclick'=> '$("#geofenceSettingsWindow").dialog("close"); return false;',
															 ),
														null);					
				} 
			?>												
		</div>	
		
	<?php $this->endWidget(); ?>
</div>				

<?php 
	$this->endWidget('zii.widgets.jui.CJuiDialog');
?>