<?php

/**
 *                     *** SoluteDNS Provisioning Module ***
 *
 * @file        
 * @package     solutedns
 *
 * Copyright (c) 2017 NetDistrict
 * All rights reserved.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @license     SoluteDNS - End User License Agreement, http://www.solutedns.com/eula/
 * @author      NetDistrict <info@netdistrict.net>
 * @copyright   NetDistrict
 * @link        https://www.solutedns.com
 **/
 
use Illuminate\Database\Capsule\Manager as Capsule;

function solutedns_zones_ConfigOptions() {

	# Should return an array of the module options for each product - maximum of 24

	$configarray = array(
		"zones" => array("Type" => "text", "Size" => "5", "Description" => "Enter amount of zones to grant.  (0 = unlimited, -1 = none)"),
	);

	return $configarray;
}

function solutedns_zones_CreateAccount($params) {

	$pdo = Capsule::connection()->getPdo();

	$userid = (int) $params["userid"];
	$zones = (int) $params["configoption1"];

	try {

		$sql = "SELECT * FROM mod_solutedns_client_limits WHERE client_id = '$userid'";
		$result = $pdo->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$zone_limit = (int) $row['zone_limit'];
		$zone_user = (int) $row['client_id'];

		if (empty($zone_user)) {
			// Add row
			$sql = "INSERT INTO mod_solutedns_client_limits (client_id, zone_limit) VALUES ('$userid', '$zones')";
			$pdo->query($sql);
		} else {
			// Update row
			$value = $zone_limit + $zones;
			$sql = "UPDATE mod_solutedns_client_limits SET zone_limit='$value' WHERE client_id='$userid'";
			$pdo->query($sql);
		}

		$successful = true;
	} catch (Exception $ex) {
		$error = $ex;
	}


	if ($successful) {
		$result = "success";
	} else {
		$result = $error;
	}
	return $result;
}

function solutedns_zones_TerminateAccount($params) {

	$pdo = Capsule::connection()->getPdo();

	$userid = $params["userid"];
	$zones = (int) $params["configoption1"];

	try {

		$sql = "SELECT * FROM mod_solutedns_client_limits WHERE client_id = '$userid'";
		$result = $pdo->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$zone_limit = (int) $row['zone_limit'];

		$value = $zone_limit - $zones;

		if ($value > 0) {
			// Update row
			$sql = "UPDATE mod_solutedns_client_limits SET zone_limit='$value' WHERE client_id='$userid'";
			$pdo->query($sql);
		} else {
			// Delete row	
			$sql = "DELETE FROM mod_solutedns_client_limits WHERE client_id='$userid'";
			$pdo->query($sql);
		}

		$successful = true;
	} catch (Exception $ex) {
		$error = $ex;
	}

	if ($successful) {
		$result = "success";
	} else {
		$result = $error;
	}
	return $result;
}

function solutedns_zones_ClientArea($params) {

	# Output can be returned like this, or defined via a clientarea.tpl template file (see docs for more info)

	$code = '<input type="button" class="btn btn-default" value="Zone Management" onClick="javascript:location.href=\'index.php?m=solutedns\'" />';
	return $code;
}

?>