#!/usr/bin/perl

use Net::SSH qw(ssh);

if(scalar(@ARGV)==1){
	my $netid = $ARGV[0];
	chomp $netid;
	if(not $netid eq ""){
		my $crashplanuser = 'username';
		my $crashplanpwd = 'password';
		ssh('root@crashplan.igb.illinois.edu',"/usr/local/sbin/crashplanDeactivateUser.sh -u $crashplanuser -p $crashplanpwd -d $netid");
	}
}