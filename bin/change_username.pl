#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) == 2) {
    my $netid = $ARGV[0];
    my $newnetid = $ARGV[1];

    # Changing subscription to everyone mailing list
    ssh('mail.igb.illinois.edu', "sudo /usr/local/bin/unsubscribe.sh \"$netid\@igb.uiuc.edu\"");
    ssh('mail.igb.illinois.edu', "sudo /usr/local/bin/unsubscribe.sh \"$netid\@igb.illinois.edu\"");
    ssh('mail.igb.illinois.edu', "echo \"$newnetid\@igb.illinois.edu\" | sudo /usr/lib/mailman/bin/add_members -r - everyone");
    ssh('mail.igb.illinois.edu', "echo \"$newnetid\@igb.illinois.edu\" | sudo /usr/lib/mailman/bin/add_members -r - igb-facilities-announce");

    # Move file-server directory
    ssh('file-server.igb.illinois.edu', "sudo /usr/local/sbin/change_home_dir.pl $netid $newnetid");
}