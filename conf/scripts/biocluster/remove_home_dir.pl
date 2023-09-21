#!/usr/bin/perl

use POSIX qw(strftime);

my $netid = $ARGV[0];

my $homesub;
if ($netid =~ /^([a-m])/) {
    $homesub = "a-m";
}
elsif ($netid =~ /^([n-z])/) {
    $homesub = "n-z";
} else {
    die "Error! Could not create account. Please verify netid.\n";
}

my $datestr = strftime "%Y%m%d%H%M", localtime;

system("mv -f /home/$homesub/$netid /home/$homesub/old_users/$netid-$datestr");
print "Home folder has been removed for $netid\n";