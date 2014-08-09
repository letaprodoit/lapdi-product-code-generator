#!/bin/sh
 
git config --global user.email "sharron@thesoftwarepeople.com"
git config --global user.name "Sharron Denice"

git filter-branch --env-filter '
 
an="Sharron Denice"
am="sharron@thesoftwarepeople.com"
cn="Sharron Denice"
cm="sharron@thessoftwarepeople.com"
 
if [ "$GIT_COMMITTER_EMAIL" = "cabox@box-codeanywhere.com" ]
then
    cn="Your New Committer Name"
    cm="Your New Committer Email"
fi
if [ "$GIT_AUTHOR_EMAIL" = "cabox@box-codeanywhere.com" ]
then
    an="Your New Author Name"
    am="Your New Author Email"
fi
 
export GIT_AUTHOR_NAME="$an"
export GIT_AUTHOR_EMAIL="$am"
export GIT_COMMITTER_NAME="$cn"
export GIT_COMMITTER_EMAIL="$cm"
'
