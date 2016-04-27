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

echo "Main plugin file: $MAINFILE"

echo "
Creating directories...";
mkdir -p $GITPATH
mkdir -p $SVNPATH

echo "
Cloning from Github...";
cd $GITPATH
git clone $GITHUBREPO | sed -e 's!^!git_clone:!g'
#chmod -R 0777 .
rm -R $PLUGINSLUG/.git
cd $GITPATH/$PLUGINSLUG

echo "
Cloning from WordPress SVN...";
cd $SVNPATH
svn co $SVNURL $SVNPATH | sed -e 's!^!svn_clone:!g'
SVNVERSION=`grep "Version:" $SVNPATH/trunk/$MAINFILE | awk -F' ' '{gsub(/[\r\t\n\s]/, "", $NF); print $NF}'`

#delete all but .svn extension files from SVN repo
cd $SVNPATH/trunk
find . -type f | grep -v '.svn' | xargs rm -f | sed -e 's!^!svn_version_grep:!g'

#copy GIT repo to SVN repo's trunk subfolder
cp -R $GITPATH/$PLUGINSLUG/* $SVNPATH/trunk

echo "
Commit to WordPress SVN...";
cd $SVNPATH
svn status | grep -v -E "^.[ \t]*\..*" | grep -E "^?" | awk '{print $2}' | xargs svn add
svn status | grep -v -E "^.[ \t]*\..*" | grep -E "^!" | awk '{print $2}' | xargs svn delete

svn commit --username=$SVNUSER --password=$SVNPASS -m "$COMMITMSG" | sed -e 's!^!svn_output_commit:!g'

echo pluginname:"$7"

# delete dir after we're done
rm -R "$GITTOSVNDIR"

# echo pluginname:"$7"
