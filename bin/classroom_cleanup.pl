#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) == 1) {
    my $netid = $ARGV[0];

    print "Cleaning up $netid on biocluster\n";
    # 	Emptying biocluster directory
    ssh('biocluster.igb.illinois.edu', "sudo /usr/local/sbin/remove_home_dir.pl $netid");
}