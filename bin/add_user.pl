#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if(scalar(@ARGV)>=1 and not $ARGV[0] eq ""){
	my $netid = $ARGV[0];
	my $classroomUser = 0;
	if(scalar(@ARGV)>=2 and $ARGV[1] eq '--classroom'){
		$classroomUser = 1;
	}
	my $homesub;
	if($netid=~/^([a-m])/){       
		$homesub="a-m";
	} elsif($netid=~/^([n-z])/) {
		$homesub="n-z";
	}
	
	if(not $classroomUser){
		# Creating mail directory
		ssh('root@mail.igb.illinois.edu',"mkdir -p /home/$homesub/$netid/mail");
		ssh('root@mail.igb.illinois.edu',"chown -R $netid.$netid /home/$homesub/$netid");
	
		# Subscribing user to everyone@igb.illinois.edu
		ssh('root@mail.igb.illinois.edu',"echo \"$netid\@igb.uiuc.edu\" | /usr/lib/mailman/bin/add_members -r - everyone");
	}
	
	# Creating file-server directory
	ssh('root@file-server.igb.illinois.edu',"mkdir /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chown $netid.$netid /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chmod 2770 /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"/usr/local/sbin/dropbox.pl $netid");
}