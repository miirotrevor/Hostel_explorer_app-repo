<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	function save_accommodation(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','description'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			if(!empty($data)) $data .=",";
				$data .= " `description`='".addslashes(htmlentities($description))."' ";
		}
		if(empty($id)){
			$sql = "INSERT INTO `accommodations` set {$data} ";
			$save = $this->conn->query($sql);
			$id = $this->conn->insert_id;
		}else{
			$sql = "UPDATE `accommodations` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"New Accommodation successfully saved.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}

	function delete_accommodation(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `accommodations` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			if(is_dir(base_app.'uploads/accommodation_'.$id)){
				$file = scandir(base_app.'uploads/accommodation_'.$id);
				foreach($file as $img){
					if(in_array($img,array('..','.')))
						continue;
					unlink(base_app.'uploads/accommodation_'.$id.'/'.$img);
				}
				rmdir(base_app.'uploads/accommodation_'.$id);
			}
			$this->settings->set_flashdata('success',"Tour accommodation successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_room(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','description'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			if(!empty($data)) $data .=",";
				$data .= " `description`='".addslashes(htmlentities($description))."' ";
		}
		if(empty($id)){
			$sql = "INSERT INTO `room_list` set {$data} ";
			$save = $this->conn->query($sql);
			$id = $this->conn->insert_id;
		}else{
			$sql = "UPDATE `room_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			if(isset($_FILES['img']) && count($_FILES['img']['tmp_name']) > 0){
				if(!is_dir(base_app.'uploads/room_'.$id)){
					mkdir(base_app.'uploads/room_'.$id);
					$data = " `upload_path`= 'uploads/room_{$id}' ";
				}else{
					$data = " `upload_path`= 'uploads/room_{$id}' ";
				}
				$this->conn->query("UPDATE `room_list` set {$data} where id = '{$id}' ");
				foreach($_FILES['img']['tmp_name'] as $k =>$v){
					move_uploaded_file($_FILES['img']['tmp_name'][$k],base_app.'uploads/room_'.$id.'/'.$_FILES['img']['name'][$k]);
				}
			}

			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"New Package successfully saved.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_p_img(){
		extract($_POST);
		if(is_file($path)){
			if(unlink($path)){
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['error'] = 'unlink file failed.';
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = 'unlink file failed. File do not exist.';
		}
		return json_encode($resp);
	}

	function register(){
		extract($_POST);
		$data = "";
		$_POST['password'] = md5($password);
		foreach($_POST as $k =>$v){
				if(!empty($data)) $data .=",";
					$data .= " `{$k}`='{$v}' ";
		}
		$check = $this->conn->query("SELECT * FROM `users` where username='{$username}' ")->num_rows;
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Username already taken.";
			return json_encode($resp);
			exit;
		}
		$save = $this->conn->query("INSERT INTO `users` set $data ");
		if($save){
			foreach($_POST as $k =>$v){
				$this->settings->set_userdata($k,$v);
			}
			$this->settings->set_userdata('id',$this->conn->insert_id);
			$resp['status'] = 'success';
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function update_account(){
		extract($_POST);
		$data = "";
		if(!empty($password)){
			$_POST['password'] = md5($password);
			if(md5($cpassword) != $this->settings->userdata('password')){
				$resp['status'] = 'failed';
				$resp['msg'] = "Current Password is Incorrect";
				return json_encode($resp);
				exit;
			}

		}
		$check = $this->conn->query("SELECT * FROM `users`  where `username`='{$username}' and `id` != $id ")->num_rows;
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Username already taken.";
			return json_encode($resp);
			exit;
		}
		foreach($_POST as $k =>$v){
			if($k == 'cpassword' || ($k == 'password' && empty($v)))
				continue;
				if(!empty($data)) $data .=",";
					$data .= " `{$k}`='{$v}' ";
		}
		$save = $this->conn->query("UPDATE `users` set $data where id = $id ");
		if($save){
			foreach($_POST as $k =>$v){
				if($k != 'cpassword')
				$this->settings->set_userdata($k,$v);
			}
			
			$this->settings->set_userdata('id',$this->conn->insert_id);
			$resp['status'] = 'success';
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function book_room(){
		extract($_POST);
		$data ="";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','accom_id'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$accom_id = implode(',',$accom_id);
		$data .= ", accommodation_ids='{$accom_id}' ";
		$data .= ", user_id='{$this->settings->userdata('id')}' ";

		$sql = "INSERT INTO `bookings` set $data";
		$save = $this->conn->query($sql);

		if($save){
			$resp['status'] ='success';
		}else{
			$resp['status'] = 'failed';
			$resp['sql'] = $sql;
			$resp['err'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function update_book_status(){
		extract($_POST);
		$update = $this->conn->query("UPDATE `bookings` set `status` = '{$status}' where id ='{$id}' ");
		if($update){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Book successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
			return json_encode($resp);
	}
	function save_inquiry(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
				if(!empty($data)) $data .=",";
					$data .= " `{$k}`='{$v}' ";
		}
		$save = $this->conn->query("INSERT INTO `inquiry` set $data");
		if($save){
			$resp['status'] = 'success';
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_accommodation':
		echo $Master->save_accommodation();
	break;
	case 'delete_accommodation':
		echo $Master->delete_accommodation();
	break;
	case 'delete_p_img':
		echo $Master->delete_p_img();
	break;
	case 'save_room':
		echo $Master->save_room();
	break;
	case 'delete_room':
		echo $Master->delete_room();
	break;
	case 'register':
		echo $Master->register();
	break;
	case 'update_account':
		echo $Master->update_account();
	break;
	case 'book_room':
		echo $Master->book_room();
	break;
	case 'update_book_status':
		echo $Master->update_book_status();
	break;
	case 'save_inquiry':
		echo $Master->save_inquiry();
	break;
	default:
		// echo $sysset->index();
		break;
}