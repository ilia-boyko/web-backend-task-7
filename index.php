<?php
$user = 'u47534';
$pass = '6518561';
$db = new PDO('mysql:host=localhost;dbname=u47534', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
$pass_hash=array();
try{
  $get=$db->prepare("select pass from admin where user=?");
  $get->execute(array('admin'));
  $pass_hash=$get->fetchAll()[0][0];
}
catch(PDOException $e){
  print('Error: '.$e->getMessage());
}
if (empty($_SERVER['PHP_AUTH_USER']) ||
      empty($_SERVER['PHP_AUTH_PW']) ||
      $_SERVER['PHP_AUTH_USER'] != 'admin' ||
      md5($_SERVER['PHP_AUTH_PW']) != $pass_hash) {
    header('HTTP/1.1 401 Unanthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Unauthorized (Требуется авторизация)</h1>');
    exit();
}
if(empty($_GET['edit_id'])){
  header('Location: admin.php');
}
header('Content-Type: text/html; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();
  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
    setcookie('name_value', '', 100000);
    setcookie('email_value', '', 100000);
    setcookie('year_value', '', 100000);
    setcookie('pol_value', '', 100000);
    setcookie('limb_value', '', 100000);
    setcookie('bio_value', '', 100000);
    setcookie('power_value', '', 100000);
    setcookie('telepat_value', '', 100000);
    setcookie('noclip_value', '', 100000);
    setcookie('immortal_value', '', 100000);
    setcookie('check_value', '', 100000);
  }
  
  $errors = array();
  $error=FALSE;
  $errors['field-name'] = !empty($_COOKIE['name_error']);
  $errors['field-email'] = !empty($_COOKIE['email_error']);
  $errors['year'] = !empty($_COOKIE['year_error']);
  $errors['radio-pol'] = !empty($_COOKIE['pol_error']);
  $errors['radio-limb'] = !empty($_COOKIE['limb_error']);
  $errors['field-super'] = !empty($_COOKIE['super_error']);
  $errors['field-bio'] = !empty($_COOKIE['bio_error']);
  $errors['checkbox'] = !empty($_COOKIE['check_error']);
  if ($errors['field-name']) {
    setcookie('name_error', '', 100000);
    $messages[] = '<div class="error">Заполните имя или у него неверный формат (only English)</div>';
    $error=TRUE;
  }
  if ($errors['field-email']) {
    setcookie('email_error', '', 100000);
    $messages[] = '<div class="error">Заполните имейл или у него неверный формат</div>';
    $error=TRUE;
  }
  if ($errors['year']) {
    setcookie('year_error', '', 100000);
    $messages[] = '<div class="error">Выберите год.</div>';
    $error=TRUE;
  }
  if ($errors['radio-pol']) {
    setcookie('pol_error', '', 100000);
    $messages[] = '<div class="error">Выберите пол.</div>';
    $error=TRUE;
  }
  if ($errors['radio-limb']) {
    setcookie('limb_error', '', 100000);
    $messages[] = '<div class="error">Укажите кол-во конечностей.</div>';
    $error=TRUE;
  }
  if ($errors['field-super']) {
    setcookie('super_error', '', 100000);
    $messages[] = '<div class="error">Выберите суперспособности(хотя бы одну).</div>';
    $error=TRUE;
  }
  if ($errors['field-bio']) {
    setcookie('bio_error', '', 100000);
    $messages[] = '<div class="error">Заполните биографию или у неё неверный формат (only English)</div>';
    $error=TRUE;
  }
  $values = array();
  $values['immortal'] = 0;
  $values['noclip'] = 0;
  $values['power'] = 0;
  $values['telepat'] = 0;
  
  $user = 'u47534';
  $pass = '6518561';
  setcookie('isLogged',1,$cookie_options);
  header('CSRF-Token: '.$_SESSION['csrf_token']);
  $db = new PDO('mysql:host=localhost;dbname=u47534', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
  try{
      $id=$_GET['edit_id'];
      $get=$db->prepare("SELECT * FROM application WHERE id=?");
      $get->bindParam(1,$id);
      $get->execute();
      $inf=$get->fetchALL();
      $values['field-name']=$inf[0]['name'];
      $values['field-email']=$inf[0]['email'];
      $values['year']=$inf[0]['year'];
      $values['radio-pol']=$inf[0]['pol'];
      $values['radio-limb']=$inf[0]['konech'];
      $values['field-bio']=$inf[0]['biogr'];
    
      $get2=$db->prepare("SELECT name FROM superp WHERE per_id=?");
      $get2->bindParam(1,$id);
      $get2->execute();
      $inf2=$get2->fetchALL();
      for($i=0;$i<count($inf2);$i++){
        if($inf2[$i]['name']=='power'){
          $values['power']=1;
        }
        if($inf2[$i]['name']=='telepat'){
          $values['telepat']=1;
        }
        if($inf2[$i]['name']=='immortal'){
          $values['immortal']=1;
        }
        if($inf2[$i]['name']=='noclip'){
          $values['noclip']=1;
        }
      }
    }
    catch(PDOException $e){
      print('Error: '.$e->getMessage());
      exit();
  }
  include('form.php');
}
else {
  if(!empty($_POST['edit'])){
    $id=$_POST['dd'];
    $name = $_POST['field-name'];
    $email = $_POST['field-email'];
    $year = $_POST['year'];
    $pol=$_POST['radio-pol'];
    $limbs=$_POST['radio-limb'];
    $powers=$_POST['field-super'];
    $bio=$_POST['field-bio'];

    //Регулярные выражения
    $bioregex = "/^\s*\w+[\w\s\.,-]*$/";
    $nameregex = "/^\w+[\w\s-]*$/";
    $mailregex = "/^[\w\.-]+@([\w-]+\.)+[\w-]{2,4}$/";
    $errors = FALSE;
    
    if (empty($name) || (!preg_match($nameregex,$name))) {
      setcookie('name_error', '1', time() + 24*60 * 60);
      setcookie('name_value', '', 100000);
      $errors = TRUE;
    }

    if (empty($email) || !filter_var($email,FILTER_VALIDATE_EMAIL) ||
     (!preg_match($mailregex,$email))) {
      setcookie('email_error', '1', time() + 24*60 * 60);
      setcookie('email_value', '', 100000);
      $errors = TRUE;
    }
    
    if ($year=='Год') {
      setcookie('year_error', '1', time() + 24 * 60 * 60);
      setcookie('year_value', '', 100000);
      $errors = TRUE;
    }
   
    if (!isset($pol)) {
      setcookie('pol_error', '1', time() + 24 * 60 * 60);
      setcookie('pol_value', '', 100000);
      $errors = TRUE;
    }
    
    if (!isset($limbs)) {
      setcookie('limb_error', '1', time() + 24 * 60 * 60);
      setcookie('limb_value', '', 100000);
      $errors = TRUE;
    }

    if (!isset($powers)) {
      setcookie('super_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    
    if ((empty($bio)) || (!preg_match($bioregex,$bio))) {
      setcookie('bio_error', '1', time() + 24 * 60 * 60);
      setcookie('bio_value', '', 100000);
      $errors = TRUE;
    }
    
    if ($errors) {
      setcookie('save','',100000);
      header('Location: index.php?edit_id='.$id);
    }
    else {
      setcookie('name_error', '', 100000);
      setcookie('email_error', '', 100000);
      setcookie('year_error', '', 100000);
      setcookie('pol_error', '', 100000);
      setcookie('limb_error', '', 100000);
      setcookie('super_error', '', 100000);
      setcookie('bio_error', '', 100000);
      setcookie('check_error', '', 100000);
    }
    
    $user = 'u47534';
    $pass = '6518561';
    $db = new PDO('mysql:host=localhost;dbname=u47534', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
    if(!$errors){
      /*$upd=$db->prepare("UPDATE application SET name=:name, email=:email, year=:byear, pol=:pol, konech=:limbs, biogr=:bio WHERE id=:id");
      $cols=array(
        ':name'=>$name,
        ':email'=>$email,
        ':byear'=>$year,
        ':pol'=>$pol,
        ':limbs'=>$limbs,
        ':bio'=>$bio
      );
      foreach($cols as $k=>&$v){
        $upd->bindParam($k,$v);
      }
      $upd->bindParam(':id',$id);
      $upd->execute();*/
	  if($_POST['csrf_token']!=$_SESSION['csrf_token']){
		print_r("CSRF invalid!");
		exit();
	}
	try{
	  dbQuery($db,"UPDATE application SET name=:name, email=:email, year=:byear, pol=:pol, konech=:limbs, biogr=:bio WHERE id=:id",array(
        'name'=>$name,
        'email'=>$email,
        'byear'=>$year,
        'pol'=>$pol,
        'limbs'=>$limbs,
        'bio'=>$bio
      ));
      /*$del=$db->prepare("DELETE FROM superp WHERE per_id=?");
      $del->execute(array($id));*/
	  dbQuery($db,"DELETE FROM superp WHERE per_id=?",array($id));
      $upd1=$db->prepare("INSERT INTO superp SET name=:power,per_id=:id",);
      $upd1->bindParam(':id',$id);
      foreach($powers as $pwr){
        $upd1->bindParam(':power',$pwr);
        $upd1->execute();
      }
    }
    
    if(!$errors){
      setcookie('save', '1');
    }
    header('Location: index.php?edit_id='.$id);
  }
  else {
    $id=$_POST['dd'];
    $user = 'u47534';
    $pass = '6518561';
    $db = new PDO('mysql:host=localhost;dbname=u47534', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
    try {
      $del=$db->prepare("DELETE FROM superp WHERE per_id=?");
      $del->execute(array($id));
      $stmt = $db->prepare("DELETE FROM application WHERE id=?");
      $stmt -> execute(array($id));
    }
    catch(PDOException $e){
      print('Error : ' . $e->getMessage());
    exit();
    }
    setcookie('del','1');
    setcookie('del_user',$id);
    header('Location: admin.php');
  }

}
