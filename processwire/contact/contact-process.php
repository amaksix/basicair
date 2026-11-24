<?php

$address = "info@basicair.lv";
if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

$error = false;
$fields = array('name', 'email', 'message');

foreach ($fields as $field) {
	if (empty($_POST[$field]) || trim($_POST[$field]) == '')
		$error = true;
}

$isLegalEntity = !empty($_POST['is_legal_entity']);

if ($isLegalEntity) {
	$companyFields = array('company_name', 'company_reg_number', 'company_vat_number');
	foreach ($companyFields as $field) {
		if (empty($_POST[$field]) || trim($_POST[$field]) == '')
			$error = true;
	}
}

if (!$error) {

	$name = stripslashes($_POST['name']);
	$email = trim($_POST['email']);
	$message = stripslashes($_POST['message']);

	$e_subject = 'BasicAir contact form: ' . $name;

	$e_body = "You have been contacted by: $name" . PHP_EOL . PHP_EOL;
	$e_reply = "E-mail: $email" . PHP_EOL . PHP_EOL;

	if ($isLegalEntity) {
		$companyName = stripslashes($_POST['company_name']);
		$companyReg = stripslashes($_POST['company_reg_number']);
		$companyVat = stripslashes($_POST['company_vat_number']);
		$e_body .= "Legal entity: Yes" . PHP_EOL;
		$e_body .= "Company: $companyName" . PHP_EOL;
		$e_body .= "Reg. No.: $companyReg" . PHP_EOL;
		$e_body .= "VAT Reg. No.: $companyVat" . PHP_EOL . PHP_EOL;
	}

	$e_content = "Message:" . PHP_EOL . $message . PHP_EOL . PHP_EOL;

	$msg = wordwrap($e_body . $e_reply . $e_content, 70);

	$headers = "From: $email" . PHP_EOL;
	$headers .= "Reply-To: $email" . PHP_EOL;
	$headers .= "MIME-Version: 1.0" . PHP_EOL;
	$headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
	$headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;

	if (mail($address, $e_subject, $msg, $headers)) {
		echo 'Success';
	} else {
		echo 'ERROR!';
	}
}
