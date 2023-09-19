#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) >= 1 and not $ARGV[0] eq "") {
    my $netid = $ARGV[0];

    ssh('biocluster.igb.illinois.edu', "sudo /usr/local/sbin/create_home_dir.pl $netid");
}