#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) == 1) {
    my $netid = $ARGV[0];

    # Remove user from everyone, igb-facilities-announce, igb-master mailing lists
    ssh('mail.igb.illinois.edu', "sudo /usr/local/bin/unsubscribe.sh \"$netid\@igb.uiuc.edu\"");
    ssh('mail.igb.illinois.edu', "sudo /usr/local/bin/unsubscribe.sh \"$netid\@igb.illinois.edu\"");

    # Remove user from file-server
    ssh('file-server.igb.illinois.edu', "sudo /usr/local/sbin/remove_home_dir.pl $netid");
}