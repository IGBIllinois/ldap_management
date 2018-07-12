#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if(scalar(@ARGV)>=1 and not $ARGV[0] eq ""){
	my $netid = $ARGV[0];

	my $homesub;
	if($netid=~/^([a-m])/){       
		$homesub="a-m";
	} else {
		$homesub="n-z";
	}
	
	ssh('root@biocluster.igb.illinois.edu', "cp -r /etc/skel /igbgroup/$homesub/$netid && chown $netid.$netid /igbgroup/$homesub/$netid && chmod 2770 /igbgroup/$homesub/$netid && ln -s /igbgroup/$homesub/$netid /home/$homesub/$netid");
}