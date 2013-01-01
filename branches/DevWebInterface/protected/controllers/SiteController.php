<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
				// captcha action renders the CAPTCHA image displayed on the contact page
				'captcha'=>array(
						'class'=>'CCaptchaAction',
						'backColor'=>0xFFFFFF,
				),
				// page action renders "static" pages stored under 'protected/views/site/pages'
				// They can be accessed via: index.php?r=site/page&view=FileName
				'page'=>array(
						'class'=>'CViewAction',
				),
		);
	}

	public function filters()
	{
		return array(
				'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
				array('deny',
						'actions'=>array('changePassword','inviteUser', 'registerGPSTracker'),
						'users'=>array('?'),
				)
		);
	}


	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}



	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	public function actionLogin()
	{
		$model = new LoginForm;
			
		$processOutput = true;

		// collect user input data
		if(isset($_REQUEST['LoginForm']))
		{
			$model->attributes = $_REQUEST['LoginForm'];
			// validate user input and if ok return json data and end application.

			// 			if (Yii::app()->request->isAjaxRequest) {
			// 				$processOutput = false;
			// 			}

			if($model->validate() && $model->login()) {
				if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
				{
					echo CJSON::encode(array(
							"result"=> "1",
							"id"=>Yii::app()->user->id,
							"realname"=> $model->getName(),
							"minDataSentInterval"=> Yii::app()->params->minDataSentInterval,
							"minDistanceInterval"=> Yii::app()->params->minDistanceInterval,
					));
				}
				else {
					//echo 'Model NOT valid in SiteController';
					Yii::app()->clientScript->scriptMap['jquery.js'] = false;
					Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
					$this->renderPartial('loginSuccessful',array('id'=>Yii::app()->user->id, 'realname'=>$model->getName()), false, $processOutput);
				}

				Yii::app()->end();
			}
			else
			{
				//echo 'model NOT valid';

				if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
				{
					$result = "1"; //Initialize with "1" to be used whether no error occured

					if ($model->getError('password') != null) {
						$result = $model->getError('password');
					}
					else if ($model->getError('email') != null) {
						$result = $model->getError('email');
					}
					else if ($model->getError('rememberMe') != null) {
						$result = $model->getError('rememberMe');
					}

					echo CJSON::encode(array(
							"result"=> $result,
							"id"=>Yii::app()->user->id,
							"realname"=> $model->getName(),
							"minDataSentInterval"=> Yii::app()->params->minDataSentInterval,
							"minDistanceInterval"=> Yii::app()->params->minDistanceInterval,
					));
				}
				else {
					//echo 'Model NOT valid in SiteController';

					Yii::app()->clientScript->scriptMap['jquery.js'] = false;
					Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
					$this->renderPartial('login',array('model'=>$model), false, $processOutput);
				}

				Yii::app()->end();
			}
		}
		else
		{
			//echo 'LoginForm NOT set';
		}
	}

	/**
	 *
	 * facebook login action
	 */
	public function actionFacebooklogin() {
		Yii::import('ext.facebook.*');
		$ui = new FacebookUserIdentity('370934372924974', 'c1e85ad2e617b480b69a8e14cfdd16c7');

		if ($ui->authenticate()) {
			$user=Yii::app()->user;
			$user->login($ui);

			$this->FB_Web_Register($nd);
			if($nd == 0)
			{
					


				$str=array("email" => Yii::app()->session['facebook_user']['email'] ,"password" => Yii::app()->session['facebook_user']['id']) ;

					
				$this->fbLogin($str);
					

			}else {

			}


			//exit;
			$this->redirect($user->returnUrl);
		} else {
			throw new CHttpException(401, $ui->error);
		}
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		if (isset($_REQUEST['client']) && $_REQUEST['client'] == 'mobile') {
			// if mobile client end the app, no need to redirect...
			echo CJSON::encode(array(
					"result"=> "1"));
			Yii::app()->end();
		}
		else {
			$this->redirect(Yii::app()->homeUrl);
		}
	}


	/**
	 * Changes the user's current password with the new one
	 */
	public function actionChangePassword()
	{
		$model = new ChangePasswordForm;

		$processOutput = true;
		// collect user input data
		if(isset($_POST['ChangePasswordForm']))
		{
			$model->attributes=$_POST['ChangePasswordForm'];
			// validate user input and if ok return json data and end application.
			if($model->validate()) {
				//$users=Users::model()->findByPk(Yii::app()->user->id);
				//$users->password=md5($model->newPassword);

				//if($users->save()) // save the change to database
				if(Users::model()->changePassword(Yii::app()->user->id, $model->newPassword)) // save the change to database
				{
					echo CJSON::encode(array("result"=> "1"));
				}
				else
				{
					echo CJSON::encode(array("result"=> "0"));
				}
				Yii::app()->end();
			}

			if (Yii::app()->request->isAjaxRequest) {
				$processOutput = false;

			}
		}

		Yii::app()->clientScript->scriptMap['jquery.js'] = false;
		Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;

		$this->renderPartial('changePassword',array('model'=>$model), false, $processOutput);
	}

	public function actionRegister()
	{
		$model = new RegisterForm;

		$processOutput = true;

		// collect user input data
		if(isset($_REQUEST['RegisterForm']))
		{
			$model->attributes = $_REQUEST['RegisterForm'];

			// validate user input and if ok return json data and end application.

			// 			if (Yii::app()->request->isAjaxRequest) {
			// 				$processOutput = false;
			// 			}

			if($model->validate()) {

				$time = date('Y-m-d h:i:s');

				//echo $model->ac_id;

				if (isset($model->ac_id) && $model->ac_id != "0") {
					if (Users::model()->saveFacebookUser($model->email, md5($model->password), $model->name, $model->ac_id, $model->account_type)) {
						//echo CJSON::encode(array("result"=> "1"));

						if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
						{
							echo CJSON::encode(array(
									"result"=> "1",
							));
						}
						else
						{
							Yii::app()->clientScript->scriptMap['jquery.js'] = false;
							Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
							$this->renderPartial('register',array('model'=>$model), false, $processOutput);
							echo '<script type="text/javascript">
							TRACKER.showMessageDialog("'.Yii::t('site', 'An activation mail is sent to your e-mail address...').'");
							</script>';
						}
					}
					else {
						if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
						{
							/*
							 echo CJSON::encode(array(
							 		"result"=> "1",
							 ));
							*/
							echo JSON::encode(array("result"=>"Error in saving"));
						}
						else
						{
							Yii::app()->clientScript->scriptMap['jquery.js'] = false;
							Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
							$this->renderPartial('register',array('model'=>$model), false, $processOutput);
							echo '<script type="text/javascript">
							TRACKER.showMessageDialog("'.Yii::t('common', 'Sorry, an error occured in operation').'");
							</script>';
						}
					}
				}
				else if (UserCandidates::model()->saveUserCandidates($model->email, md5($model->password), $model->name, date('Y-m-d h:i:s')))
				{

					$key = md5($model->email.$time);
					$message = 'Hi '.$model->name.',<br/> <a href="http://'.Yii::app()->request->getServerName() . $this->createUrl('site/activate',array('email'=>$model->email,'key'=>$key)).'">'.
							'Click here to register to traceper</a> <br/>';
					$message .= '<br/> Your Password is :'.$model->password;
					$message .= '<br/> The Traceper Team';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers  .= 'From: '. Yii::app()->params->contactEmail .'' . "\r\n";
					//echo $message;
					@mail($model->email, "Traceper Activation", $message, $headers);


					//echo CJSON::encode(array("result"=> "1"));
					if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
					{
						echo CJSON::encode(array(
								"result"=> "1",
						));
					}
					else
					{
						Yii::app()->clientScript->scriptMap['jquery.js'] = false;
						Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
						$this->renderPartial('register',array('model'=>$model), false, $processOutput);
						echo '<script type="text/javascript">
						TRACKER.showMessageDialog("'.Yii::t('site', 'An activation mail is sent to your e-mail address...').'");
						</script>';
					}
				}
				else
				{
					//echo CJSON::encode(array("result"=> "Error in saving"));
					if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
					{
						/*
						 echo CJSON::encode(array(
						 		"result"=> "1",
						 ));
						*/
						echo JSON::encode(array("result"=>"Error in saving"));
					}
					else
					{
						Yii::app()->clientScript->scriptMap['jquery.js'] = false;
						Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
						$this->renderPartial('register',array('model'=>$model), false, $processOutput);
						echo '<script type="text/javascript">
						TRACKER.showMessageDialog("'.Yii::t('common', 'Sorry, an error occured in operation').'");
						</script>';
					}
				}


				Yii::app()->end();
			}
			else
			{
				if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile')
				{
					$result = "1"; //Initialize with "1" to be used whether no error occured

					if ($model->getError('password') != null) {
						$result = $model->getError('password');
					}
					else if ($model->getError('email') != null) {
						$result = $model->getError('email');
					}
					else if ($model->getError('passwordAgain') != null) {
						$result = $model->getError('passwordAgain');
					}
					else if ($model->getError('passwordAgain') != null) {
						$result = $model->getError('passwordAgain');
					}

					echo CJSON::encode(array(
							"result"=> $result,
					));
				}
				else
				{
					//echo 'RegisterForm not valid';

					Yii::app()->clientScript->scriptMap['jquery.js'] = false;
					Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
					$this->renderPartial('register',array('model'=>$model), false, $processOutput);
				}

				Yii::app()->end();
			}
		}
		else
		{
			//echo 'RegisterForm is NOT set';
		}
	}

	public function actionIsFacebookUserRegistered(){

		$result = "Missing parameter";
		if (isset($_REQUEST['email']) && $_REQUEST['email'] != NULL
			 && isset($_REQUEST['facebookId']) && $_REQUEST['facebookId'] != NULL)
		{
			$email = $_REQUEST['email'];
			$facebookId = $_REQUEST['facebookId'];
			$result = "0";
			if (Users::model()->isFacebookUserRegistered($email, $facebookId)){
				$result = "1";
			}
		}
		echo CJSON::encode(array(
				"result"=> $result,
		));
	}



	//facebook web register
	public function FB_Web_Register()
	{
		$result = 0;
			
		// validate user input and if ok return json data and end application.
		if(Yii::app()->session['facebook_user']) {

			if (Users::model()->saveFacebookUser(Yii::app()->session['facebook_user']['email'], md5(Yii::app()->session['facebook_user']['id']), Yii::app()->session['facebook_user']['name'], Yii::app()->session['facebook_user']['id'], 1))
			{
				$result = 1;
			}
			else
			{
				$result = 0;
			}

		}
		return $result;
	}


	public function actionRegisterGPSTracker()
	{
		$model = new RegisterGPSTrackerForm;

		$processOutput = true;
		$isMobileClient = false;
		// collect user input data
		if(isset($_POST['RegisterGPSTrackerForm']))
		{
			$isMobileClient = false;
			if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile') {
				$isMobileClient = true;
			}
			$model->attributes = $_POST['RegisterGPSTrackerForm'];
			// validate user input and if ok return json data and end application.
			if($model->validate()) {

				//Check whether a device exists with the same name in the Users table (Since the table 'Users' is used as common for both
				//real users and devices we cannot add unique index for realname, so we have to check same name existance manually)
				if(Users::model()->find('userType=:userType AND realname=:name', array(':userType'=>UserType::GPSDevice, ':name'=>$model->name)) == null)
				{
					try
					{
						if (Users::model()->saveGPSUser($model->deviceId, md5($model->name), $model->name, UserType::GPSDevice, 0))
						{

							if(Friends::model()->makeFriends(Yii::app()->user->id, Users::model()->getUserId($model->deviceId)))
							{
								echo CJSON::encode(array("result"=> "1"));
							}
							else
							{
								echo CJSON::encode(array("result"=> "Unknown error 1"));
							}
						}
						else
						{
							echo CJSON::encode(array("result"=> "Unknown error 2"));
						}
					}
					catch (Exception $e)
					{
						if($e->getCode() == Yii::app()->params->duplicateEntryDbExceptionCode) //Duplicate Entry
						{
							echo CJSON::encode(array("result"=> "Duplicate Entry"));
						}
						else
						{
							echo 'Caught exception: ',  $e->getMessage(), "\n";
							echo 'Code: ', $e->getCode(), "\n";
						}
						Yii::app()->end();
							

					}
				}
				else
				{
					echo CJSON::encode(array("result"=> "Duplicate Name"));
				}

				Yii::app()->end();
			}

			if (Yii::app()->request->isAjaxRequest) {
				$processOutput = false;

			}
		}

		if ($isMobileClient == true)
		{
			$result = "1"; //Initialize with "1" to be used whether no error occured

			if ($model->getError('password') != null) {
				$result = $model->getError('password');
			}
			else if ($model->getError('email') != null) {
				$result = $model->getError('email');
			}
			else if ($model->getError('passwordAgain') != null) {
				$result = $model->getError('passwordAgain');
			}
			else if ($model->getError('passwordAgain') != null) {
				$result = $model->getError('passwordAgain');
			}

			echo CJSON::encode(array(
					"result"=> $result,
			));
			Yii::app()->end();
		}
		else {
			Yii::app()->clientScript->scriptMap['jquery.js'] = false;
			Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
			$this->renderPartial('registerGPSTracker',array('model'=>$model), false, $processOutput);
		}

	}

	public function actionRegisterNewStaff()
	{
		$model = new RegisterNewStaffForm;

		$processOutput = true;
		$isMobileClient = false;
		// collect user input data
		if(isset($_POST['RegisterNewStaffForm']))
		{
			$isMobileClient = false;
			if (isset($_REQUEST['client']) && $_REQUEST['client']=='mobile') {
				$isMobileClient = true;
			}
			$model->attributes = $_POST['RegisterNewStaffForm'];
			// validate user input and if ok return json data and end application.
			if($model->validate()) {

				try
				{
					if(Users::model()->saveUser($model->email, md5($model->password), $model->name, UserType::RealStaff/*userType*/, 0/*accountType*/))
					{
						if(Friends::model()->makeFriends(Yii::app()->user->id, Users::model()->getUserId($model->email)))
						{
							echo CJSON::encode(array("result"=> "1"));
						}
						else
						{
							echo CJSON::encode(array("result"=> "Unknown error 1"));
						}
					}
					else
					{
						echo CJSON::encode(array("result"=> "Unknown error 2"));
					}
				}
				catch (Exception $e)
				{
					if($e->getCode() == Yii::app()->params->duplicateEntryDbExceptionCode) //Duplicate Entry
					{
						echo CJSON::encode(array("result"=> "Duplicate Entry"));
					}
					Yii::app()->end();
				}

				Yii::app()->end();
			}

			if (Yii::app()->request->isAjaxRequest) {
				$processOutput = false;

			}
		}

		if ($isMobileClient == true)
		{
			$result = "1"; //Initialize with "1" to be used whether no error occured

			if ($model->getError('password') != null) {
				$result = $model->getError('password');
			}
			else if ($model->getError('email') != null) {
				$result = $model->getError('email');
			}
			else if ($model->getError('passwordAgain') != null) {
				$result = $model->getError('passwordAgain');
			}
			else if ($model->getError('passwordAgain') != null) {
				$result = $model->getError('passwordAgain');
			}

			echo CJSON::encode(array(
					"result"=> $result,
			));
			Yii::app()->end();
		}
		else {
			Yii::app()->clientScript->scriptMap['jquery.js'] = false;
			Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
			$this->renderPartial('registerNewStaff',array('model'=>$model), false, $processOutput);
		}

	}

	public function actionInviteUsers()
	{
		$model = new InviteUsersForm;

		$processOutput = true;
		// collect user input data
		if(isset($_POST['InviteUsersForm']))
		{
			$model->attributes = $_POST['InviteUsersForm'];
			// validate user input and if ok return json data and end application.
			if($model->validate()) {

				$emailArray= $this->splitEmails($model->emails);
				$arrayLength = count($emailArray);
				$invitationSentCount = 0;
				for ($i = 0; $i < $arrayLength; $i++)
				{
					/*
					 $dt = date("Y-m-d H:m:s");

					$invitedUsers = new InvitedUsers;
					$invitedUsers->email = $emailArray[$i];
					$invitedUsers->dt = $dt;

					if ($invitedUsers->save())
						*/
					if(InvitedUsers::model()->saveInvitedUsers($emailArray[$i], date("Y-m-d H:m:s")))
					{
						$key = md5($emailArray[$i].$dt);
						//send invitation mail
						$invitationSentCount++;

						//Invitation kontrol� yap�ld���nda bu k�s�m a��lacak

						//$message = 'Hi ,<br/> You have been invited to traceper by one of your friends <a href="'.$this->createUrl('site/register',array('invitation'=>true, 'email'=>$emailArray[$i],'key'=>$key)).'">'.
						//'Click here to register to traceper</a> <br/>';

						$message = 'Hi ,<br/> You have been invited to traceper by one of your friends <a href="'.$this->createUrl('site/register').'">'.
								'Click here to register to traceper</a> <br/>';
						$message .= '<br/> ' . $model->message;
						$message .= '<br/> The Traceper Team';
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers  .= 'From: contact@traceper.com' . "\r\n";
						//echo $message;
						mail($emailArray[$i], "Traceper Invitation", $message, $headers);
					}
				}

				if ($arrayLength == $invitationSentCount) // save the change to database
				{
					echo CJSON::encode(array("result"=> "1"));
				}
				else
				{
					echo CJSON::encode(array("result"=> "0"));
				}
				Yii::app()->end();
			}

			if (Yii::app()->request->isAjaxRequest) {
				$processOutput = false;

			}
		}

		Yii::app()->clientScript->scriptMap['jquery.js'] = false;
		Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;

		$this->renderPartial('inviteUsers',array('model'=>$model), false, $processOutput);
	}

	private function splitEmails($emails)
	{
		$emails = str_replace(array(" ",",","\r","\n"),array(";",";",";",";"),$emails);
		$emails = str_replace(";;", ";",$emails);
		$emails = explode(";", $emails);
		return $emails;
	}

	public function actionActivate()
	{
		$result = "Sorry, you entered this page with wrong parameters";
		if (isset($_GET['email']) && $_GET['email'] != null
				&& isset($_GET['key']) && $_GET['key'] != null
		)
		{
			$email = $_GET['email'];
			$key = $_GET['key'];

			$processOutput = true;
			// collect user input data

			$criteria=new CDbCriteria;
			$criteria->select='Id,email,realname,password,time';
			$criteria->condition='email=:email';
			$criteria->params=array(':email'=>$email);
			$userCandidate = UserCandidates::model()->find($criteria); // $params is not needed

			$generatedKey =  md5($email.$userCandidate->time);
			if ($generatedKey == $key)
			{
				$result = "Sorry, there is a problem in activating the user";
				if(Users::model()->saveUser($userCandidate->email, $userCandidate->password, $userCandidate->realname, UserType::RealUser/*userType*/, 0/*accountType*/))
				{
					$userCandidate->delete();
					$result = "Your account has been activated successfully, you can login now";
					//echo CJSON::encode(array("result"=> "1"));
				}
			}
		}

		$this->renderPartial('accountActivationResult',array('result'=>$result), false, true);
	}
}



