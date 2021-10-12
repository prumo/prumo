#!/bin/sh

find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
find ./*.sh -type f -exec chmod 755 {} \;

cd ..

rm prumo.tar.gz
find prumo -type f -print | grep -v .git | tar cfz prumo.tar.gz -T -

cd prumo
