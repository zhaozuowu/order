#!/bin/sh
PRODUCT_NAME="nwms"
APP_NAME="order"
SVN_NAME="nwms-order"
rm -rf output
mkdir -p output/app/$APP_NAME
mkdir -p output/conf/app/$APP_NAME
mkdir -p output/webroot/$APP_NAME
cp -r actions controllers library models script Bootstrap.php output/app/$APP_NAME
cp -r conf/*  output/conf/app
cp -r index.php  output/webroot/$APP_NAME
cd output
find ./ -name .svn -exec rm -rf {} \;
tar cvzf $SVN_NAME.tar.gz app webroot conf
rm -rf app webroot php conf
