#!/bin/sh

cd ..

rm prumo.tar.gz
find prumo -type f -print | grep -v .git | tar cfz prumo.tar.gz -T -

cd prumo
