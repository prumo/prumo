#!/bin/sh

cd ..

rm prumo.tar.gz
find prumo -type f -print | grep -v .svn | tar cfz prumo.tar.gz -T -

cd prumo
