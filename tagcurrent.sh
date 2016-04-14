#! /bin/bash

# PLUGINSLUG="$1"
if [ "$1" != "" ]
	then 
		PLUGINSLUG="$1"
	else 
		echo 'var error:no plugin slug'
		exit 1
fi
# GITTOSVNDIR="$2"
if [ "$2" != "" ]
	then 
		GITTOSVNDIR="$2"
		SVNPATH="$GITTOSVNDIR/$PLUGINSLUG/tmpsvn"
		GITPATH="$GITTOSVNDIR/$PLUGINSLUG/tmpgit"
		SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG/"
	else 
		echo 'var error:no git to svn dir given'
		exit 1
fi


if [ "$3" != "" ]
	then 
		GITHUBREPO="$3"
	else 
		echo 'var error:no github repo'
		exit 1
fi
if [ "$4" != "" ]
	then 
		SVNUSER="$4"
	else 
		echo 'var error:no svn username given'
		exit 1
fi
if [ "$5" != "" ]
	then 
		SVNPASS="$5"
	else 
		echo 'var error:no svn password given'
		exit 1
fi
if [ "$6" != "" ]
	then 
		COMMITMSG="$6"
	else 
		echo 'var error:'
		exit 1
fi
if [ "$6" != "" ]
	then 
		COMMITMSG="$6"
	else 
		echo 'var error:'
		exit 1
fi
if [ "$8" != "" ]
	then 
		MAINFILE="$8"
	else 
		echo 'var error:'
		exit 1
fi

#pull svn
mkdir $SVNPATH
cd $SVNPATH
chmod -R 0777 $SVNPATH
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH | sed -e 's!^!svn_clone:!g'
chmod -R 0777 $SVNPATH
chmod 777 $SVNPATH/trunk/$8 
SVNVERSION=`grep "Version:" $SVNPATH/trunk/$8 | awk -F' ' '{gsub(/[\r\t\n\s]/, "", $NF); print $NF}'`

#delete all but .svn extension files from SVN repo
cd $SVNPATH/trunk
# sudo -R 0777 .
# find . -type f | grep -v '.svn' | xargs rm -f | sed -e 's!^!svn_version_grep:!g'
cd $SVNPATH

echo "test:$SVNPATH/tags/$SVNVERSION"

# if [ ! -d "$SVNPATH/tags/$SVNVERSION" ]; then
	mkdir $SVNPATH/tags/$SVNVERSION
	chmod -R 0777 $SVNPATH/tags/$SVNVERSION
	svn add tags/$SVNVERSION | sed -e 's!^!svn_output_add_tags:!g'

	svn copy trunk/* tags/$SVNVERSION | sed -e 's!^!svn_output_copy_trunk_to_tag:!g'

	#checking changes to SVN
	svn status | grep -v -E "^.[ \t]*\..*" | grep -E "^?" | awk '{print $2}' | xargs svn add
	svn status | grep -v -E "^.[ \t]*\..*" | grep -E "^!" | awk '{print $2}' | xargs svn delete
	
	svn ci --username=$SVNUSER --password=$SVNPASS -m "$COMMITMSG" | sed -e 's!^!svn_output_commit:!g'
	# echo "Need to tag!!!!!!!!!!!!!1"
	echo currenttagged:"$SVNVERSION"

# else
# 	echo alreadytagged:"$SVNVERSION"
# fi

echo svnversion:"$SVNVERSION"
echo pluginname:"$7"

# delete dir after we're done
rm -R $GITTOSVNDIR