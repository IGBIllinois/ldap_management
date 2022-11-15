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

    # Remove user from mail
    ssh('root@mail.igb.illinois.edu', "mv -f /home/$homesub/$netid /home/oldusers/$homesub/");
    # Remove user from everyone, igb-facilities-announce, igb-master mailing lists
    ssh('root@mail.igb.illinois.edu', "/usr/local/bin/unsubscribe.sh \"$netid\@igb.uiuc.edu\"");
    # Remove mail alias
    # 	ssh('root@mail.igb.illinois.edu',"/usr/local/sbin/remove_alias.pl $netid");

    # Remove user from file-server
    ssh('root@file-server.igb.illinois.edu', "mv -f /file-server/home/$homesub/$netid /file-server/home/$homesub/oldusers/no_backup/$netid-$datestr");

}