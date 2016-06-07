#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if(scalar(@ARGV)==1){
	my $netid = $ARGV[0];
	my $homesub;
	if($netid=~/^([a-m])/){       
		$homesub="a-m";
	} elsif($netid=~/^([n-z])/) {
		$homesub="n-z";
	}
	
# 	Emotying file-server directory
	ssh('root@file-server.igb.illinois.edu',"mv -f /home/$homesub/$netid /home/oldusers/$homesub/");
	ssh('root@file-server.igb.illinois.edu',"mkdir /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chown $netid.$netid /file-server/home/$homesub/$netid");
	ssh('root@file-server.igb.illinois.edu',"chmod 2770 /file-server/home/$homesub/$netid");
	
# 	Emptying biocluster directory
	ssh('root@biocluster.igb.illinois.edu',"mv -f /home/$homesub/$netid /home/oldusers/$homesub/");
}