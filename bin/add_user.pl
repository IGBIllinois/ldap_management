#!/usr/bin/perl

use Net::SSH qw(ssh);
use Net::SCP qw(scp);
if (scalar(@ARGV) >= 1 and not $ARGV[0] eq "") {
    my $netid = $ARGV[0];
    my $classroomUser = 0;
    if (scalar(@ARGV) >= 2 and $ARGV[1] eq '--classroom') {
        $classroomUser = 1;
    }

    if (not $classroomUser) {
        # Subscribing user to everyone@igb.illinois.edu
        ssh('mail.igb.illinois.edu', "echo \"$netid\@igb.illinois.edu\" | sudo /usr/lib/mailman/bin/add_members -r - everyone");
        ssh('mail.igb.illinois.edu', "echo \"$netid\@igb.illinois.edu\" | sudo /usr/lib/mailman/bin/add_members -r - igb-facilities-announce");
        # Creating file-server directory
        ssh('file-server.igb.illinois.edu', "sudo /usr/local/sbin/home_dir.pl $netid");
        ssh('file-server.igb.illinois.edu', "sudo /usr/local/sbin/dropbox.pl $netid");
    }

}