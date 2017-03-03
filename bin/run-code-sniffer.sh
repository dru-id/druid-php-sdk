#!/bin/bash

php ${PWD}/../vendor/bin/phpcs --report-file=${PWD}/../tests/_output/code-sniffing.txt -p --standard=PSR2 ${PWD}/../src