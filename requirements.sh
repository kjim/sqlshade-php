#!/usr/bin/env bash

HERE=`dirname $0`
TEST_VENDOR=$HERE/test/vendor

curl --create-dirs -o $TEST_VENDOR/lime.php 'http://trac.symfony-project.org/browser/tools/lime/tags/RELEASE_1_0_9/lib/lime.php?format=txt'
