#!/usr/bin/perl

use POSIX qw(strftime);

$netid=$ARGV[0];
if($netid=~/^([a-m])/)#check if user is in a-m
{
    $homepath="a-m";
}
elsif($netid=~/^([n-z])/)#check if user is in n-z
{
    $homepath="n-z";
}
else
{
    die "Error! Could not remove account, Please verify netid.\n";
}

if(! -d "/file-server/home/$homepath/$netid")#first verify there is a home directory
{
    print "Home folder already exists\n";
}
else
{
    my $datestr = strftime "%Y%m%d%H%M", localtime;
    system("mv -f /file-server/home/$homepath/$netid /file-server/home/$homepath/oldusers/no_backup/$netid-$datestr");
    print "Home folder has been removed for $netid\n";

}