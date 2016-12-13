#!/bin/bash
#php codecept.phar run unit,integration

php ${PWD}/../src/main/php/lib/vendor/codeception/codeception/codecept --config=${PWD}/../tests/codeception.yml run unit,integration
