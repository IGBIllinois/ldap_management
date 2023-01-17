#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) == 2) {
    my $netid = $ARGV[0];
    my $homesub;
    if ($netid =~ /^([a-m])/) {
        $homesub = "a-m";
    }
    elsif ($netid =~ /^([n-z])/) {
        $homesub = "n-z";
    }

    my $newnetid = $ARGV[1];
    my $newhomesub;
    if ($newnetid =~ /^([a-m])/) {
        $newhomesub = "a-m";
    }
    elsif ($newnetid =~ /^([n-z])/) {
        $newhomesub = "n-z";
    }

    # Changing subscription to everyone mailing list
    ssh('mail.igb.illinois.edu', "/usr/lib/mailman/bin/remove_members everyone \"$netid\@igb.uiuc.edu\"");
    ssh('mail.igb.illinois.edu', "echo \"$newnetid\@igb.uiuc.edu\" | /usr/lib/mailman/bin/add_members -r - everyone");

    # Move file-server directory
    ssh('file-server.igb.illinois.edu', "mv /file-server/home/$homesub/$netid /file-server/home/$newhomesub/$newnetid");
}