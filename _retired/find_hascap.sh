#!/bin/sh
find /home/kric/www/kric.co.jp/www/wp-content/plugins -name "*.php" -exec grep -l "has_cap" {} \;
