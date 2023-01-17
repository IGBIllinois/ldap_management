#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
use POSIX qw(strftime);
if (scalar(@ARGV) == 1) {
    my $netid = $ARGV[0];
    my $homesub;
    if ($netid =~ /^([a-m])/) {
        $homesub = "a-m";
    }
    elsif ($netid =~ /^([n-z])/) {
        $homesub = "n-z";
    }
    my $datestr = strftime "%Y%m%d%H%M", localtime;

    print "Cleaning up $netid biocluster2\n";
    # 	Emptying biocluster directory
    ssh('biocluster2.igb.illinois.edu', "mv -f /home/$homesub/$netid /home/$homesub/old_users/$netid-$datestr");
}