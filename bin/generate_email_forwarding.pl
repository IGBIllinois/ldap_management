#! /usr/bin/perl -w
$|++;
use strict;

use Net::LDAP;
use Data::Dumper;

my $PEOPLE_OU = "ou=people,dc=igb,dc=uiuc,dc=edu";

my $ldap = Net::LDAP->new('ldap://auth.igb.illinois.edu') || die "Can't connect to server\n";

my $filter = "(postalAddress=*)";
my $msg = $ldap->search(base => $PEOPLE_OU, filter => $filter);

my @usernames;
my %forwarding;

foreach my $user ($msg->entries) {
    my $address = $user->get_value('postalAddress');
    chomp $address;
    if ($address ne '' and $user->get_value('postalAddress') =~ m/^[a-zA-Z0-9\._-]+?@[a-zA-Z0-9\._-]+?\.[a-zA-Z0-9\._-]+?$/m) {
        push @usernames, $user->get_value('uid');
        $forwarding{$user->get_value('uid')} = $user->get_value('postalAddress');
    }
}

# Alphabetize
@usernames = sort @usernames;
foreach my $user (@usernames) {
    print $user . ": \t" . $forwarding{$user} . "\n";
}