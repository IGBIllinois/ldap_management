#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
use POSIX qw(strftime);
if(scalar(@ARGV)==1){
	my $netid = $ARGV[0];
	my $homesub;
	if($netid=~/^([a-m])/){       
		$homesub="a-m";
	} elsif($netid=~/^([n-z])/) {
		$homesub="n-z";
	}
	my $datestr = strftime "%Y%m%d%H%M", localtime;
	
	print "Cleaning up $netid file-server\n";
	
# 	Emptying file-server directory
	ssh('root@file-server.igb.illinois.edu',"mv -f /file-server/home/$homesub/$netid /file-server/home/$homesub/oldusers/$netid-$datestr");
	ssh('root@file-server.igb.illinois.edu',"mkdir /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chown $netid.$netid /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chmod 2770 /file-server/home/$homesub/$netid");
	
# 	print "Cleaning up $netid biocluster\n";
# 	Emptying biocluster directory
# 	ssh('root@biocluster.igb.illinois.edu',"mv -f /home/$homesub/$netid /home/$homesub/old_users/no_backup/$netid-$datestr");
	
	print "Cleaning up $netid biocluster2\n";
# 	Emptying biocluster directory
	ssh('root@biocluster2.igb.illinois.edu',"mv -f /home/$homesub/$netid /home/$homesub/old_users/$netid-$datestr");
}