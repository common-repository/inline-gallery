#!/bin/bash

size="160x120"

[ -n "$1" ] && cd $1

#generate a list of all images using filename as alt text

echo
echo "Generating ${1:-.}/desc.txt; Remember that you need to edit it to set proper captions for images"

find . -maxdepth 1 -type f ! -name "thumb-*" -name "*.jpg" -o -name "*.jpeg" -o -name "*.gif" -o -name "*.png" | sed -e 's#^\./\(.*\)\.\(.*\)$#[\1.\2]\ncaption=\1\n#g' >desc.txt

#recurse
find . -maxdepth 1 -type d ! -name thumbs ! -name "." -execdir $0 {} \;

#generate thumbnails
echo "Generating thumbnails in ${1:-current directory}"
echo
mkdir -p thumbs
ls -1 | awk "BEGIN{size=\"$size\"}"'/\.(jpg|jpeg|gif|png)$/ && !/^thumb/{print $0" -> thumbs/thumb-"$0; system("[ ! -e '"'"'thumbs/thumb-"$0"'"'"' ] && convert -resize "size" '"'"'"$0"'"'"' '"'"'thumbs/thumb-"$0"'"'"'")}'
echo
