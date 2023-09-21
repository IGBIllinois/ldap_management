#!/usr/bin/perl

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

system("cp -r /etc/skel /home/$homesub/$netid");
system("chown -R $netid.$netid /home/$homesub/$netid");
system("chmod -R 2770 /home/$homesub/$netid");
print "Home folder has been created for $netid\n";