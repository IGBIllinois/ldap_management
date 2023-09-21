#!/usr/bin/perl

$netid=$ARGV[0];
if($netid=~/^([a-m])/)#check if user is in a-m
{
    $homepath="/file-server/home/a-m/$netid";
}
elsif($netid=~/^([n-z])/)#check if user is in n-z
{
    $homepath="/file-server/home/n-z/$netid";
}
else
{
    die "Error! Could not create account, Please verify netid.\n";
}

if(-d "$homepath")#first verify there is a home directory
{
    print "Home folder already exists\n";
}
else
{
    system("mkdir -m 2770 $homepath");#create folder with permission and sticky bit
    system("chown $netid:$netid $homepath");
    print "Home folder has been created for $netid\n";

}