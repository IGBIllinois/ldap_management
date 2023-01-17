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

    # Remove user from everyone, igb-facilities-announce, igb-master mailing lists
    ssh('mail.igb.illinois.edu', "/usr/local/bin/unsubscribe.sh \"$netid\@igb.uiuc.edu\"");

    # Remove user from file-server
    ssh('file-server.igb.illinois.edu', "sudo mv -f /file-server/home/$homesub/$netid /file-server/home/$homesub/oldusers/no_backup/$netid-$datestr");
}