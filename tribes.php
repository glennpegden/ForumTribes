<?php 

class tribes {
	
	public function calcRep($user_id) {
		//
		
	}
	
	public function addUserToTribe($user_id) {
		//
		
	}

	public function clearTribes() {

		require_once( 'initdata.php' );
		require_once( CP_DIRECTORY.'/sources/base/ipsRegistry.php' );
		
		ipsRegistry::DB();
		$registry = ipsRegistry::instance();
		$registry->init();				
		
		$results = array();
		$this->DB->build( array( 'select' => 'mgroup_others', 'from' => 'powerboardmembers', 'where' => 'mgroup_others like "%,32,%";' ) );
		$this->DB->execute();
		while ( $row = $this->DB->fetch() )
		{
			
			$results[] = $row;
			$x = explode(",",$results["mgroup_others"]);
		}

	}
	
	public function getTribesList() {
		// Returns an array of the forum IDs for active tribe forums
		return array(73);
	}
}

//Test Code

$bob = new Tribe();
$bob->clearTribes();

?>

