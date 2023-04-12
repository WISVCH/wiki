# acl.auth.php
# <?php exit()?>
# Don't modify the lines above
#
# Access Control Lists
#
# Editing this file by hand instead of using the ACL interface.
#
# If your auth backend allows special char like spaces in groups
# or user names you need to urlencode them (only chars <128, leave
# UTF-8 multibyte chars as is)
#
# none   0
# read   1
# edit   2
# create 4
# upload 8
# delete 16
*	@ALL	0
beheer:*	@staff	16
bestuur	@epa	1
bestuur	@bestuur	2
bestuur	@oudbestuur	2
bestuur	@vc	2
bestuur:*	@epa	1
bestuur:*	@bestuur	16
bestuur:*	@oudbestuur	16
bestuur:*	@vc	16
chmanual:*	@user	16
commissies	@user	1
commissies	@bestuur	2
commissies	@oudbestuur	2
commissies	@vc	2
commissies:*	@epa	1
commissies:*	@epa	1
commissies:*	@bestuur	16
commissies:*	@oudbestuur	16
commissies:*	@vc	16
commissies:akcie	@akcie	2
commissies:akcie	@lucie	1
commissies:akcie:*	@akcie	16
commissies:akcie:*	@lucie	1
commissies:algemeen	@user	1
commissies:algemeen	@bestuur	2
commissies:algemeen	@oudbestuur	2
commissies:algemeen:*	@user	1
commissies:algemeen:*	@bestuur	16
commissies:algemeen:*	@oudbestuur	16
commissies:annucie	@annucie	2
commissies:annucie:*	@annucie	16
commissies:bedrijvenreis	@bedrijvenreis	2
commissies:bedrijvenreis:*	@bedrijvenreis	16
commissies:chipcie	@chipcie	2
commissies:chipcie:*	@chipcie	16
commissies:choco	@choco	2
commissies:choco:*	@choco	16
commissies:comma	@comma	2
commissies:constantijnhuygens	@constantijnhuygens	2
commissies:constantijnhuygens:*	@constantijnhuygens	16
commissies:dies	@dies	2
commissies:dies	@lucie	1
commissies:dies:*	@dies	16
commissies:dies:*	@lucie	1
commissies:eiweiw	@eiweiw	2
commissies:eiweiw:*	@eiweiw	16
commissies:facie	@facie	2
commissies:facie:*	@facie	16
commissies:filmcrew	@filmcrew	2
commissies:filmcrew:*	@filmcrew	16
commissies:flitcie	@flitcie	2
commissies:flitcie:*	@flitcie	16
commissies:galacie	@gala	2
commissies:galacie	@lucie	1
commissies:galacie:*	@gala	16
commissies:galacie:*	@lucie	1
commissies:hackdelft	@hackathon	2
commissies:icom	@icom	2
commissies:icom:*	@icom	16
commissies:lancie	@lancie	2
commissies:lancie:*	@lancie	16
commissies:lucie	@lucie	2
commissies:lucie:*	@lucie	16
commissies:machazine	@machazine	2
commissies:machazine:*	@machazine	16
commissies:match	@match	2
commissies:match:*	@match	16
commissies:pi	@pi	2
commissies:pi:*	@pi	16
commissies:reis	@studyvisit	2
commissies:reis:*	@studyvisit	16
commissies:sjaarcie	@sjaarcie	2
commissies:sjaarcie:*	@sjaarcie	16
commissies:symposium	@symposium	2
commissies:symposium:*	@symposium	16
commissies:w3cie	@w3cie	2
commissies:w3cie:*	@w3cie	16
commissies:wiewie	@wiewie	2
commissies:wiewie:*	@wiewie	16
commissies:wifi	@wifi	2
commissies:wifi:*	@wifi	16
commissies:wocky	@wocky	2
commissies:wocky:*	@wocky	16
start	@ALL	0
start   @user 1
start	@bestuur	2
start	@oudbestuur	2
wiki:*	@ALL	0
wiki:*  @user 1
