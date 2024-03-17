#!/bin/sh

rm -rf ./shortlink/view/dist;
yarn build;

mkdir -p ./dist;
rm -rf ./dist/;
mkdir -p ./dist;

cp -r ./shortlink ./dist/
rm -rf ./dist/shortlink/view/css;
rm -rf ./dist/shortlink/view/html;
rm -rf ./dist/shortlink/view/js;

release_dir=./release/$(date -I)
mkdir -p $release_dir;
rm -rf $release_dir;
mkdir -p $release_dir;
cd ./dist;
zip -r ../$release_dir/shortlink.zip ./shortlink;
cd ../;
rm -rf ./dist;