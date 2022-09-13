<?php
require_once ("includes.php");

header ("Content-Type: application/json");

$response = array ();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	if (check_login()) {
		if (array_key_exists ("UserId", $_GET) && is_numeric ($_GET['UserId'])) {
			$user_id = intval ($_GET['UserId']);

			$balance = get_balance($user_id);
			if (is_null ($balance)) {
				return_error ("User not found");
			}
			$response["Balance"] = $balance;
		} else {
			return_error ("Missing User ID");
		}
	} else {
		return_unauthorised ("You are not logged in");
	}
} else {
	return_error ("Method not supported");
}

echo json_encode ($response);
?>

