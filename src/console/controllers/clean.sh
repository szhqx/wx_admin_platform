# @author: Crunchify.com
# DEFAULT_SOCK_AGE - # days ago (rounded up) that socket was last accessed

CRUNCHIFY_TMP_DIRS="/mnt/tmp"
DEFAULT_FILE_AGE=+1
DEFAULT_LINK_AGE=+1
DEFAULT_SOCK_AGE=+1

DEFAULT_AGE=+360

# Make EMPTYFILES true to delete zero-length files
EMPTYFILES=true
#EMPTYFILES=true

cd
/usr/bin/logger "cleantmp.sh[$$] - Begin cleaning tmp directories"

echo ""
echo "delete any tmp files that are more than 1 hours ago"
/usr/bin/find $CRUNCHIFY_TMP_DIRS                               \
              -depth                                                     \
              -type f -a -mmin $DEFAULT_AGE \
              -print -delete
echo ""

echo "delete any old tmp symlinks"
/usr/bin/find $CRUNCHIFY_TMP_DIRS                               \
              -depth                                                     \
              -type l -a -mmin $DEFAULT_AGE  \
              -print -delete
echo ""

if /usr/bin/$EMPTYFILES ;
then
    echo "delete any empty files"
    /usr/bin/find $CRUNCHIFY_TMP_DIRS                               \
                  -depth                                                     \
                  -type f -a -empty                                          \
                  -print -delete
fi

echo "Delete any old Unix sockets"
/usr/bin/find $CRUNCHIFY_TMP_DIRS                               \
              -depth                                                     \
              -type s -a -mmin $DEFAULT_AGE -a -size 0             \
              -print -delete
echo""

echo "delete any empty directories (other than lost+found)"
/usr/bin/find $CRUNCHIFY_TMP_DIRS                               \
              -depth -mindepth 1                                         \
              -type d -a -empty -a ! -name 'lost+found'                  \
              -print -delete
echo ""

/usr/bin/logger "cleantmp.sh[$$] - Done cleaning tmp directories"

# send out an email about diskcleanup action
# mail -s "Disk cleanup has been performed successfully." you@email.com

echo ""
echo "Diskcleanup Script Successfully Executed"
exit 0
