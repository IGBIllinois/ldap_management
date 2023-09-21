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
    die "Error! Could not find account, Please verify netid.\n";
}

$newnetid=$ARGV[1];
if($newnetid=~/^([a-m])/)#check if user is in a-m
{
    $newhomepath="/file-server/home/a-m/$newnetid";
}
elsif($newnetid=~/^([n-z])/)#check if user is in n-z
{
    $newhomepath="/file-server/home/n-z/$newnetid";
}
else
{
    die "Error! Could not change account, Please verify netid.\n";
}

if(-d "$newhomepath")#first verify there is a home directory
{
    print "Home folder already exists\n";
}
else
{
    system("mv $homepath $newhomepath");
    print "Home folder has been moved for $newnetid\n";

}