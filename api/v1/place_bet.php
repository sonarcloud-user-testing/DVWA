<?php
require_once ("includes.php");

header ("Content-Type: application/json");

$response = array ();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (check_login()) {
		$decoded = get_json_body();
		if (property_exists ($decoded, "UserId") && is_numeric ($decoded->UserId) &&
			property_exists ($decoded, "Stake") && is_numeric ($decoded->Stake) &&
			property_exists ($decoded, "Guess") && is_numeric ($decoded->Guess) &&
			property_exists ($decoded, "NumDice") && is_numeric ($decoded->NumDice)
			) {
			$user_id = intval ($decoded->UserId);
			$stake = intval ($decoded->Stake);
			$guess = intval ($decoded->Guess);
			$number_of_dice = intval ($decoded->NumDice);

			// var_dump ($decoded);
			$balance = get_balance($user_id);
			if (!$balance) {
				return_error ("User not found");
			}

			if ($stake > $balance) {
				return_error ("Insufficient funds");
			}

			if ($guess < 1 || $guess > (6 * $number_of_dice)) {
				return_error ("Guess outside range 1-" . (6 * $number_of_dice));
			}

			# Forgot to check min value
			if ($number_of_dice > 5) {
				return_error ("Maximum of five dice allowed");
			}

			$dice_total = 0;
			for ($i = 0; $i < $number_of_dice; $i++) {
				$dice_roll = mt_rand(1,6);
				$dice_total += $dice_roll;
			}

			if ($dice_total == $guess) {
				$response["Success"] = true;
				# Get stake times the number of dice
				$winnings = ($stake * $number_of_dice);
				$response['Winnings'] = $winnings;
				$response['Stake'] = $stake;
				$response['Balance'] = $balance + $winnings;
				update_balance($user_id, $winnings);
			} else {
				$response["Success"] = false;
				$response['Winnings'] = 0;
				$response['Stake'] = $stake;
				# Forgot to check stake is positive so negative stake can add balance
				$response['Balance'] = $balance - $stake;
				update_balance($user_id, (-1 * $stake));
			}
		} else {
			return_error ("Missing parameter");
		}
	} else {
		return_unauthorised ("You are not logged in");
	}
} else {
	return_error ("Method not supported");
}

echo json_encode ($response);
?>

