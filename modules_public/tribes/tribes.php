<?php 

class public_tribes_tribes_tribes extends ipsCommand {

	var $db;
	var $registry;
	var $passmark = 1;


	public function doExecute( ipsRegistry $registry ) {


        	if (!isset($_GET["join"])) {

	           $this->showAvailableGroups();
	        } else {
        	   $this->addToGroup($_GET["join"]);
       		 }
	}

	
	public function canJoinTribe($userID,$forumID) {

		// Check is a user has sufficient rep to join the tribe

// RETURN POSITIVE RATINGS
$this->DB->allow_sub_select=1;
		$this->DB->build( array( 'select' => 'SUM(powerboardreputation_index.rep_rating) as 
rating,count(powerboardreputation_index.rep_rating) as raters, author_id', 'from' 
=> 'reputation_index left join powerboardposts on pid = type_id', 'where' => 
'author_id = '.$userID.' and powerboardreputation_index.rep_rating =  "1"
and powerboardreputation_index.member_id IN (SELECT member_id from powerboardmembers where mgroup_others like "%,'.$forumID.',%") GROUP BY 
author_id'));

                $this->DB->execute();
                while ( $row = $this->DB->fetch() )
                {
			$ratevals[$row['author_id']]['posval'] = $row['rating'];
			$ratevals[$row['author_id']]['poscount'] = $row['raters'];
                }

// RETURN NEGATIVE RATINGS
$this->DB->allow_sub_select=1;

                $this->DB->build( array( 'select' => 'SUM(powerboardreputation_index.rep_rating) as
rating,count(powerboardreputation_index.rep_rating) as raters, author_id', 'from'
=> 'reputation_index left join powerboardposts on pid = type_id', 'where' =>
'author_id =
'.$userID.'
and powerboardreputation_index.rep_rating =  "-1"
and powerboardreputation_index.member_id IN (SELECT member_id from powerboardmembers where mgroup_others like "%,'.$forumID.',%") GROUP BY 
author_id'));

                $this->DB->execute();
                while ( $row = $this->DB->fetch() )
                {
                        $ratevals[$row['author_id']]['negval'] = $row['rating']; 
                        $ratevals[$row['author_id']]['negcount'] = $row['raters'];
                }

	$rating = $ratevals[$userID]['posval'] + ($ratevals[$userID]['negval']*10);

	return $rating;
	
	}

	public function TestTop($forumID,$retcount) {
// RETURN THE TOP POSTERS RATING FOR A GIVEN FORUM

		$this->db->build( array( 'select' => 'member_id,name,posts', 'from' => 'members', 'order' => 'posts DESC LIMIT '.$retcount ));
                $this->db->execute();
                $count=0;
                while ( $row = $this->db->fetch() )
                {
			$toprates[$count]['name']  = $row['name'];
   			$toprates[$count]['posts'] = $row['posts'];
			$toprates[$count]['member_id'] = $row['member_id'];
			$count++;
                }
		$toprates['count'] = $count;
		for($i=0;$i<$count;$i++) 
		{
			$memberid = $toprates[$i]['member_id'];
                        $toprates[$i]['rating'] = $this->canJoinTribe($memberid,$forumID);
		}

		return $toprates;
	}

 function showAvailableGroups() {


		$this->lang->loadLanguageFile( array( 'key' ) );
		$this->registry->output->setTitle("Forum Tribes");
		$this->registry->output->addNavigation( "Forum Tribes", 'app=tribes' );

		$this->registry->getClass('output')->addContent("<p>Tribes are like-minded groups of people who can chat together. 
The software carefully analyses which posts people rate up and rate down to see which tribes they would fit in with. People can belong to multiple 
tribes at once, but for evey tribe you join it gets less likely you'll be welcomed in more of them.</p>");

		$this->registry->getClass('output')->addContent("<p>Tribes are not based around topic, instead they are based around people who share similar likes and dislikes in what they read on the forums. As you like and dislike post (and as people like and dislike yours), 
you'll be more likely to be invited to tribes of people who both like and dislike similar post to you as well as liking your posts</p>");

                $this->db = ipsRegistry::DB();

                $member = $this->registry->member()->fetchMemberData();

                if ($member['member_id'] == 0) {
			$this->registry->getClass('output')->addContent("You need to be logged in to use this");

                } else {


                        for ($i=37;$i<=40;$i++) {

                                $score = $this->canJoinTribe($member['member_id'],$i);
                                if ($score >= $this->passmark) {

			               $this->db->build( array( 'select' => 'mgroup_others', 'from' => 'members', 'where' => 'member_id = 
'.$member['member_id'].' and mgroup_others like "%,'.$i.',%";' ) );
                			$this->db->execute();
                			$row = $this->db->fetch();

					if ($row) {
						$this->registry->getClass('output')->addContent( "<p>You are already in tribe " .$i . ".</p>");
					} else {	
						$this->registry->getClass('output')->addContent("<p>You are invited to join tribe ".$i.". If you 
want <a href='index.php?app=tribes?join=" . $i 
. "'>Click
Here To Join</a>.</p>");
					
					}
                                }
                        }
                }

$this->registry->getClass('output')->sendOutput();
        }

   public function addToGroup($gid) {

        $member = $this->registry->member()->fetchMemberData();
	$this->registry->output->setTitle("Forum Tribes");
        $this->registry->output->addNavigation( "Forum Tribes", 'app=tribes' );
	
	if ($this->canJoinTribe($member['member_id'],$gid) > $this->passmark) {	
			
                $this->DB->build( array( 'select' => 'mgroup_others', 'from' => 'members', 'where' => 'member_id = '.$member['member_id'].';' ) );
                $this->DB->execute();
                $row = $this->DB->fetch();

                $groups = explode(",",$row["mgroup_others"]);

                array_push($groups,$gid);
		array_unique($groups);

		$str = ",";
		foreach ($groups as $group) {
			if ($group) {
				$str .= $group . ",";
			}
		}

                $this->DB->update('members', array('mgroup_others' => $str), ' member_id =' .$member["member_id"]);
		
						$this->registry->getClass('output')->addContent( "<p>Welcome! You have joined " .$gid . ".</p>");
        } else {
						$this->registry->getClass('output')->addContent( "<p>You can not join tribe " .$gid . ".</p>");
	}
$this->registry->getClass('output')->sendOutput();
  }	

}



?>
